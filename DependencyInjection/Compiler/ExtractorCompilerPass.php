<?php

namespace Draw\SwaggerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ExtractorCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $swagger = $container->getDefinition("draw.swagger");

        foreach (array_keys($container->findTaggedServiceIds("swagger.extractor")) as $id) {
            if ($container->getDefinition($id)->isAbstract()) {
                continue;
            }

            $swagger->addMethodCall("registerExtractor", array(new Reference($id)));
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$definition instanceof DefinitionDecorator) {
                continue;
            }

            if ($definition->getParent() != "draw.swagger.extractor.constraint_extractor") {
                continue;
            }

            $swagger->addMethodCall("registerExtractor", array(new Reference($id)));
        }
    }
}