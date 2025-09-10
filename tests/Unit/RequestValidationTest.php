<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\MedicationSearchRequest;
use App\Http\Requests\MedicationAddRequest;
use App\Http\Requests\MedicationListRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test LoginRequest validation rules
     */
    public function test_login_request_validation_rules()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertStringContainsString('required', $rules['email']);
        $this->assertStringContainsString('string', $rules['email']);
        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('required', $rules['password']);
        $this->assertStringContainsString('string', $rules['password']);
    }

    /**
     * Test LoginRequest with valid data
     */
    public function test_login_request_with_valid_data()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * Test LoginRequest with invalid data
     */
    public function test_login_request_with_invalid_data()
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $invalidData = [
            'email' => 'invalid-email',
            'password' => ''
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * Test RegistrationRequest validation rules
     */
    public function test_registration_request_validation_rules()
    {
        $request = new RegistrationRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('string', $rules['name']);
        $this->assertStringContainsString('max:255', $rules['name']);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('unique:users,email', $rules['email']);
        $this->assertContains('required', $rules['password']);
    }

    /**
     * Test RegistrationRequest with valid data
     */
    public function test_registration_request_with_valid_data()
    {
        $request = new RegistrationRequest();
        $rules = $request->rules();

        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * Test RegistrationRequest with weak password
     */
    public function test_registration_request_with_weak_password()
    {
        $request = new RegistrationRequest();
        $rules = $request->rules();

        $invalidData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak'
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * Test RegistrationRequest with invalid email
     */
    public function test_registration_request_with_invalid_email()
    {
        $request = new RegistrationRequest();
        $rules = $request->rules();

        $invalidData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123'
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Test MedicationSearchRequest validation rules
     */
    public function test_medication_search_request_validation_rules()
    {
        $request = new MedicationSearchRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('drug_name', $rules);
        $this->assertStringContainsString('required', $rules['drug_name']);
        $this->assertStringContainsString('string', $rules['drug_name']);
        $this->assertStringContainsString('min:3', $rules['drug_name']);
    }

    /**
     * Test MedicationSearchRequest with valid data
     */
    public function test_medication_search_request_with_valid_data()
    {
        $request = new MedicationSearchRequest();
        $rules = $request->rules();

        $validData = [
            'drug_name' => 'aspirin'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * Test MedicationSearchRequest with short drug name
     */
    public function test_medication_search_request_with_short_drug_name()
    {
        $request = new MedicationSearchRequest();
        $rules = $request->rules();

        $invalidData = [
            'drug_name' => 'ab'
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('drug_name', $validator->errors()->toArray());
    }

    /**
     * Test MedicationAddRequest validation rules
     */
    public function test_medication_add_request_validation_rules()
    {
        $request = new MedicationAddRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('rxcui', $rules);
        $this->assertStringContainsString('required', $rules['rxcui']);
        $this->assertStringContainsString('string', $rules['rxcui']);
    }

    /**
     * Test MedicationAddRequest with valid data
     */
    public function test_medication_add_request_with_valid_data()
    {
        $request = new MedicationAddRequest();
        $rules = $request->rules();

        $validData = [
            'rxcui' => '12345'
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * Test MedicationAddRequest with missing rxcui
     */
    public function test_medication_add_request_with_missing_rxcui()
    {
        $request = new MedicationAddRequest();
        $rules = $request->rules();

        $invalidData = [];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rxcui', $validator->errors()->toArray());
    }

    /**
     * Test MedicationListRequest validation rules
     */
    public function test_medication_list_request_validation_rules()
    {
        $request = new MedicationListRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('per_page', $rules);
        $this->assertStringContainsString('nullable', $rules['per_page']);
        $this->assertStringContainsString('integer', $rules['per_page']);
        $this->assertStringContainsString('max:500', $rules['per_page']);
    }

    /**
     * Test MedicationListRequest with valid data
     */
    public function test_medication_list_request_with_valid_data()
    {
        $request = new MedicationListRequest();
        $rules = $request->rules();

        $validData = [
            'per_page' => 10
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }

    /**
     * Test MedicationListRequest with invalid per_page
     */
    public function test_medication_list_request_with_invalid_per_page()
    {
        $request = new MedicationListRequest();
        $rules = $request->rules();

        $invalidData = [
            'per_page' => 'invalid'
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }

    /**
     * Test MedicationListRequest with per_page exceeding maximum
     */
    public function test_medication_list_request_with_per_page_exceeding_maximum()
    {
        $request = new MedicationListRequest();
        $rules = $request->rules();

        $invalidData = [
            'per_page' => 1000
        ];

        $validator = Validator::make($invalidData, $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }

    /**
     * Test MedicationListRequest with null per_page (should pass)
     */
    public function test_medication_list_request_with_null_per_page()
    {
        $request = new MedicationListRequest();
        $rules = $request->rules();

        $validData = [
            'per_page' => null
        ];

        $validator = Validator::make($validData, $rules);
        $this->assertTrue($validator->passes());
    }
}
