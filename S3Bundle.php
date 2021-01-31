<?php


namespace Reconnect\S3Bundle;


use Symfony\Component\HttpKernel\Bundle\Bundle;
use Reconnect\S3Bundle\DependencyInjection\S3Extension;

class S3Bundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new S3Extension();
        }

        return $this->extension;
    }
}