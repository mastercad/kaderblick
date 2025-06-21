<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\RefreshTokenService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

##[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
class JWTExceptionSubscriber
{
    public function __construct(
        private RefreshTokenService $refreshTokenService,
        private JWTTokenManagerInterface $JWTManager,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $params
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof AuthenticationException
            && !$exception instanceof AccessDeniedException
        ) {
            return;
        }

        $request = $event->getRequest();

        $refreshToken = $request->cookies->get('jwt_refresh_token');
        if (!$refreshToken) {
            return;
        }

        $validToken = $this->refreshTokenService->validateRefreshToken($refreshToken);
        if (!$validToken) {
            return;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $validToken->getEmail()]);
        if (!$user) {

            dump($refreshToken);
            return;
        }

        $newJwt = $this->JWTManager->create($user);
        $newRefresh = $this->refreshTokenService->createRefreshToken($user);

        $ttl = $this->params->get('lexik_jwt_authentication.token_ttl');
        $expireDate = (new DateTime())->modify("+{$ttl} seconds");
        $expireTimestamp = $expireDate->getTimestamp();

        $response = new JsonResponse(['token' => $newJwt, 'refresh_token' => $newRefresh]);
        $response->headers->setCookie(
            new Cookie(
                'jwt_token',
                $newJwt,
                $expireTimestamp,
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            )
        );

        $response->headers->setCookie(
            new Cookie(
                'jwt_refresh_token',
                $newRefresh,
                new DateTime('+7 days'),
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            )
        );
        
        $event->setResponse($response);
    }
}
