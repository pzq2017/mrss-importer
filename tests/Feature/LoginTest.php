<?php

namespace Tests\Feature;

use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use TestCase;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create([
            'password' => Hash::make('ABCabc01'),
        ]);
    }

    /**
     * @test
     */
    public function login_missing_required_values_failed()
    {
        $this->json('POST', '/api/login', ['email' => '', 'password' => ''])
            ->seeJson([
                'status' => 'error',
            ])
            ->seeJsonStructure([
                'message'=> ['email', 'password']
            ])
            ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function login_with_wrong_values_failed()
    {
        $this->json('POST', '/api/login', ['email' => 'test@121.com', 'password' => '123456'])
            ->seeJson([
                'status' => 'error',
                'message'=> 'wrong email or password.'
            ])
            ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function login_success()
    {
        $this->json('POST', '/api/login', ['email' => $this->user->email, 'password' => 'ABCabc01'])
            ->seeJson([
                'status' => 'success'
            ])
            ->seeJsonStructure([
                'data'=> ['id', 'name', 'email', 'token', 'expire_at']
            ])
            ->assertResponseStatus(200);
    }
}
