<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;

class JWTLoginSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getData()['token'];

        $response = $event->getResponse();
        $response->headers->setCookie(
            Cookie::create('jwt_token')
                ->withValue($token)
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withPath('/')
                ->withSameSite('lax')
        );
    }
}
