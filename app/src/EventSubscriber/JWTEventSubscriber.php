<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JWTEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_jwt_not_found' => 'onJWTNotFound',
            'lexik_jwt_authentication.on_authentication_failure' => 'onAuthenticationFailure'
        ];
    }

    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        $response = new RedirectResponse($this->router->generate('app_dashboard'));
        $event->setResponse($response);
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $response = new RedirectResponse($this->router->generate('app_dashboard'));
        $event->setResponse($response);
    }
}
