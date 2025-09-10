<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test successful user registration
     */
    public function test_user_can_register_successfully()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'token'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Registration successful'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name' => $userData['name']
        ]);
    }

    /**
     * Test registration with invalid email
     */
    public function test_registration_fails_with_invalid_email()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => 'invalid-email',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test registration with weak password
     */
    public function test_registration_fails_with_weak_password()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test registration with duplicate email
     */
    public function test_registration_fails_with_duplicate_email()
    {
        $existingUser = User::factory()->create();

        $userData = [
            'name' => $this->faker->name,
            'email' => $existingUser->email,
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test registration with missing required fields
     */
    public function test_registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test successful user login
     */
    public function test_user_can_login_successfully()
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123')
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'token'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Login successful'
                ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123')
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'WrongPassword'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(401)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    /**
     * Test login with non-existent email
     */
    public function test_login_fails_with_nonexistent_email()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(401)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    /**
     * Test login with missing fields
     */
    public function test_login_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test login with invalid email format
     */
    public function test_login_fails_with_invalid_email_format()
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }
}
