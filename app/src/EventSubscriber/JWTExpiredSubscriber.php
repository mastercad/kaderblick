<?php

namespace App\EventSubscriber;

use App\Entity\RefreshToken;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

// #[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_expired', method: "onJwtExpired", priority: 30)]
class JWTExpiredSubscriber
{
    public function __construct(private EntityManagerInterface $entityManager, private JWTTokenManagerInterface $jwtManager, private ParameterBagInterface $params)
    {
    }

    public function onJwtExpired(JWTExpiredEvent $event): void
    {
        $request = $event->getRequest();
        $refreshTokenString = $request->cookies->get('jwt_refresh_token');

        if (!$refreshTokenString) {
            $response = new JsonResponse(['error' => 'No refresh token'], 401);
            $response->headers->clearCookie('jwt_token');
            $response->headers->clearCookie('jwt_refresh_token');
            $event->setResponse($response);

            return;
        }

        $token = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['token' => $refreshTokenString]);

        if (!$token || $token->isExpired()) {
            $response = new JsonResponse(['error' => 'Invalid or expired refresh token'], 401);
            $response->headers->clearCookie('jwt_token');
            $response->headers->clearCookie('jwt_refresh_token');
            $event->setResponse($response);

            return;
        }

        $user = $token->getUser();
        $jwt = $this->jwtManager->create($user);

        $ttl = $this->params->get('lexik_jwt_authentication.token_ttl');
        $expireDate = (new DateTime())->modify("+{$ttl} seconds");
        $expireTimestamp = $expireDate->getTimestamp();

        $response = $event->getResponse();

        dump($response->headers->getCookies());

        $response->headers->setCookie(
            new Cookie(
                'jwt_token',
                $jwt,
                $expireTimestamp,
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            ),
        );

        $event->setResponse($response);
    }
}
