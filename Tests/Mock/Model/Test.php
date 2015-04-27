<?php

namespace Draw\SwaggerBundle\Tests\Mock\Model;

use JMS\Serializer\Annotation as Serializer;

class Test
{
    /**
     * Property description
     *
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $property;
}