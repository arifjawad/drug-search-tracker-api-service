<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that protected routes require authentication
     */
    public function test_protected_routes_require_authentication()
    {
        // Test GET /api/v1/user/drugs without authentication
        $response = $this->getJson('/api/v1/user/drugs');
        $response->assertStatus(401);

        // Test POST /api/v1/user/drugs without authentication
        $response = $this->postJson('/api/v1/user/drugs', ['rxcui' => '12345']);
        $response->assertStatus(401);

        // Test DELETE /api/v1/user/drugs/12345 without authentication
        $response = $this->deleteJson('/api/v1/user/drugs/12345');
        $response->assertStatus(401);
    }

    /**
     * Test that public routes don't require authentication
     */
    public function test_public_routes_dont_require_authentication()
    {
        // Test GET /api/v1/drugs/search without authentication (should work)
        $response = $this->getJson('/api/v1/drugs/search?drug_name=aspirin');
        $response->assertStatus(200);
    }

    /**
     * Test that authenticated routes work with valid token
     */
    public function test_authenticated_routes_work_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        // Test GET /api/v1/user/drugs with authentication
        $response = $this->getJson('/api/v1/user/drugs', $headers);
        $response->assertStatus(200);

        // Test POST /api/v1/user/drugs with authentication
        $response = $this->postJson('/api/v1/user/drugs', ['rxcui' => '12345'], $headers);
        $response->assertStatus(201);

        // Test DELETE /api/v1/user/drugs/12345 with authentication
        $response = $this->deleteJson('/api/v1/user/drugs/12345', [], $headers);
        $response->assertStatus(404); // 404 because medication doesn't exist, but auth works
    }

    /**
     * Test that invalid token is rejected
     */
    public function test_invalid_token_is_rejected()
    {
        $headers = [
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json'
        ];

        $response = $this->getJson('/api/v1/user/drugs', $headers);
        $response->assertStatus(401);
    }

    /**
     * Test that malformed authorization header is rejected
     */
    public function test_malformed_authorization_header_is_rejected()
    {
        $headers = [
            'Authorization' => 'InvalidFormat token',
            'Accept' => 'application/json'
        ];

        $response = $this->getJson('/api/v1/user/drugs', $headers);
        $response->assertStatus(401);
    }

    /**
     * Test that missing authorization header is rejected
     */
    public function test_missing_authorization_header_is_rejected()
    {
        $headers = [
            'Accept' => 'application/json'
        ];

        $response = $this->getJson('/api/v1/user/drugs', $headers);
        $response->assertStatus(401);
    }

    /**
     * Test rate limiting on search endpoint
     */
    public function test_rate_limiting_on_search_endpoint()
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/drugs/search?drug_name=aspirin');

            if ($i < 5) { // Assuming rate limit is 5 requests per minute
                $response->assertStatus(200);
            } else {
                // After rate limit is exceeded, should return 429
                $response->assertStatus(429);
            }
        }
    }

    /**
     * Test rate limiting on authenticated endpoints
     */
    public function test_rate_limiting_on_authenticated_endpoints()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/user/drugs', $headers);

            if ($i < 5) { // Assuming rate limit is 5 requests per minute
                $response->assertStatus(200);
            } else {
                // After rate limit is exceeded, should return 429
                $response->assertStatus(429);
            }
        }
    }

    /**
     * Test that different users have separate rate limits
     */
    public function test_different_users_have_separate_rate_limits()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $token1 = $user1->createToken('test-token-1')->plainTextToken;
        $token2 = $user2->createToken('test-token-2')->plainTextToken;

        $headers1 = [
            'Authorization' => 'Bearer ' . $token1,
            'Accept' => 'application/json'
        ];

        $headers2 = [
            'Authorization' => 'Bearer ' . $token2,
            'Accept' => 'application/json'
        ];

        // User 1 makes requests
        for ($i = 0; $i < 3; $i++) {
            $response = $this->getJson('/api/v1/user/drugs', $headers1);
            $response->assertStatus(200);
        }

        // User 2 should still be able to make requests
        $response = $this->getJson('/api/v1/user/drugs', $headers2);
        $response->assertStatus(200);
    }

    /**
     * Test that expired token is rejected
     */
    public function test_expired_token_is_rejected()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['*'], now()->addMinutes(-1))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        $response = $this->getJson('/api/v1/user/drugs', $headers);
        $response->assertStatus(401);
    }

    /**
     * Test that revoked token is rejected
     */
    public function test_revoked_token_is_rejected()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Revoke the token
        $user->tokens()->delete();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        $response = $this->getJson('/api/v1/user/drugs', $headers);
        $response->assertStatus(401);
    }
}
