<?php

namespace Draw\SwaggerBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\DrawBundle\Serializer\GroupHierarchy;
use Draw\Swagger\Schema\MediaType;
use Draw\Swagger\Schema\RequestBody;
use Draw\Swagger\Schema\Schema;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ParamConverterExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var GroupHierarchy
     */
    private $groupHierarchy;

    public function __construct(Reader $reader, GroupHierarchy $groupHierarchy)
    {
        $this->reader = $reader;
        $this->groupHierarchy = $groupHierarchy;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
            return false;
        }

        if (!$this->getParamConverter($source)) {
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
     * @param ReflectionMethod $method
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     *
     * @return void
     * @throws ExtractionImpossibleException
     */
    public function extract($method, &$operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            return;
        }

        $paramConverter = $this->getParamConverter($method);
        if (null === $type = $paramConverter->getClass()) {
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->getName() !== $paramConverter->getName()) {
                    continue;
                }
                $type = $parameter->getClass()->getName();
            }
        }

        $operation->requestBody = $requestBody = new RequestBody();
        $requestBody->content['application/json'] = $mediaType = new MediaType();

        $subContext = $extractionContext->createSubContext();

        $subContext->setParameter('direction', 'in');

        if ($serializationGroups = $this->getDeserializationGroups($paramConverter)) {
            $serializationGroups = $this->groupHierarchy->getReachableGroups($serializationGroups);
        }
        $operation->setCustomProperty('deserializationGroups', $serializationGroups);

        $subContext->setParameter(
            'validation-groups',
            $validationGroups = $this->getValidationGroups($paramConverter)
        );

        $modelContext = $subContext->getParameter('in-model-context', []);

        if ($serializationGroups) {
            $modelContext['serializer-groups'] = $serializationGroups;
        }

        if ($validationGroups) {
            $modelContext['validation-groups'] = $validationGroups;
        }

        $subContext->setParameter('in-model-context', $modelContext);

        $mediaType->schema = new Schema();
        $subContext->getSwagger()->extract(
            $type,
            $mediaType->schema,
            $subContext
        );

        $mediaType->schema->type = 'object';
    }

    private function getDeserializationGroups(ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if (isset($options['deserializationContext']['groups'])) {
            return $options['deserializationContext']['groups'];
        }

        return array(GroupsExclusionStrategy::DEFAULT_GROUP);
    }

    private function getValidationGroups(ParamConverter $paramConverter)
    {
        $options = $paramConverter->getOptions();
        if (isset($options['validator']['groups'])) {
            return $options['validator']['groups'];
        }

        return [];
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter
     */
    private function getParamConverter(ReflectionMethod $reflectionMethod)
    {
        $converters = array_filter(
            $this->reader->getMethodAnnotations($reflectionMethod),
            function ($converter) {
                if (!$converter instanceof ParamConverter) {
                    return false;
                }

                return $converter->getConverter() === 'fos_rest.request_body';
            }
        );

        return reset($converters);
    }
}
