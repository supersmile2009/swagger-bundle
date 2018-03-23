<?php

namespace Draw\SwaggerBundle\Extractor;

use Doctrine\ORM\EntityManager;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
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
     * @throws ExtractionImpossibleException
     */
    public function extract($className, &$target, ExtractionContextInterface $subContext)
    {
        if (!$this->canExtract($className, $target, $subContext)) {
            throw new ExtractionImpossibleException();
        }
        $reflectionClass = new \ReflectionClass($className);

        $metadata = $this->em->getClassMetadata($reflectionClass->getName());
        foreach ($metadata->subClasses as $className) {
            $targetSchema = clone $target;
            $subContext->getSwagger()->extract(
                $className,
                $targetSchema,
                clone $subContext
            );
        }
    }
    public function canExtract($className, $target, ExtractionContextInterface $subContext)
    {
        if (is_string($className) && class_exists($className)) {
            $reflectionClass = new \ReflectionClass($className);
            return $reflectionClass->isAbstract();
        }
        return false;
    }


}
