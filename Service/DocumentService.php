<?php

namespace Reconnect\S3Bundle\Service;


use Imagick;
use Reconnect\S3Bundle\Adapter\S3Adapter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\UuidV4;

class DocumentService
{
    private S3Adapter $s3Adapter;
    private PdfService $pdfService;

    public function __construct(S3Adapter $s3Adapter, PdfService $pdfService)
    {
        $this->s3Adapter = $s3Adapter;
        $this->pdfService = $pdfService;
    }

    /**
     * @param File   $file
     * @param string $fileName
     * @return string|null
     * @throws \ImagickException|\Exception
     */
    public function generateThumbnail(File $file, string $fileName): ?string
    {
        $thumbnailKey = null;
        $fileIsImage = exif_imagetype($file->getPathname());
        $originalFilename = $file instanceof UploadedFile ? $file->getClientOriginalName() : $originalFilename = $file->getFilename();
        if ('application/pdf' === $file->getMimeType()) {
            $thumbnailName = 'thumbnail-'.$fileName;
            $thumbnailPath = $this->pdfService->generatePdfThumbnail($file->getPathname(), $thumbnailName.'.jpeg');
            $thumbnailFile = new File($thumbnailPath);
            $thumbnailKey = $this->s3Adapter->putFile($thumbnailFile);
            unlink($thumbnailFile);
        } elseif ($fileIsImage) {
            $thumbnailName = 'thumbnail-'.$originalFilename;
            $im = new Imagick();
            $im->readImage($file);
            $im->thumbnailImage(400, 164, true);
            $im->writeImage($thumbnailName);
            $thumbnailFile = new File($thumbnailName);
            $thumbnailKey = $this->s3Adapter->putFile($thumbnailFile);
            unlink($thumbnailFile);
        }

        return $thumbnailKey;
    }

    /**
     * @param UploadedFile $file
     * @param ?string      $fileKey
     * @return UuidV4
     * @throws \Exception
     */
    public function uploadFile(UploadedFile $file, ?string $fileKey = null): UuidV4
    {
        try {
            return $this->s3Adapter->putFile($file, $fileKey);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getPreSignedUrl(string $fileKey = null): ?string
    {
        return $this->s3Adapter->getPreSignedUrl($fileKey);
    }

}