<?php

namespace Reconnect\S3Bundle\Service;

use Aws\Result;
use Reconnect\S3Bundle\Adapter\S3Adapter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FlysystemS3Client
{
    private S3Adapter $s3Adapter;
    private PdfService $pdfService;

    public function __construct(S3Adapter $s3Adapter, PdfService $pdfService)
    {
        $this->s3Adapter = $s3Adapter;
        $this->pdfService = $pdfService;
    }

    /**
     * @throws \Exception|\ImagickException
     */
    public function generateThumbnail(File $file): ?string
    {
        $originalFilename = $file instanceof UploadedFile ? $file->getClientOriginalName() : $originalFilename = $file->getFilename();
        $thumbnailName = 'thumbnail-'.$originalFilename;
        if ('application/pdf' === $file->getMimeType()) {
            $thumbnailName = $this->pdfService->generatePdfThumbnail($file->getPathname(), $thumbnailName.'.jpeg');
        } elseif (exif_imagetype($file->getPathname())) {
            $im = new \Imagick();
            $im->readImage($file);
            $im->thumbnailImage(400, 164, true);
            $im->writeImage($thumbnailName);
        } else {
            return null;
        }

        $thumbnailFile = new File($thumbnailName);
        $thumbnailKey = $this->s3Adapter->putFile($thumbnailFile);
        unlink($thumbnailFile);

        return $thumbnailKey;
    }

    /**
     * @throws \Exception
     */
    public function uploadFile(File $file, ?string $fileKey = null): string
    {
        return $this->s3Adapter->putFile($file, $fileKey);
    }

    /**
     * @throws \Exception
     */
    public function downloadFile(string $fileKey, string $tempUri, ?string $bucketName = null): Result
    {
        return $this->s3Adapter->download($fileKey, $tempUri, $bucketName);
    }

    public function getPreSignedUrl(?string $fileKey = null): ?string
    {
        return $this->s3Adapter->getPreSignedUrl($fileKey);
    }

    /**
     * @throws \Exception
     */
    public function copyFileFromOtherBucket(string $otherBucketName, string $otherBucketKey): string
    {
        return $this->s3Adapter->copyFileFromOtherBucket($otherBucketName, $otherBucketKey);
    }

    public function getDownloadablePresignedUrl(string $fileKey, string $originalFileName, string $fileMimeType): string
    {
        $fileNameParts = explode('.', $originalFileName, 2);
        $extension = $fileNameParts[1] ?? (new MimeTypes())->getExtensions($fileMimeType)[0] ?? null;
        $slug = (new AsciiSlugger())->slug($fileNameParts[0] ?? $originalFileName)->lower()->toString();
        $fileName = $extension ? sprintf('%s.%s', $slug, $extension) : $slug;

        return $this->s3Adapter->getDownloadablePresignedUrl($fileKey, $fileMimeType, $fileName);
    }

    /**
     * @throws \Exception
     */
    public function deleteFile(string $fileKey): void
    {
        $this->s3Adapter->deleteFile($fileKey);
    }
}
