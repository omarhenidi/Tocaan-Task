<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RoleSeeder']);
    }

    public function test_register_returns_jwt_and_201(): void
    {
        $response = $this->postJson('/api/v1/client/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                    'user' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJson(['success' => true, 'data' => ['token_type' => 'bearer']]);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/client/auth/register', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_returns_jwt_for_valid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => bcrypt('secret123')]);

        $response = $this->postJson('/api/v1/client/auth/login', [
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['token', 'user']])
            ->assertJson(['success' => true]);
    }

    public function test_login_returns_401_for_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/client/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_me_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->getJson('/api/v1/client/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.email', $user->email);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/client/auth/me');

        $response->assertStatus(401);
    }
}
