<?php

namespace Tests\Feature;

use App\cms_users;
use Tests\TestCase;

class CmsUsersLoginTest extends TestCase
{
    public function testMustEnterEmailAndPassword()
    {
        $this->json('POST', 'instapic/ajax/login')
            ->assertStatus(200)
            ->assertJson([
                'error_description' => 'The password field is required.',
                "error_description" => "The email field is required.",
                'is_success' => false,
            ]);
    }


    public function testSuccessfulLogin()
    {
        $userData = [
            "email" => "doe@example.com",
            "password" => "demo12345"
        ];

        $this->json('POST', 'instapic/ajax/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'is_success' => true,
                'data' => array(
                    'updated_at' => '2020-11-28 16:17:19',
                    'msg' => 'Login Success',
                )
            ]);
    }
}
