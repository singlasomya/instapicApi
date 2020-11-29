<?php

namespace Tests\Feature;

use App\cms_users;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use Dirape\Token\Token;


class CmsUploadImage extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testItFetchesPosts()
    {
        $user_token = (new Token())->Unique('cms_users', 'user_token', 60);

        $user = cms_users::first();
        $this->actingAs($user->username)->json('POST', '/instapic/ajax/upload', [
            'remark' => 'Todo Description',
            'user_token' => $user_token,
            'preview_url' => \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg')
        ]);
        $api_token = $request->api_token;
        $remark = $request->remark;


        //Storage::disk('s3')->assertExists('uploads/' . $_SESSION['testing']);
    }

    /*public function testSuccessfulRegistration()
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
    }*/
}
