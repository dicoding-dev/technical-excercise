# Technical Exercise Product Engineer - Enrollment Management System

This Laravel-based application manages enrollments, exams, and submissions. The API exposed to external clients currently consists of only one endpoint: `/api/user`. This README provides step-by-step setup instructions, API documentation for the available endpoint, guidance on running tests, including how to test the `DropOutEnrollments` command, and product analysis document as part of Dicoding Technical Exercise for Product Engineer.

## Table of Contents

- [Setup Instructions](#setup-instructions)
- [API Documentation](#api-documentation)
- [Testing Instructions](#testing-instructions)
  - [Testing the API Using Postman](#testing-the-api-using-postman)
  - [Testing the DropOutEnrollments Command](#testing-the-dropoutenrollments-command)
- [Product Analysis Document](#product-analysis-document)

## Setup Instructions

### Prerequisites

- PHP \>= 8.1
- Composer
- MySQL or MariaDB database

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/achmadardanip/technical-excercise
   cd technical-excercise
   ```

2. **Install PHP dependencies using Composer:**

   ```bash
   composer install
   ```

3. **Run Migrations and Seed The Database:**

   ```bash
   php artisan migrate --seed
   ```

4. **Run The Application**

   ```bash
   php artisan serve
   ```

   The application will be accessible at <http://localhost:8000>.

## API Documentation

Since the current API consists of a single endpoint

<img width="536" alt="api list" src="https://github.com/user-attachments/assets/848327cf-b7a9-45c6-9c63-89790030d598" />

Here is the documentation for /api/user:

### GET /api/user

* **Description:**
  Returns the details of the authenticated user. This endpoint is used in applications where user authentication is required, providing basic information about the logged-in user.

* **Authentication:**
  This endpoint requires an authenticated request (for example, using Laravel Sanctum or Passport). Ensure you include the necessary authentication headers (e.g., Bearer token).

* **Response Example**

  ```
  {
      "id": 1166041,
      "name": "Monalisa",
      "email": "monalisa@example.com",
      "email_verified_at": "2025-03-01T14:58:25.000000Z",
      "created_at": "2025-03-01T14:58:25.000000Z",
      "updated_at": "2025-03-01T14:58:25.000000Z"
  }
  ```

* **Example Request**

  ```
  curl -X GET http://localhost:8000/api/user \
       -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
  ```

## Testing Instructions

### Testing the API Using Postman

To test the `/api/user` endpoint using Postman, first create a new user using Tinker and generate an API token.

* **Create a New User with Tinker:**

  Open your terminal and start Tinker:

  ```
  php artisan tinker 
  ```

  Create a new user (this example uses a factory; adjust if needed):

  ```
  $user = App\Models\User::factory()->create([
      'name' => 'Monalisa',
      'email' => 'monalisa@example.com',
      'password' => bcrypt('secret'),
  ])
  ```

* **Generate an API Token (if using Laravel Sanctum or Passport):**

  For example, using Laravel Sanctum:

  ```
  $token = $user->createToken('Test Token')->plainTextToken;
  echo $token;
  ```

* **Run The Application**

  Make sure you start the application again after creating a new user, type the following command:

   ```bash
   php artisan serve
   ```

* **Set Up Postman:**

  * Open Postman and create a new GET request to:

    ```
    http://localhost:8000/api/user
    ```

  * In the request headers, add token you've got from previous step:

    ```
    Authorization: Bearer YOUR_GENERATED_TOKEN 
    ```

  * Click "Send" to make the request.

* **Verify the Response:**

  You should receive a JSON response like the following with the user’s details (matching the newly created user).

  ```
  {
      "id": 1166041,
      "name": "Monalisa",
      "email": "monalisa@example.com",
      "email_verified_at": "2025-03-01T14:58:25.000000Z",
      "created_at": "2025-03-01T14:58:25.000000Z",
      "updated_at": "2025-03-01T14:58:25.000000Z"
  }
  ```

### Testing the DropOutEnrollments Command

- **Run The Command Manually**

  In root directory of the app, run the following command:

  ```
  php artisan enrollments:dropout
  ```

  The command will output details such as the number of enrollments processed, excluded, and dropped out, along with performance metrics.

- **Link to Pull Request (testing result and optimization steps)**

  <https://github.com/dicoding-dev/technical-excercise/pull/1>

## Product Analysis Document

[**Click here**](https://docs.google.com/document/d/13JoCo44M9q_hEBQnW6R58gwABcYdL05v7JRO0tw0Uh4/edit?usp=sharing)
