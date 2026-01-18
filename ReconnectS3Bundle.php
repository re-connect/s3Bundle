<?php

namespace Reconnect\S3Bundle;

use Reconnect\S3Bundle\Adapter\S3Adapter;
use Reconnect\S3Bundle\Service\FlysystemS3Client;
use Reconnect\S3Bundle\Service\PdfService;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ReconnectS3Bundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('bucketHost')->isRequired()->end()
            ->scalarNode('bucketName')->isRequired()->end()
            ->scalarNode('bucketKey')->isRequired()->end()
            ->scalarNode('bucketSecret')->isRequired()->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()->set(S3Adapter::class)
            ->arg(0, $config['bucketHost'])
            ->arg(1, $config['bucketName'])
            ->arg(2, $config['bucketKey'])
            ->arg(3, $config['bucketSecret'])
            ->autoconfigure()->autowire()
        ;

        $container->services()->set(FlysystemS3Client::class)->autoconfigure()->autowire();

        $container->services()->set(PdfService::class)->autoconfigure()->autowire();
    }
}
