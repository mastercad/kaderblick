<?php

namespace App\EventSubscriber;

use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener(event: 'kernel.response', method: 'onKernelResponse')]
class JWTResponseSubscriber
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('new_jwt_token')) {
            return;
        }

        $newJwt = $request->attributes->get('new_jwt_token');

        $ttl = (int) $this->parameterBag->get('lexik_jwt_authentication.token_ttl');
        $expires = (new DateTime())->modify("+{$ttl} seconds");

        $cookie = Cookie::create(
            'jwt_token',
            $newJwt,
            $expires,
            '/',
            null,
            true,
            true,
            false,
            Cookie::SAMESITE_STRICT
        );

        $response = $event->getResponse();
        $response->headers->setCookie($cookie);
    }
}
