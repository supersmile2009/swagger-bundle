<?php

namespace Draw\SwaggerBundle;

use Draw\SwaggerBundle\DependencyInjection\Compiler\ExtractorCompilerPass;
use Draw\SwaggerBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawSwaggerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new ExtractorCompilerPass());
    }

    public function boot()
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
    }
}