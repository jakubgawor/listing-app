<?php

namespace App\Tests\functional\Controller\Auth;

use App\Tests\Utils\EntityBuilder;

class SecurityControllerTest extends EntityBuilder
{
    /** @test */
    public function logged_user_is_redirected_if_trying_to_access_login_page()
    {
        $this->client->loginUser($this->createUser())->request('GET', '/login');

        $this->assertResponseRedirects('/');
    }

    /** @test */
    public function logout_works_correctly()
    {
        $this->client->loginUser($this->createUser())->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    /** @test */
    public function user_can_not_logout_while_not_logged_in()
    {
        $this->client->loginUser($this->createUser())->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302);
    }

    /** @test */
    public function user_can_not_render_login_page_while_logged_in()
    {
        $this->client->loginUser($this->createUser())->request('GET', '/login');

        $this->assertResponseRedirects('/', 302);
    }
}
