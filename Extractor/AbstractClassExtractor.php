<?php

namespace Draw\SwaggerBundle\Extractor;

use Doctrine\ORM\EntityManager;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Schema;

class AbstractClassExtractor implements ExtractorInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $className
     * @param Schema $target
     * @param ExtractionContextInterface $subContext
     *
     * @throws \ReflectionException
     * @throws \Draw\Swagger\Extraction\ExtractionImpossibleException
     */
    public function extract($className, &$target, ExtractionContextInterface $subContext)
    {
        if (!$this->canExtract($className, $target, $subContext)) {
            return;
        }

        $reflectionClass = new \ReflectionClass($className);

        $metadata = $this->em->getClassMetadata($reflectionClass->getName());
        foreach ($metadata->subClasses as $subClassName) {
            $targetSchema = clone $target;
            $subContext->getSwagger()->extract(
                $subClassName,
                $targetSchema,
                clone $subContext
            );
        }
    }

    /**
     * @param mixed $className
     * @param mixed $target
     * @param ExtractionContextInterface $subContext
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function canExtract($className, $target, ExtractionContextInterface $subContext): bool
    {
        if (\is_string($className) && \class_exists($className)) {
            $reflectionClass = new \ReflectionClass($className);
            return $reflectionClass->isAbstract();
        }
        return false;
    }


}
