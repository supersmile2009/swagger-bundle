<?php

namespace Draw\SwaggerBundle\DependencyInjection;

use Draw\DrawBundle\Config\Definition\Builder\AllowExtraPropertiesNodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('draw_swagger');
        $rootNode->setBuilder(new AllowExtraPropertiesNodeBuilder());

        $rootNode->children()
            ->arrayNode('definitionAliases')
                ->defaultValue([])
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->isRequired()->end()
                        ->scalarNode('alias')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('schema')
                ->normalizeKeys(false)
                ->acceptExtraKeys(true)
                ->children()
                    ->arrayNode('info')
                        ->children()
                            ->scalarNode('version')->defaultValue('1.0')->end()
                            ->scalarNode('contact')->end()
                            ->scalarNode('termsOfService')->end()
                            ->scalarNode('description')->end()
                            ->scalarNode('title')->end()
                        ->end()
                    ->end()
                    ->scalarNode('openapi')->defaultValue('3.0.0')->end()
                    ->arrayNode('servers')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('url')->end()
                                ->scalarNode('description')->end()
                                ->arrayNode('variables')
                                    ->variablePrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('security')
                        ->variablePrototype()->end()
                    ->end()
                    ->arrayNode('components')
                        ->variablePrototype()->end()
                    ->end()
                    ->arrayNode('tags')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->end()
                                ->scalarNode('description')->end()
                                ->arrayNode('externalDocs')
                                    ->children()
                                        ->scalarNode('description')->end()
                                        ->scalarNode('url')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('excludedReturnTypes')
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
