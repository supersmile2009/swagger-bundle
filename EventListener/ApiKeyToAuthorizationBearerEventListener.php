<?php

namespace Draw\SwaggerBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ApiKeyToAuthorizationBearerEventListener
{
    public function listenRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }

        $request = $event->getRequest();

        if(!$request->query->has('api_key')) {
            return;
        }

        $request->headers->add(
            array(
                'authorization' => array(
                    'Bearer ' . $request->query->get('api_key')
                )
            )
        );
    }
}