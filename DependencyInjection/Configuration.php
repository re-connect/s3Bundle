<?php

namespace Reconnect\S3Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('reconnect_s3_bundle');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->children()
            ->scalarNode('bucketHost')->isRequired()->end()
            ->scalarNode('bucketName')->isRequired()->end()
            ->scalarNode('bucketKey')->isRequired()->end()
            ->scalarNode('bucketSecret')->isRequired()->end()
            ->end();

        return $treeBuilder;
    }
}
