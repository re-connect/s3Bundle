<?php

namespace Reconnect\S3Bundle\Service;

class PdfService
{
    public function generatePdfThumbnail(string $source, string $target, int $size = 256, int $page = 1): ?string
    {
        try {
            if (!file_exists($source) || is_dir($source) || 'application/pdf' != mime_content_type($source)) {
                return null;
            }

            $target = dirname($source).'/'.$target; // using '/' as file separation for nfs on linux.

            --$page; // default page 1, must be treated as 0 hereafter
            if ($page < 0) {
                $page = 0; // we cannot have negative values
            }

            $img = new \Imagick($source."[{$page}]"); // [0] = first page, [1] = second page

            $imH = $img->getImageHeight();
            $imW = $img->getImageWidth();
            if (0 == $imH) {
                $imH = 1; // if the pdf page has no height use 1 instead
            }
            if (0 == $imW) {
                $imW = 1; // if the pdf page has no width use 1 instead
            }

            $sizR = (int) round($size * (min($imW, $imH) / max($imW, $imH))); // relative pixels of the shorter side

            if ($imH == $imW) { // square page
                $img->thumbnailimage($size, $size);
            }
            if ($imH < $imW) { // landscape page orientation
                $img->thumbnailimage($size, $sizR);
            }
            if ($imH > $imW) { // portrait page orientation
                $img->thumbnailimage($sizR, $size);
            }

            if (!is_dir(dirname($target))) { // if not there make target directory
                mkdir(dirname($target), 0777, true);
            }

            $img->setImageBackgroundColor('white'); // set background color and flatten, it solves background problems for pdfs with alpha channel
            $img = $img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN); // prevents black zones on transparency in pdf

            $img->setimageformat('jpeg');
            $img->writeimage($target);
            $img->clear();

            if (!file_exists($target)) {
                return null;
            }

            return $target; // return the path to the new file for further processing
        } catch (\ImagickException $exception) {
            return null;
        }
    }
}
