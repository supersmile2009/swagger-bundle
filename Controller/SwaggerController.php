<?php

namespace Draw\SwaggerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SwaggerController extends Controller
{
    public function apiDocAction()
    {
        $swagger = $this->get("draw.swagger");
        $schema = $swagger->extract($this->container);

        return new JsonResponse(json_decode($swagger->dump($schema)));
    }
}