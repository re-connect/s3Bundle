<?php

namespace Reconnect\S3Bundle\Service;


use Imagick;
use Reconnect\S3Bundle\Adapter\S3Adapter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\UuidV4;
use function exif_imagetype;

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
     * @param File $file
     * @return UuidV4|null
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
     * @param File    $file
     * @param ?string $fileKey
     * @return UuidV4
     * @throws \Exception
     */
    public function uploadFile(File $file, ?string $fileKey = null): UuidV4
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