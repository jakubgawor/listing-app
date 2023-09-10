<?php

namespace App\Tests\integration\Security;

use App\Security\LoginFormAuthenticator;
use App\Tests\Utils\EntityBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticatorTest extends EntityBuilder
{
    private UrlGeneratorInterface $urlGenerator;

    public function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    }

    /** @test */
    public function authenticate_works_correctly()
    {
        $request = new Request([], [
            '_username' => 'username',
            '_password' => 'password',
            '_csrf_token' => 'token',
        ]);

        $request->setSession(new Session(new MockArraySessionStorage()));

        $authenticator = new LoginFormAuthenticator($this->urlGenerator);
        $passport = $authenticator->authenticate($request);

        $this->assertSame('username', $request->getSession()->get('_security.last_username'));
        $this->assertInstanceOf(Passport::class, $passport);
    }
}