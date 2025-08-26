<?php

namespace App\Security;

use App\Entity\User;
use App\Service\RefreshTokenService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Twig\Environment;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;
    private JWTTokenManagerInterface $jwtManager;
    private RefreshTokenService $refreshTokenService;
    private int $jwtTtl;
    private Environment $twig;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenService $refreshTokenService,
        Environment $twig,
        int $jwtTtl = 3600
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->jwtManager = $jwtManager;
        $this->refreshTokenService = $refreshTokenService;
        $this->jwtTtl = $jwtTtl;
        $this->twig = $twig;
    }

    public function supports(Request $request): ?bool
    {
        return 'connect_google_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();
        $googleUser = $client->fetchUserFromToken($accessToken);
        $googleUserData = $googleUser->toArray();
        $googleId = $googleUser->getId();
        $email = $googleUserData['email'];

        return new SelfValidatingPassport(
            new UserBadge($googleId, function () use ($googleId, $email, $googleUserData) {
                $user = $this->em->getRepository(User::class)->findOneBy(['googleId' => $googleId]);
                if (!$user) {
                    $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
                    if ($user) {
                        $user->setGoogleId($googleId);
                    } else {
                        $user = new User();
                        $user->setEmail($email);
                        $user->setGoogleId($googleId);
                        $user->setFirstName($googleUserData['given_name']);
                        $user->setLastName($googleUserData['family_name']);
                        $user->setPassword('!');
                        $user->setRoles(['ROLE_USER']);
                        $user->setIsVerified(true);
                        $user->setIsEnabled(true);
                    }
                    $this->em->persist($user);
                    $this->em->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        /** @var User $user */
        $user = $token->getUser();
        $accessToken = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenService->createRefreshToken($user);

        $expireDate = (new DateTime())->modify("+{$this->jwtTtl} seconds");
        $expireTimestamp = $expireDate->getTimestamp();

        $response = new Response(
            $this->twig->render('security/google_success.html.twig')
        );
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

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        return new RedirectResponse('/login?error=google');
    }

    /**
     * @return array<string, string>
     */
    public function splitNameToFirstAndLast(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name));
        if (!$parts) {
            return ['first' => '', 'last' => ''];
        }
        $last = array_pop($parts);
        $first = implode(' ', $parts);

        return [
            'first' => $first,
            'last' => $last
        ];
    }
}
