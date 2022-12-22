<?php

namespace Reconnect\S3Bundle;

use Reconnect\S3Bundle\DependencyInjection\S3Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class S3Bundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new S3Extension();
        }

        return $this->extension;
    }
}
