<?php

namespace Draw\SwaggerBundle\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\MediaType;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\QueryParameter;
use Draw\Swagger\Schema\RequestBody;
use Draw\Swagger\Schema\Schema;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamReaderInterface;

class FOSRestRouteOperationExtractor implements ExtractorInterface
{
    /**
     * @var ParamReaderInterface
     */
    private $paramReader;

    public function __construct(ParamReaderInterface $paramReader)
    {
        $this->paramReader = $paramReader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     *
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof \ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
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
     * @param \ReflectionMethod $method
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($method, &$operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach ($this->paramReader->read($method->getDeclaringClass(), $method->getName()) as $paramName => $param) {
            /* @var \FOS\RestBundle\Controller\Annotations\AbstractScalarParam $param */

            $parameter = null;
            if ($param instanceof QueryParam) {
                $parameter = new QueryParameter();
                $parameter->schema = new Schema();
                //TODO: extract parameter constraints.
                $operation->parameters[] = $parameter;
                $parameter->name = $paramName;
                // Param is really required only if it's not nullable and has no default value.
                if ($param->nullable === false && $param->default === null) {
                    $parameter->required = true;
                }
            } elseif ($param instanceof RequestParam) {
                $parameter = $operation->requestBody;

                if ($parameter === null) {
                    $parameter = new RequestBody();
                    $parameter->content['application/json'] = new MediaType();
                    $parameter->content['application/json']->schema = new Schema();
                    $parameter->content['application/json']->schema->type = 'object';
                    $operation->requestBody = $parameter;
                }

                $propertySchema = new Schema();
                $parameter->content['application/json']->schema->properties[$paramName] = $propertySchema;
                if (!$param->nullable) {
                    $parameter->content['application/json']->schema->required[] = $paramName;
                }
            }

            $parameter->default = $param->default;
            $parameter->description = $param->description;
        }
    }
}
