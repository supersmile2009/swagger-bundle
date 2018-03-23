<?php

namespace Draw\SwaggerBundle\Controller;

use Draw\Swagger\Schema\OpenApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SwaggerController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Draw\Swagger\Extraction\ExtractionImpossibleException
     */
    public function apiDocAction(Request $request)
    {
        if ($request->attributes->get('_format') !== 'json') {
            $currentRoute = $request->attributes->get('_route');
            $currentUrl = $this->get('router')
                ->generate($currentRoute, ['_format' => 'json'], UrlGeneratorInterface::NETWORK_PATH);
            return new RedirectResponse('http://petstore.swagger.io/?url='.$currentUrl);
        }

        $swagger = $this->get('draw.swagger');
        /** @var OpenApi $schema */
        $schema = $swagger->extract(json_encode($this->getParameter('draw_swagger.schema')));

        //set host dynamically.
        $schema->servers[0]->url = $request->getSchemeAndHttpHost();

        $schema = $swagger->extract($this->container, $schema);
        $jsonSchema = $swagger->dump($schema);

        return new JsonResponse($jsonSchema, 200, [], true);
    }
}
