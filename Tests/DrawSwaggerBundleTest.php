<?php

namespace Draw\SwaggerBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DrawSwaggerBundleTest extends WebTestCase
{
    public function testGetService()
    {
        $swagger = static::createClient()->getContainer()->get("draw.swagger");

        $this->assertInstanceOf('Draw\Swagger\Swagger', $swagger);

        return $swagger;
    }

    public function testSwaggerControllerApiDoc()
    {
        $client = static::createClient();
        $client->request('GET', '/api-doc');
        $client->getResponse()->getContent();
    }
}