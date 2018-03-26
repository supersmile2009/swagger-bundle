<?php

namespace Draw\SwaggerBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\PathItem;
use Draw\Swagger\Schema\Tag;
use Draw\Swagger\Schema\OpenApi;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class SymfonyContainerSwaggerExtractor implements ExtractorInterface
{
    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof ContainerInterface) {
            return false;
        }

        if (!$target instanceof OpenApi) {
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
     * @param OpenApi $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \ReflectionException
     * @throws ExtractionImpossibleException
     */
    public function extract($source, &$target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            return;
        }

        $this->triggerRouteExtraction($source->get('router'), $target, $extractionContext);
    }

    /**
     * @param RouterInterface $router
     * @param OpenApi $schema
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws \ReflectionException
     * @throws ExtractionImpossibleException
     */
    private function triggerRouteExtraction(RouterInterface $router, OpenApi $schema, ExtractionContextInterface $extractionContext)
    {
        foreach ($router->getRouteCollection() as $operationId => $route) {
            /* @var \Symfony\Component\Routing\Route $route */
            if (!($path = $route->getPath())) {
                continue;
            }

            $controller = explode('::', $route->getDefault('_controller'));

            if (\count($controller) !== 2) {
                continue;
            }

            list($class, $method) = $controller;

            $reflectionMethod = new \ReflectionMethod($class, $method);

            if (!$this->isSwaggerRoute($route, $reflectionMethod)) {
                continue;
            }

            $operation = new Operation();

            $operation->operationId = $operationId;

            $extractionContext->getSwagger()->extract($route, $operation, $extractionContext);
            $extractionContext->getSwagger()->extract($reflectionMethod, $operation, $extractionContext);

            if (!isset($schema->paths[$path])) {
                $schema->paths[$path] = new PathItem();
            }

            $pathItem = $schema->paths[$path];

            foreach ($route->getMethods() as $method) {
                $pathItem->{strtolower($method)} = $operation;
            }
        }
    }

    private function isSwaggerRoute(Route $route, \ReflectionMethod $method)
    {
        if ($route->getDefault('_swagger')) {
            return true;
        }

        foreach ($this->annotationReader->getMethodAnnotations($method) as $annotation) {
            if ($annotation instanceof Tag) {
                return true;
            }
        }

        return false;
    }
}
