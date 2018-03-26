<?php

namespace Draw\SwaggerBundle\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\PathParameter;
use Draw\Swagger\Schema\Reference;
use Draw\Swagger\Schema\Schema;
use Symfony\Component\Routing\Route;

class RouteOperationExtractor implements ExtractorInterface
{

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
        if (!$source instanceof Route) {
            return false;
        }

        if (!$target instanceof Operation) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. An extractor can be called before you and you must complete the
     * extraction.
     *
     * @param Route $route
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($route, &$operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($route, $operation, $extractionContext)) {
            return;
        }

        foreach($route->compile()->getPathVariables() as $pathVariable) {
            foreach($operation->parameters as $parameter) {
                if($parameter->name === $pathVariable) {
                    continue 2;
                }
            }
            $pathParameter = new PathParameter();
            $pathParameter->name = $pathVariable;
            $pathParameter->schema = new Schema();
            $pathParameter->schema->type = 'string';
            $operation->parameters[] = $pathParameter;
        }


    }
}
