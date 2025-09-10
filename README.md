Drug Search and Tracker API
This is a Laravel-based API service designed for drug information search and user-specific medication tracking. It integrates with the National Library of Medicine's (NLM) RxNorm APIs to provide accurate drug data. The application features secure user authentication, a public search endpoint, and private endpoints for users to manage their personal medication lists.

✨ Features
Secure User Authentication: Register and Login endpoints using Laravel Sanctum for token-based authentication.

Public Drug Search: An unauthenticated endpoint to search for drugs using the RxNorm API.

Private Medication Tracking: Authenticated users can add, delete, and view drugs on their personal medication list.

External API Integration: A dedicated service class for clean and reusable communication with the RxNorm APIs.

Rate Limiting: The public search endpoint is rate-limited to prevent abuse and excessive requests.

API Caching: Responses from the external RxNorm API are cached to improve performance and reduce redundant calls.

Validation & Error Handling: Robust validation for incoming requests and proper error handling.

💻 Technical Stack
Framework: Laravel 12+

Database: MySQL

Authentication: Laravel Sanctum

HTTP Client: Laravel's built-in HTTP Client

Testing: Pest / PHPUnit

🚀 Setup and Installation
Follow these steps to get the project running on your local machine.

1. Prerequisites
PHP >= 8.1

Composer

MySQL Server

A code editor (like Visual Studio Code)

2. Clone the Repository
git clone <your-repository-url>
cd drug-tracker-api

3. Install Dependencies
Install the required PHP packages using Composer.

composer install

4. Environment Configuration
Create a copy of the .env.example file and name it .env.

cp .env.example .env

Open the .env file and configure your MySQL database connection:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=drug_tracker
DB_USERNAME=root
DB_PASSWORD=your_password

Note: Ensure you have created a database named drug_tracker (or your chosen name) in MySQL.

5. Generate Application Key
php artisan key:generate

6. Run Database Migrations
This command will create all the necessary tables (users, medications, etc.) in your database.

php artisan migrate

7. Start the Development Server
php artisan serve

The API will now be running at http://127.0.0.1:8000.

↔️ API Endpoints
The base URL for all endpoints is http://127.0.0.1:8000/api.

User Authentication
1. Register User
Method: POST

Endpoint: /register

Description: Allows a new user to register.

Payload:

{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123"
}

Success Response (201):

{
    "token": "1|AbcdeFgHiJkLMnOp..."
}

2. Login User
Method: POST

Endpoint: /login

Description: Allows a user to log in and receive an API token.

Payload:

{
    "email": "john.doe@example.com",
    "password": "password123"
}

Success Response (200):

{
    "token": "2|AbcdeFgHiJkLMnOp..."
}

Public Search Endpoint
1. Search for Drugs
Method: GET

Endpoint: /search

Description: Searches for drugs by name. This endpoint is public and rate-limited (10 requests/minute).

Parameters: drug_name (string, required)

Example Request: GET /api/search?drug_name=morphine sulfate

Success Response (200):

{
    "message": "Search results",
    "data": [
        {
            "rxcui": "1731999",
            "name": "20 ML morphine sulfate 10 MG/ML Injection [Infumorph]",
            "baseNames": [
                "morphine"
            ],
            "doseForms": [
                "Injectable Product"
            ]
        },
        {
            "rxcui": "1871440",
            "name": "Abuse-Deterrent 12 HR morphine sulfate 15 MG Extended Release Oral Tablet [Arymo]",
            "baseNames": [
                "morphine"
            ],
            "doseForms": [
                "Oral Product",
                "Pill"
            ]
        },
        {
            "rxcui": "1871443",
            "name": "Abuse-Deterrent 12 HR morphine sulfate 30 MG Extended Release Oral Tablet [Arymo]",
            "baseNames": [
                "morphine"
            ],
            "doseForms": [
                "Oral Product",
                "Pill"
            ]
        },
        {
            "rxcui": "1871446",
            "name": "Abuse-Deterrent 12 HR morphine sulfate 60 MG Extended Release Oral Tablet [Arymo]",
            "baseNames": [
                "morphine"
            ],
            "doseForms": [
                "Oral Product",
                "Pill"
            ]
        },
        {
            "rxcui": "2055307",
            "name": "20 ML morphine sulfate 10 MG/ML Injection [Mitigo]",
            "baseNames": [
                "morphine"
            ],
            "doseForms": [
                "Injectable Product"
            ]
        }
    ]
}

Private User Medication Endpoints
Authentication Required: All endpoints below require a valid Bearer Token in the Authorization header. Authorization: Bearer <token>

1. Get User's Drug List
Method: GET

Endpoint: /user/drugs

Description: Retrieves all drugs from the authenticated user's medication list.

Success Response (200):

{
    "message": "User medications",
    "data": {
        "current_page": 1,
        "data": [
            {
                "rxcui": "1731999",
                "name": "20 ML morphine sulfate 10 MG/ML Injection [Infumorph]",
                "baseNames": [
                    "morphine"
                ],
                "doseForms": [
                    "Injectable Product"
                ]
            }
        ],
        "first_page_url": "http://127.0.0.1:8000/api/v1/user/drugs?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://127.0.0.1:8000/api/v1/user/drugs?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "page": null,
                "active": false
            },
            {
                "url": "http://127.0.0.1:8000/api/v1/user/drugs?page=1",
                "label": "1",
                "page": 1,
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "page": null,
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://127.0.0.1:8000/api/v1/user/drugs",
        "per_page": 10,
        "prev_page_url": null,
        "to": 1,
        "total": 1
    }
}
2. Add Drug to List
Method: POST

Endpoint: /user/drugs

Description: Adds a new drug to the user's medication list.

Payload:

{
    "rxcui": "1731999"
}

Success Response (201):

{
    "message": "Drug added to your list",
    "data": {
        "rxcui": "1731999",
        "created_at": "2025-09-10T17:50:09.000000Z"
    }
}

3. Delete Drug from List
Method: DELETE

Endpoint: /user/drugs/{rxcui}

Description: Deletes a specific drug from the user's list.

Example Request: DELETE /api/user/drugs/1731999

Success Response: 204 No Content

🧪 Testing
To run the full suite of unit and feature tests, use the following artisan command:

php artisan test
