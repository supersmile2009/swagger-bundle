<?php

namespace Draw\SwaggerBundle\Tests\Mock\Controller;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;

class TestController extends FOSRestController
{
    /**
     * @FOS\Get("/tests/{id}")
     * @FOS\QueryParam(name="filter", description="fos description")
     * @FOS\RequestParam(name="object")
     *
     * @param string $id Php doc description
     * @param string $filter Should not be used since define in QueryParam
     * @param \Draw\SwaggerBundle\Tests\Mock\Model\Test $object Object parameter
     *
     * @return \Draw\SwaggerBundle\Tests\Mock\Model\Test
     */
    public function getAction($object, $id, $filter = null)
    {

    }
}