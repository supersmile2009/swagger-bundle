<?php

namespace Draw\SwaggerBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\DrawBundle\Serializer\GroupHierarchy;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use ReflectionMethod;

class FOSRestViewOperationExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var GroupHierarchy
     */
    private $groupHierarchy;

    public function __construct(Reader $reader, GroupHierarchy $groupHierarchy)
    {
        $this->annotationReader = $reader;
        $this->groupHierarchy = $groupHierarchy;
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
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionMethod) {
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

        $groups = [];

        if ($view = $this->annotationReader->getMethodAnnotation($method, View::class)) {
            $groups = $view->getSerializerGroups();
        }

        if (empty($groups)) {
            $groups = [GroupsExclusionStrategy::DEFAULT_GROUP];
        }

        $groups = $this->groupHierarchy->getReachableGroups($groups);

        $modelContext = $extractionContext->getParameter('out-model-context', []);
        $modelContext['serializer-groups'] = $groups;
        $extractionContext->setParameter('out-model-context', $modelContext);
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     *
     * @return View|null
     */
    public function getView(ReflectionMethod $reflectionMethod)
    {
        $views = array_filter(
            $this->annotationReader->getMethodAnnotations($reflectionMethod),
            function ($annotation) {
                return $annotation instanceof View;
            }
        );

        if (!empty($views)) {
            return reset($views);
        }

        return null;
    }
}
