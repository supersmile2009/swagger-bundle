<?php

namespace Draw\SwaggerBundle\Routing\Loader\Reader;

class RestControllerReader extends \FOS\RestBundle\Routing\Loader\Reader\RestControllerReader
{
    public function read(\ReflectionClass $reflectionClass)
    {
        $collection = parent::read($reflectionClass);

        foreach($collection as $route) {
            $route->setDefault('_swagger', true);
        }

        return $collection;
    }
}