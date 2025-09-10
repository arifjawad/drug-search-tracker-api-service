<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserMedication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test complete user workflow: register -> login -> search drugs -> add medication -> list medications -> delete medication
     */
    public function test_complete_user_workflow()
    {
        // Step 1: Register a new user
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ];

        $registerResponse = $this->postJson('/api/v1/auth/register', $userData);
        $registerResponse->assertStatus(201)
                        ->assertJsonStructure([
                            'success',
                            'message',
                            'data' => ['token']
                        ]);

        $token = $registerResponse->json('data.token');

        // Step 2: Login with the same credentials
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $loginResponse = $this->postJson('/api/v1/auth/login', $loginData);
        $loginResponse->assertStatus(200)
                     ->assertJsonStructure([
                         'success',
                         'message',
                         'data' => ['token']
                     ]);

        // Step 3: Search for drugs using the token
        $searchResponse = $this->getJson('/api/v1/drugs/search?drug_name=aspirin');
        $searchResponse->assertStatus(200)
                      ->assertJsonStructure([
                          'success',
                          'message',
                          'data'
                      ]);

        // Step 4: Add a medication to user's list
        $medicationData = [
            'rxcui' => '12345'
        ];

        $addResponse = $this->postJson('/api/v1/user/drugs', $medicationData, [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ]);
        $addResponse->assertStatus(201)
                   ->assertJsonStructure([
                       'success',
                       'message',
                       'data'
                   ]);

        // Step 5: List user's medications
        $listResponse = $this->getJson('/api/v1/user/drugs', [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ]);
        $listResponse->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                        'message',
                        'data' => [
                            'data',
                            'current_page',
                            'per_page',
                            'total'
                        ]
                    ]);

        // Verify medication was added
        $this->assertDatabaseHas('user_medications', [
            'user_id' => User::where('email', $userData['email'])->first()->id,
            'rxcui' => $medicationData['rxcui']
        ]);

        // Step 6: Delete the medication
        $deleteResponse = $this->deleteJson("/api/v1/user/drugs/{$medicationData['rxcui']}", [], [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ]);
        $deleteResponse->assertStatus(204)
                      ->assertJson([
                          'success' => true,
                          'message' => 'Drug deleted from your list'
                      ]);

        // Verify medication was deleted
        $this->assertDatabaseMissing('user_medications', [
            'user_id' => User::where('email', $userData['email'])->first()->id,
            'rxcui' => $medicationData['rxcui']
        ]);
    }

    /**
     * Test multiple users can have separate medication lists
     */
    public function test_multiple_users_separate_medication_lists()
    {
        // Create two users
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

        // User 1 adds medication
        $medication1 = ['rxcui' => '11111'];
        $response1 = $this->postJson('/api/v1/user/drugs', $medication1, $headers1);
        $response1->assertStatus(201);

        // User 2 adds different medication
        $medication2 = ['rxcui' => '22222'];
        $response2 = $this->postJson('/api/v1/user/drugs', $medication2, $headers2);
        $response2->assertStatus(201);

        // User 1 lists medications - should only see their own
        $listResponse1 = $this->getJson('/api/v1/user/drugs', $headers1);
        $listResponse1->assertStatus(200);
        $user1Medications = $listResponse1->json('data.data');
        $this->assertCount(1, $user1Medications);
        $this->assertEquals('11111', $user1Medications[0]['rxcui']);

        // User 2 lists medications - should only see their own
        $listResponse2 = $this->getJson('/api/v1/user/drugs', $headers2);
        $listResponse2->assertStatus(200);
        $user2Medications = $listResponse2->json('data.data');
        $this->assertCount(1, $user2Medications);
        $this->assertEquals('22222', $user2Medications[0]['rxcui']);
    }

    /**
     * Test error handling across different scenarios
     */
    public function test_error_handling_scenarios()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        // Test adding duplicate medication
        $medication = ['rxcui' => '12345'];

        // Add first time
        $response1 = $this->postJson('/api/v1/user/drugs', $medication, $headers);
        $response1->assertStatus(201);

        // Try to add same medication again
        $response2 = $this->postJson('/api/v1/user/drugs', $medication, $headers);
        $response2->assertStatus(422);

        // Test deleting non-existent medication
        $deleteResponse = $this->deleteJson('/api/v1/user/drugs/99999', [], $headers);
        $deleteResponse->assertStatus(404);

        // Test invalid search parameters
        $searchResponse = $this->getJson('/api/v1/drugs/search?drug_name=ab');
        $searchResponse->assertStatus(422);

        // Test invalid pagination
        $listResponse = $this->getJson('/api/v1/user/drugs?per_page=invalid', $headers);
        $listResponse->assertStatus(422);
    }

    /**
     * Test API response format consistency
     */
    public function test_api_response_format_consistency()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        // Test successful responses
        $searchResponse = $this->getJson('/api/v1/drugs/search?drug_name=aspirin');
        $searchResponse->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        $listResponse = $this->getJson('/api/v1/user/drugs', $headers);
        $listResponse->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        $addResponse = $this->postJson('/api/v1/user/drugs', ['rxcui' => '12345'], $headers);
        $addResponse->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);

        // Test error responses
        $errorResponse = $this->getJson('/api/v1/drugs/search?drug_name=ab');
        $errorResponse->assertJsonStructure([
            'success',
            'message',
            'data'
        ]);
    }

    /**
     * Test pagination functionality
     */
    public function test_pagination_functionality()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ];

        // Create multiple medications
        for ($i = 1; $i <= 15; $i++) {
            UserMedication::factory()->create([
                'user_id' => $user->id,
                'rxcui' => str_pad($i, 5, '0', STR_PAD_LEFT)
            ]);
        }

        // Test default pagination
        $response1 = $this->getJson('/api/v1/user/drugs', $headers);
        $response1->assertStatus(200);
        $data1 = $response1->json('data');
        $this->assertEquals(10, $data1['per_page']); // Default per_page
        $this->assertEquals(15, $data1['total']);

        // Test custom pagination
        $response2 = $this->getJson('/api/v1/user/drugs?per_page=5', $headers);
        $response2->assertStatus(200);
        $data2 = $response2->json('data');
        $this->assertEquals(5, $data2['per_page']);
        $this->assertEquals(15, $data2['total']);
        $this->assertCount(5, $data2['data']);

        // Test second page
        $response3 = $this->getJson('/api/v1/user/drugs?per_page=5&page=2', $headers);
        $response3->assertStatus(200);
        $data3 = $response3->json('data');
        $this->assertEquals(2, $data3['current_page']);
        $this->assertCount(5, $data3['data']);
    }

    /**
     * Test concurrent user operations
     */
    public function test_concurrent_user_operations()
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

        // Both users add medications simultaneously
        $medication1 = ['rxcui' => '11111'];
        $medication2 = ['rxcui' => '22222'];

        $response1 = $this->postJson('/api/v1/user/drugs', $medication1, $headers1);
        $response2 = $this->postJson('/api/v1/user/drugs', $medication2, $headers2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        // Both users list their medications
        $listResponse1 = $this->getJson('/api/v1/user/drugs', $headers1);
        $listResponse2 = $this->getJson('/api/v1/user/drugs', $headers2);

        $listResponse1->assertStatus(200);
        $listResponse2->assertStatus(200);

        // Verify each user only sees their own medications
        $user1Medications = $listResponse1->json('data.data');
        $user2Medications = $listResponse2->json('data.data');

        $this->assertCount(1, $user1Medications);
        $this->assertCount(1, $user2Medications);
        $this->assertEquals('11111', $user1Medications[0]['rxcui']);
        $this->assertEquals('22222', $user2Medications[0]['rxcui']);
    }
}
