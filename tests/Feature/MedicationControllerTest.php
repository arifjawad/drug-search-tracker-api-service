<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserMedication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test drug search with valid drug name
     */
    public function test_drug_search_with_valid_name()
    {
        $searchData = [
            'drug_name' => 'aspirin'
        ];

        $response = $this->getJson('/api/v1/drugs/search?' . http_build_query($searchData));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Search results'
                ]);
    }

    /**
     * Test drug search with short drug name
     */
    public function test_drug_search_fails_with_short_name()
    {
        $searchData = [
            'drug_name' => 'ab'
        ];

        $response = $this->getJson('/api/v1/drugs/search?' . http_build_query($searchData));

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test drug search without drug name
     */
    public function test_drug_search_fails_without_drug_name()
    {
        $response = $this->getJson('/api/v1/drugs/search');

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test drug search with empty drug name
     */
    public function test_drug_search_fails_with_empty_drug_name()
    {
        $searchData = [
            'drug_name' => ''
        ];

        $response = $this->getJson('/api/v1/drugs/search?' . http_build_query($searchData));

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test getting user medications when authenticated
     */
    public function test_get_user_medications_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create some user medications
        UserMedication::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/user/drugs');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User medications'
                ]);
    }

    /**
     * Test getting user medications with pagination
     */
    public function test_get_user_medications_with_pagination()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create some user medications
        UserMedication::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/user/drugs?per_page=5');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User medications',
                    'data' => [
                        'per_page' => 5
                    ]
                ]);
    }

    /**
     * Test getting user medications when not authenticated
     */
    public function test_get_user_medications_fails_when_not_authenticated()
    {
        $response = $this->getJson('/api/v1/user/drugs');

        $response->assertStatus(401);
    }

    /**
     * Test adding medication when authenticated
     */
    public function test_add_medication_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $medicationData = [
            'rxcui' => '12345'
        ];

        $response = $this->postJson('/api/v1/user/drugs', $medicationData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Drug added to your list'
                ]);

        $this->assertDatabaseHas('user_medications', [
            'user_id' => $user->id,
            'rxcui' => $medicationData['rxcui']
        ]);
    }

    /**
     * Test adding medication without rxcui
     */
    public function test_add_medication_fails_without_rxcui()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/drugs', []);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /**
     * Test adding medication when not authenticated
     */
    public function test_add_medication_fails_when_not_authenticated()
    {
        $medicationData = [
            'rxcui' => '12345'
        ];

        $response = $this->postJson('/api/v1/user/drugs', $medicationData);

        $response->assertStatus(401);
    }

    /**
     * Test adding duplicate medication
     */
    public function test_add_duplicate_medication_fails()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create existing medication
        $existingMedication = UserMedication::factory()->create([
            'user_id' => $user->id,
            'rxcui' => '12345'
        ]);

        $medicationData = [
            'rxcui' => '12345'
        ];

        $response = $this->postJson('/api/v1/user/drugs', $medicationData);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    /**
     * Test deleting medication when authenticated
     */
    public function test_delete_medication_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $medication = UserMedication::factory()->create([
            'user_id' => $user->id,
            'rxcui' => '12345'
        ]);

        $response = $this->deleteJson("/api/v1/user/drugs/{$medication->rxcui}");

        $response->assertStatus(204)
                ->assertJson([
                    'success' => true,
                    'message' => 'Drug deleted from your list'
                ]);

        $this->assertDatabaseMissing('user_medications', [
            'user_id' => $user->id,
            'rxcui' => $medication->rxcui
        ]);
    }

    /**
     * Test deleting non-existent medication
     */
    public function test_delete_nonexistent_medication_fails()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/user/drugs/99999');

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    /**
     * Test deleting medication when not authenticated
     */
    public function test_delete_medication_fails_when_not_authenticated()
    {
        $response = $this->deleteJson('/api/v1/user/drugs/12345');

        $response->assertStatus(401);
    }

    /**
     * Test deleting another user's medication
     */
    public function test_delete_another_users_medication_fails()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        $medication = UserMedication::factory()->create([
            'user_id' => $user2->id,
            'rxcui' => '12345'
        ]);

        $response = $this->deleteJson("/api/v1/user/drugs/{$medication->rxcui}");

        $response->assertStatus(404)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        // Verify medication still exists
        $this->assertDatabaseHas('user_medications', [
            'user_id' => $user2->id,
            'rxcui' => $medication->rxcui
        ]);
    }

    /**
     * Test pagination validation
     */
    public function test_pagination_validation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Test with invalid per_page value
        $response = $this->getJson('/api/v1/user/drugs?per_page=invalid');

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test with per_page exceeding maximum
        $response = $this->getJson('/api/v1/user/drugs?per_page=1000');

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }
}
