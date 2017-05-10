<?php

namespace Draw\SwaggerBundle\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\BodyParameter;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\QueryParameter;
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
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if(!$source instanceof \ReflectionMethod) {
            return false;
        }

        if(!$type instanceof Operation) {
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
     * @param \ReflectionMethod $source
     * @param Operation $type
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $type, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        foreach($this->paramReader->read($source->getDeclaringClass(), $source->getName()) as $paramName => $param) {
            /* @var \FOS\RestBundle\Controller\Annotations\Param $param */

            $parameter = null;
            if($param instanceof QueryParam) {
                $parameter = new QueryParameter();
                $type->parameters[] = $parameter;
                $parameter->name = $paramName;
            } elseif($param instanceof RequestParam) {
                foreach($type->parameters as $currentParameter) {
                    if($currentParameter instanceof BodyParameter) {
                        $parameter = $currentParameter;
                        break;
                    }
                }

                if(is_null($parameter)) {
                    $parameter = new BodyParameter();
                    $parameter->schema = new Schema();
                    $parameter->schema->type = "object";
                    $type->parameters[] = $parameter;
                }

                $propertySchema = new Schema();
                $parameter->schema->properties[$paramName] = $propertySchema;
                if(!$param->nullable) {
                    $parameter->schema->required[] = $paramName;
                }
                //$parameter = $propertySchema;
            }

            $parameter->default = $param->default;
            $parameter->description = $param->description;
        }
    }
}