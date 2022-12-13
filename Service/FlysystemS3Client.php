<?php

namespace Reconnect\S3Bundle\Service;


use Aws\Result;
use Cocur\Slugify\Slugify;
use Imagick;
use Reconnect\S3Bundle\Adapter\S3Adapter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Uid\UuidV4;

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
     * @throws \ImagickException|\Exception
     */
    public function generateThumbnail(File $file): ?UuidV4
    {
        $originalFilename = $file instanceof UploadedFile ? $file->getClientOriginalName() : $originalFilename = $file->getFilename();
        $thumbnailName = 'thumbnail-'.$originalFilename;
        if ('application/pdf' === $file->getMimeType()) {
            $thumbnailName = $this->pdfService->generatePdfThumbnail($file->getPathname(), $thumbnailName.'.jpeg');
        } elseif (exif_imagetype($file->getPathname())) {
            $im = new Imagick();
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
    public function uploadFile(File $file, ?string $fileKey = null): UuidV4
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

    public function getPreSignedUrl(string $fileKey = null): ?string
    {
        return $this->s3Adapter->getPreSignedUrl($fileKey);
    }

    /**
     * @throws \Exception
     */
    public function copyFileFromOtherBucket(string $otherBucketName, string $otherBucketKey): UuidV4
    {
        return $this->s3Adapter->copyFileFromOtherBucket($otherBucketName, $otherBucketKey);
    }

    public function getDownloadablePresignedUrl(string $fileKey, string $originalFileName, string $fileMimeType): string
    {
        $explodedFileName = explode('.', (new Slugify())->slugify($originalFileName));
        $mimes = (new MimeTypes())->getExtensions($fileMimeType);

        $extension = count($explodedFileName) > 1
            ? end($explodedFileName)
            : (count($mimes) > 0 ? $mimes[0] : null);

        $fileName = $extension ? sprintf('%s.%s', $explodedFileName[0], $extension) : $originalFileName;

        return $this->s3Adapter->getDownloadablePresignedUrl($fileKey, $fileMimeType, $fileName);
    }
}