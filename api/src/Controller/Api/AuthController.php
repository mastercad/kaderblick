<?php

namespace App\Controller\Api;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Entity\UserRelation;
use App\Service\RefreshTokenService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api', name: 'api_')]
class AuthController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
        EntityManagerInterface $entityManager,
        RefreshTokenService $refreshTokenService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        if (!$user->isVerified()) {
            return new JsonResponse(['error' => 'User not verified'], 401);
        }

        $accessToken = $JWTManager->create($user);
        $refreshToken = $refreshTokenService->createRefreshToken($user);

        $ttl = $this->getParameter('lexik_jwt_authentication.token_ttl');
        $expireDate = (new DateTime())->modify("+{$ttl} seconds");
        $expireTimestamp = $expireDate->getTimestamp();

        $response = new JsonResponse(['token' => $accessToken]);
        $response->headers->setCookie(
            new Cookie(
                'jwt_token',
                $accessToken,
                $expireTimestamp,
                '/',
                null,
                true,
                true,
                false,
                'strict'
            ),
        );

        $response->headers->setCookie(
            new Cookie(
                'jwt_refresh_token',
                $refreshToken,
                new DateTime('+7 days'),
                '/',
                null,
                true,
                true,
                false,
                'strict'
            ),
        );

        return $response;
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logoutAction(Request $request): JsonResponse
    {
        $response = new JsonResponse(['logged out' => true], 200);

        $request->attributes->remove('new_jwt_token');
        $response->headers->clearCookie('jwt_token', '/', '', true, true, 'lax');
        $response->headers->clearCookie('jwt_refresh_token', '/', '', true, true, 'lax');

        return $response;
    }

    #[Route(path: '/token/refresh', name: 'token_refresh')]
    public function refreshTokenAction(Request $request, EntityManagerInterface $em, JWTManager $jwtManager): JsonResponse
    {
        $refreshTokenString = $request->cookies->get('jwt_refresh_token');

        if (!$refreshTokenString) {
            return new JsonResponse(['error' => 'No refresh token'], 401);
        }

        $token = $em->getRepository(RefreshToken::class)->findOneBy(['token' => $refreshTokenString]);

        if (!$token || $token->isExpired()) {
            return new JsonResponse(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $token->getUser();
        $jwt = $jwtManager->create($user);

        return new JsonResponse(['token' => $jwt]);
    }

    #[Route('/about/me', name: 'about_me')]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $isCoach = false;
        $isPlayer = false;
        /** @var UserRelation $userRelation */
        foreach ($user->getUserRelations() as $userRelation) {
            if ('coach' === $userRelation->getRelationType()->getCategory()) {
                $isCoach = true;
            } elseif ('player' === $userRelation->getRelationType()->getCategory()) {
                $isPlayer = true;
            }
            if ($isCoach && $isPlayer) {
                break;
            }
        }

        return $this->json([
            'id' => $user->getId(),
            'isCoach' => $isCoach,
            'isPlayer' => $isPlayer,
            'email' => $user->getUserIdentifier(),
        ]);
    }
}
