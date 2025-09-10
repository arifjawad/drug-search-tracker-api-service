# Drug Search and Tracker API

A Laravel-based API service designed for drug information search and user-specific medication tracking. It integrates with the National Library of Medicine's (NLM) RxNorm APIs to provide accurate drug data. The application features secure user authentication, a public search endpoint, and private endpoints for users to manage their personal medication lists.

## ✨ Features

- **Secure User Authentication**: Register and Login endpoints using Laravel Sanctum for token-based authentication
- **Public Drug Search**: An unauthenticated endpoint to search for drugs using the RxNorm API
- **Private Medication Tracking**: Authenticated users can add, delete, and view drugs on their personal medication list
- **External API Integration**: A dedicated service class for clean and reusable communication with the RxNorm APIs
- **Rate Limiting**: The public search endpoint is rate-limited to prevent abuse and excessive requests
- **API Caching**: Responses from the external RxNorm API are cached to improve performance and reduce redundant calls
- **Validation & Error Handling**: Robust validation for incoming requests and proper error handling

## 💻 Technical Stack

- **Framework**: Laravel 12+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **HTTP Client**: Laravel's built-in HTTP Client
- **Testing**: Pest / PHPUnit


## 🚀 Setup and Installation

Follow these steps to get the project running on your local machine.

### Prerequisites

- PHP >= 8.1
- Composer
- MySQL Server
- A code editor (like Visual Studio Code)

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone <your-repository-url>
   cd drug-tracker-api
   ```

2. **Install Dependencies**
   Install the required PHP packages using Composer:
   ```bash
   composer install
   ```

3. **Environment Configuration**
   Create a copy of the `.env.example` file and name it `.env`:
   ```bash
   cp .env.example .env
   ```

4. **Database Configuration**
   Open the `.env` file and configure your MySQL database connection:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=drug_tracker
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
   
   > **Note**: Ensure you have created a database named `drug_tracker` (or your chosen name) in MySQL.

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Run Database Migrations**
   This command will create all the necessary tables (users, medications, etc.) in your database:
   ```bash
   php artisan migrate
   ```

7. **Start the Development Server**
   ```bash
   php artisan serve
   ```
   
   The API will now be running at `http://127.0.0.1:8000`

## 📡 API Endpoints

The base URL for all endpoints is `http://127.0.0.1:8000/api/v1`

### User Authentication

#### 1. Register User

**Method:** `POST`  
**Endpoint:** `/auth/register`  
**Description:** Allows a new user to register.

**Payload:**
```json
{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123"
}
```

**Success Response (201):**
```json
{
    "token": "1|AbcdeFgHiJkLMnOp..."
}
```

#### 2. Login User

**Method:** `POST`  
**Endpoint:** `/auth/login`  
**Description:** Allows a user to log in and receive an API token.

**Payload:**
```json
{
    "email": "john.doe@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "token": "2|AbcdeFgHiJkLMnOp..."
}
```

### Public Search Endpoint

#### 1. Search for Drugs

**Method:** `GET`  
**Endpoint:** `/drugs/search`  
**Description:** Searches for drugs by name. This endpoint is public and rate-limited (10 requests/minute).

**Parameters:**
- `drug_name` (string, required) - The name of the drug to search for

**Example Request:**
```
GET /api/v1/drugs/search?drug_name=morphine sulfate
```

**Success Response (200):**
```json
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
```

### Private User Medication Endpoints

> **Authentication Required**: All endpoints below require a valid Bearer Token in the Authorization header.  
> **Header**: `Authorization: Bearer <token>`

#### 1. Get User's Drug List

**Method:** `GET`  
**Endpoint:** `/user/drugs`  
**Description:** Retrieves all drugs from the authenticated user's medication list.

**Success Response (200):**
```json
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
```

#### 2. Add Drug to List

**Method:** `POST`  
**Endpoint:** `/user/drugs`  
**Description:** Adds a new drug to the user's medication list.

**Payload:**
```json
{
    "rxcui": "1731999"
}
```

**Success Response (201):**
```json
{
    "message": "Drug added to your list",
    "data": {
        "rxcui": "1731999",
        "created_at": "2025-09-10T17:50:09.000000Z"
    }
}
```

#### 3. Delete Drug from List

**Method:** `DELETE`  
**Endpoint:** `/user/drugs/{rxcui}`  
**Description:** Deletes a specific drug from the user's list.

**Example Request:**
```
DELETE /api/v1/user/drugs/1731999
```

**Success Response:** `204 No Content`

## 🧪 Testing

To run the full suite of unit and feature tests, use the following artisan command:

```bash
php artisan test
```

## 📋 Error Handling

The API includes comprehensive error handling with appropriate HTTP status codes:

- **400 Bad Request**: Invalid request data or missing required parameters
- **401 Unauthorized**: Missing or invalid authentication token
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **429 Too Many Requests**: Rate limit exceeded
- **500 Internal Server Error**: Server-side errors

## 🚦 Rate Limiting

- **Public Search Endpoint**: 10 requests per minute per IP address
- **Authenticated Endpoints**: 60 requests per minute per user

## 🔧 Configuration

### Environment Variables

Key environment variables that can be configured in your `.env` file:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=drug_tracker
DB_USERNAME=root
DB_PASSWORD=your_password

# RxNorm API
RXNORM_API_BASE_URL=https://rxnav.nlm.nih.gov/REST

# Rate Limiting
RATE_LIMIT_SEARCH=10
RATE_LIMIT_API=60
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

If you have any questions or need help, please open an issue in the repository or contact the development team.
