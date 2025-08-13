
# Wallet API


## Note

The commit history is not available because this was a private challenge that was only made public after its conclusion.

## Description

This project is a simple financial wallet API where users can register, login, deposit money, transfer balance to other users, and reverse transactions if needed.  
It includes authentication, validation of balance, transaction reversal, and rate limiting to protect the API.

---

## Requirements

- Docker & Docker Compose  
- PHP 8.1+ (inside Docker)  
- Composer (inside Docker)  

---

## Setup

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd <your-repo-folder>
```

### 2. Configure environment variables

Copy `.env.example` to `.env` and set your database credentials:

```bash
cp .env.example .env
```

Make sure `.env` has:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=wallet
DB_USERNAME=root
DB_PASSWORD=root
```

### 3. Start Docker containers

```bash
docker-compose up -d
```

### 4. Install dependencies

Enter the PHP container and run composer install:

```bash
docker exec -it wallet-app bash
composer install
php artisan key:generate
```

### 5. Run migrations

```bash
php artisan migrate
```

---

## API Endpoints

All endpoints are prefixed with `/api` and protected by Sanctum authentication unless otherwise noted.

### Authentication

- `POST /api/register`  
  Register a new user. Required fields: `name`, `email`, `password`, `password_confirmation`.

- `POST /api/login`  
  Login with email and password. Returns a Sanctum token.

- `POST /api/logout`  
  Logout the authenticated user. Requires authentication token.

### Wallet Operations (require authentication)

#### POST `/api/wallet`  
Current wallet balance and user email 

#### POST `/api/deposit`  
Deposit money into your wallet.  
**Body parameters:**  
- `amount` (decimal) — The amount to deposit.

#### POST `/api/transfer`  
Transfer money to another user.  
**Body parameters:**  
- `receiver_id` (integer) — The ID of the user receiving the transfer.  
- `amount` (decimal) — The amount to transfer.

#### POST `/api/reverse`  
Reverse a previous transaction.  
**Body parameters:**  
- `reference` (UUID string) — The unique reference of the transaction to reverse.

#### GET `/api/transactions`  
Get a paginated list of your transactions (both sent and received).

---

## Rate Limiting

- API requests to wallet operations are limited to 30 requests per minute per user to prevent abuse.  
- When the limit is reached, the API returns HTTP 429 with a message:  
  ```json
  {
    "message": "Too many requests. Please slow down."
  }
  ```

---

## Notes

- Transactions are atomic and validated to prevent overdrafts.  
- Reversals can only be done by users involved in the original transaction.  
- The system supports deposit, transfer, and reversal operations with proper validation.

---

## Testing

### Steps to Configure Testing Database

1. **Create a `.env.testing` file**

Create a file named `.env.testing` in the project root with the following content (adjust credentials as needed):

```env
APP_NAME=WalletTest
APP_ENV=testing
APP_KEY=base64:YourAppKeyHere
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=wallet_test
DB_USERNAME=root
DB_PASSWORD=root

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
SESSION_LIFETIME=120
```

2. **Create the testing database**

Create the database manually in your MySQL instance:

```sql
CREATE DATABASE wallet_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Run migrations on the testing database**

Run migrations specifying the testing environment:

```bash
php artisan migrate --env=testing
```

4. **Run tests**

Simply run:

```bash
php artisan test
```

Laravel will automatically use the `.env.testing` configuration for your tests, keeping your test data isolated.

---

---

## Example cURL Requests

### Register

```bash
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Diogo Santos","email":"diogo.santos@example.com","password":"password","password_confirmation":"password"}'
```

### Login

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"diogo.santos@example.com","password":"password"}'
```

### Deposit

```bash
curl -X POST http://localhost/api/deposit \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"amount":100.00}'
```

