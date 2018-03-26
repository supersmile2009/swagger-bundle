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
                $parameter->schema = $parameterSchema = new Schema();
                //TODO: extract parameter constraints.
                $operation->parameters[] = $parameter;
                $parameter->name = $paramName;
                // Param is really required only if it's not nullable and has no default value.
                if ($param->nullable === false && $param->default === null) {
                    $parameter->required = true;
                }
                $parameter->description = $param->description;
            } elseif ($param instanceof RequestParam) {
                $requestBody = $operation->requestBody;

                if ($requestBody === null) {
                    $requestBody = new RequestBody();
                    $requestBody->content['application/json'] = new MediaType();
                    $requestBody->content['application/json']->schema = new Schema();
                    $requestBody->content['application/json']->schema->type = 'object';
                    $operation->requestBody = $requestBody;
                }

                $parameterSchema = new Schema();
                $requestBody->content['application/json']->schema->properties[$paramName] = $parameterSchema;
                if (!$param->nullable) {
                    $requestBody->content['application/json']->schema->required[] = $paramName;
                }
                $parameterSchema->description = $param->description;
            }

            $parameterSchema->default = $param->default;
        }
    }
}
