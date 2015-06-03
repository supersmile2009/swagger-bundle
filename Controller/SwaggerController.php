<?php

namespace Draw\SwaggerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SwaggerController extends Controller
{
    public function apiDocAction()
    {
        $swagger = $this->get("draw.swagger");
        $schema = $swagger->extract(json_encode($this->getParameter("draw_swagger.schema")));

        $schema = $swagger->extract($this->container, $schema);

        $jsonSchema = $swagger->dump($schema);

        return new JsonResponse(json_decode($jsonSchema));
    }
}