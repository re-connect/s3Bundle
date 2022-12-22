<?php

namespace Reconnect\S3Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class S3Extension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('s3_bundle.s3_adapter');
        $definition->setArguments([
            $config['bucketHost'],
            $config['bucketName'],
            $config['bucketKey'],
            $config['bucketSecret']
        ]);
    }

    public function getAlias(): string
    {
        return 'reconnect_s3_bundle';
    }
}
