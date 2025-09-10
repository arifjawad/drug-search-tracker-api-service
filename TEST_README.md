# API Test Suite

This directory contains comprehensive unit and feature tests for the Laravel API endpoints.

## Test Structure

### Feature Tests
- **`AuthControllerTest.php`** - Tests for authentication endpoints (login, register)
- **`MedicationControllerTest.php`** - Tests for medication CRUD operations and drug search
- **`MiddlewareTest.php`** - Tests for authentication middleware and rate limiting
- **`ApiIntegrationTest.php`** - Comprehensive integration tests for complete user workflows

### Unit Tests
- **`RequestValidationTest.php`** - Tests for request validation rules

## Test Coverage

### Authentication Endpoints
- ✅ User registration with valid data
- ✅ User registration with invalid data (weak password, invalid email, duplicate email)
- ✅ User login with valid credentials
- ✅ User login with invalid credentials
- ✅ Request validation for all auth endpoints

### Drug Search Endpoints
- ✅ Drug search with valid drug name
- ✅ Drug search with short drug name (validation error)
- ✅ Drug search without drug name (validation error)
- ✅ Rate limiting on search endpoint

### User Medication Management
- ✅ List user medications (authenticated)
- ✅ Add medication to user list (authenticated)
- ✅ Delete medication from user list (authenticated)
- ✅ Pagination support for medication lists
- ✅ Duplicate medication prevention
- ✅ User isolation (users can only see their own medications)

### Middleware & Security
- ✅ Authentication required for protected routes
- ✅ Public routes accessible without authentication
- ✅ Invalid token rejection
- ✅ Rate limiting on all endpoints
- ✅ Token expiration handling
- ✅ Token revocation handling

### Request Validation
- ✅ Login request validation rules
- ✅ Registration request validation rules
- ✅ Medication search request validation rules
- ✅ Medication add request validation rules
- ✅ Medication list request validation rules

### Integration Tests
- ✅ Complete user workflow (register → login → search → add → list → delete)
- ✅ Multiple users with separate medication lists
- ✅ Error handling across different scenarios
- ✅ API response format consistency
- ✅ Pagination functionality
- ✅ Concurrent user operations

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Files
```bash
# Authentication tests
php artisan test tests/Feature/AuthControllerTest.php

# Medication controller tests
php artisan test tests/Feature/MedicationControllerTest.php

# Middleware tests
php artisan test tests/Feature/MiddlewareTest.php

# Integration tests
php artisan test tests/Feature/ApiIntegrationTest.php

# Request validation tests
php artisan test tests/Unit/RequestValidationTest.php
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

### Run Tests with Verbose Output
```bash
php artisan test --verbose
```

### Use the Test Runner Script
```bash
./run_tests.sh
```

## Test Data

The tests use Laravel's factory and faker to generate test data:
- User factories for creating test users
- UserMedication factories for creating test medications
- Faker for generating realistic test data

## Database

Tests use the `RefreshDatabase` trait to ensure a clean database state for each test.

## Authentication

Tests use Laravel Sanctum for API authentication testing:
- Token-based authentication
- Token creation and validation
- Token expiration and revocation testing

## Rate Limiting

Tests verify that rate limiting is properly applied:
- Search endpoint rate limiting
- Authenticated endpoint rate limiting
- Separate rate limits per user

## Error Handling

Tests verify proper error handling for:
- Invalid input data
- Missing required fields
- Authentication failures
- Authorization failures
- Duplicate data
- Non-existent resources

## API Response Format

All tests verify that API responses follow the consistent format:
```json
{
    "success": true/false,
    "message": "Response message",
    "data": {} // Optional data payload
}
```

## Test Environment

Tests run in the testing environment with:
- SQLite in-memory database
- Disabled rate limiting for some tests
- Mocked external services where applicable

## Contributing

When adding new API endpoints or modifying existing ones:
1. Add corresponding tests to the appropriate test file
2. Update this README if new test categories are added
3. Ensure all tests pass before committing
4. Maintain test coverage above 90%

## Troubleshooting

### Common Issues

1. **Database connection errors**: Ensure the testing database is properly configured
2. **Authentication failures**: Check that Sanctum is properly set up
3. **Rate limiting issues**: Some tests may need to be run individually if rate limits are too restrictive
4. **Factory errors**: Ensure all model factories are properly defined

### Debug Mode

Run tests with debug output:
```bash
php artisan test --verbose --stop-on-failure
```

This will show detailed output and stop on the first failure for easier debugging.
