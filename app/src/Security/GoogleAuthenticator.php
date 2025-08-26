<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
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
        $name = $googleUserData['name'];

        return new SelfValidatingPassport(
            new UserBadge($googleId, function () use ($googleId, $email, $name) {
                $user = $this->em->getRepository(User::class)->findOneBy(['googleId' => $googleId]);
                if (!$user) {
                    $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
                    if ($user) {
                        $user->setGoogleId($googleId);
                    } else {
                        $nameParts = $this->splitNameToFirstAndLast($name);
                        $user = new User();
                        $user->setEmail($email);
                        $user->setGoogleId($googleId);
                        $user->setFirstName($nameParts['first']);
                        $user->setLastName($nameParts['last']);
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

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        return new RedirectResponse('/');
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
