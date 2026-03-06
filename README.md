# Tocaan Task

Laravel API for orders and payments. Client and Admin both use JWT (Bearer token). New payment methods: add a gateway class and register it in config.

**Stack:** PHP 8.1+, Laravel 10, MySQL.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Set DB in `.env`, then:

```bash
php artisan migrate
php artisan db:seed
```

Seeding creates roles (`admin`, `client`) and admin user **admin@gmail.com** / **123456**.

```bash
php artisan serve
```

Base URL: `http://localhost:8000/api`

- **Client API:** `/api/v1/client/*` (JWT) — auth, orders, payments, checkout
- **Admin API:** `/api/v1/admin/*` (JWT, admin role) — orders, products, users
- **Callback:** `POST /api/payment/callback` for gateway webhooks

## Client API (`/api/v1/client`)

Auth header: `Authorization: Bearer <token>`.

| Method | Endpoint | Notes |
|--------|----------|--------|
| POST | `auth/register` | Returns JWT |
| POST | `auth/login` | Returns JWT |
| GET | `auth/me` | Current user |
| POST | `auth/logout` | |
| GET | `orders` | Paginated; query `status` (pending, confirmed, cancelled), `per_page` (max 100) |
| POST | `orders` | Create order; returns `order` + `pay_url` (payment link) |
| GET | `orders/{id}` | Own only |
| GET | `orders/by-number/{orderNumber}` | |
| PUT/PATCH | `orders/{id}` | Own only |
| DELETE | `orders/{id}` | If no payments: deleted; if has payments: status set to cancelled |
| POST | `checkout` | Create order + payment URL |
| POST | `payments/process` | Order must be confirmed |
| GET | `payments` | Paginated; optional `per_page` (max 100) |
| GET | `orders/{id}/payments` | |
| GET | `products` | Paginated; optional `per_page` (max 100) |
| GET | `products/{id}` | |

**Create order:** Send `items` as array of `{ "product_id": 1, "quantity": 2 }`. Product name and price are taken from the catalog; total is calculated. Optional: `customer_name`, `customer_email`, `payment_method`. Response includes `order` and `pay_url`.

Business rules: payments only for confirmed orders. Deleting an order with payments sets its status to cancelled instead of deleting.

## Admin API (`/api/v1/admin`)

Auth header: `Authorization: Bearer <token>`. User must have `admin` role.

| Method | Endpoint | Notes |
|--------|----------|--------|
| GET | `orders` | Paginated; query `status`, `user_id`, `per_page` (max 100) |
| GET | `orders/{id}` | |
| POST | `orders` | |
| PUT/PATCH | `orders/{id}` | |
| DELETE | `orders/{id}` | If no payments: deleted; if has payments: status set to cancelled |
| GET | `products` | Paginated; optional `per_page` (max 100) |
| POST | `products` | |
| GET | `products/{id}` | |
| PUT/PATCH | `products/{id}` | |
| DELETE | `products/{id}` | |
| GET | `users` | Paginated; optional `per_page` (max 100) |
| POST | `users` | |
| PUT/PATCH | `users/{id}` | |
| DELETE | `users/{id}` | |

## Payment gateway extensibility

Gateways use a strategy-style setup. Each gateway implements `App\Contracts\PaymentGatewayInterface` (methods: `process(Order $order, float $amount, array $options)`, `getMethod()`). To add one: create a class in `app/Services/Client/Payment/Gateways/`, add an entry under `gateways` in `config/payment.php` with `enabled`, `driver` (class name), and any keys. Put secrets in `.env`. Add the method value to `App\Enums\PaymentMethod` and to `ProcessPaymentRequest` rules. To disable a gateway, set `enabled` to false in config.

## Postman

Import `postman/Task.postman_collection.json`. Set `admin_url_v1` and `client_url_v1` (e.g. `http://localhost:8000/api/v1/admin`, `http://localhost:8000/api/v1/client`). Use Admin or Client Auth > Login to get the token; collection auth uses it for subsequent requests.

## Tests

```bash
php artisan test
```

Uses MySQL and `RefreshDatabase`.

## Going live

Before deploy:

- Set `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to your API URL.
- Set `CORS_ALLOWED_ORIGINS` to your frontend domain(s), or leave empty to allow all (not for production).
- Run `php artisan jwt:secret` if `JWT_SECRET` is empty.
- Run `php artisan config:cache` and `php artisan route:cache`.
- Run `composer install --no-dev --optimize-autoloader`.
- Use HTTPS. Set `LOG_LEVEL=error` (or `warning`) in production.

Main code: `app/Contracts/PaymentGatewayInterface.php`, `app/Services/` (Admin, Client, Contracts), `app/Support/Traits/` (ResponseTrait, Orderable), `config/payment.php`, `config/cors.php`, `config/jwt.php`.

## Notes and assumptions

- Create order: send `items` with `product_id` and `quantity` per line; product name and price are read from the products table and the total is computed server-side.
- Client and Admin APIs use JWT (Bearer token). Payment processing is simulated (no real gateway calls).
- Gateway webhooks: `POST /api/payment/callback`.
