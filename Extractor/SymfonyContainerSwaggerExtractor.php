<?php

namespace Draw\SwaggerBundle\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\PathItem;
use Draw\Swagger\Schema\Swagger as SwaggerSchema;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class SymfonyContainerSwaggerExtractor implements ExtractorInterface
{

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ContainerInterface) {
            return false;
        }

        if (!$type instanceof SwaggerSchema) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ContainerInterface $source
     * @param SwaggerSchema $type
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $type, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $this->triggerRouteExtraction($source->get('router'), $type, $extractionContext);
    }

    private function triggerRouteExtraction(RouterInterface $router, SwaggerSchema $schema, ExtractionContextInterface $extractionContext)
    {
        foreach ($router->getRouteCollection() as $route) {
            /* @var \Symfony\Component\Routing\Route $route */
            if (!$route->getDefault('_swagger')) {
                continue;
            }

            if(!($path = $route->getPath())) {
                continue;
            }

            list($class, $method) = explode('::', $route->getDefault('_controller'));

            $reflectionMethod = new \ReflectionMethod($class, $method);
            $operation = new Operation();

            $extractionContext->getSwagger()->extract($route, $operation, $extractionContext);
            $extractionContext->getSwagger()->extract($reflectionMethod, $operation, $extractionContext);

            if(!isset($schema->paths[$path])) {
                $schema->paths[$path] = new PathItem();
            }

            $pathItem = $schema->paths[$path];

            foreach($route->getMethods() as $method) {
                $pathItem->{strtolower($method)} = $operation;
            }
        }
    }
}