<?php

namespace Tests\Feature;

use App\cms_users;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CmsUsersTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRequiredFieldsForRegistration()
    {

        $this->json('POST', 'instapic/ajax/register', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'error_description' => 'The name field is required.',
                'error_description' => 'The password field is required.',
                "error_description" => "The email field is required.",
                'is_success' => false,
            ]);
    }

    public function testSuccessfulRegistration()
    {
        $userData = [
            "name" => "John Doe",
            "email" => "doe@example.com",
            "password" => "demo12345"
        ];

        $this->json('POST', 'instapic/ajax/register', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                'is_success' => true
            ]);
    }
}
