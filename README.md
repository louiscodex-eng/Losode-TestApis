# Laravel E-Commerce API (Technical Assessment)

A high-performance, role-based e-commerce backend built with **Laravel 11**. This application features secure authentication, vendor-specific product management, and a concurrency-safe ordering system integrated with **Supabase (PostgreSQL)**.

## 🚀 Setup Instructions

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- A [Supabase](https://supabase.com/) account (or any PostgreSQL instance)

### 1. Clone & Environment Configuration
```bash
git clone [https://github.com/louiscodex-eng/Losode-TestApis.git](https://github.com/louiscodex-eng/Losode-TestApis.git)
cd Losode-TestApis
cp .env.example .env


2. Docker SetupBuild and start the application environment:Bashdocker-compose up -d --build
3. Database MigrationRun the migrations within the Docker container:Bashdocker-compose exec app php artisan migrate


API Documentation
Base URL: http://localhost:8000/api

Headers: * Accept: application/json (Required)

Authorization: Bearer {your_token} (Required for protected routes)

# User Authentication
Endpoint,       Method,    Body Parameters,                                    Description
/register,         POST,  "name, email, password, role",                    Create a customer or vendor account.
/login,          POST,     "email, password",                          Exchange credentials for a Bearer Token.

# Product mANAGEMENT
/products,             GET,             No,             Any,            Fetch a list of all available products.
/createProducts,       POST,             Yes,           vendor,                Create a new listing.
/products/{id},       PUT,                Yes,          vendor,           Update price or stock of a product.
/product/{id}         DELETE              YES           vendor            Delete product/products
/vendor/products	 GET	               Yes	         Vendor         Single vendor retrieving his products

# Product Ordering
/orders,                 POST,           Yes,           Any      Place an order. Requires product_id and quantity.

# Error response
{
    "status": "error",
    "message": "Unauthenticated.",

}

# Success response
{
    "status": "successs",
    "message": "Registration Successful",
    "details": "Additional info here..." 
}


 Design Decisions & Assumptions
1. Concurrency Control (Race Condition Prevention)
To satisfy the requirement of preventing over-selling during high-traffic bursts (e.g., two users buying the last item at the same time), the OrderService implements Pessimistic Locking.

The Logic: We use lockForUpdate() during the order transaction. This tells the database to lock that specific product row until the order is finished.

The Benefit: It ensures that stock levels are always accurate and never drop below zero, even with hundreds of simultaneous requests.

2. Global Exception Handling (API Consistency)
Instead of relying on Laravel's default HTML error pages, I customized the Exception Handler in bootstrap/app.php.

The Logic: I intercepted AuthenticationException and ValidationException to force a JSON response.

The Benefit: The API always returns a consistent structure: { "status": "error", "message": "..." }. This provides a better experience for frontend developers and keeps the API predictable.

3. Middleware-Based Authorization (RBAC)
I chose to use Custom Middleware (EnsureUserIsVendor) rather than checking roles inside the Controller.

The Logic: Authorization is handled at the routing layer. If a customer tries to hit a vendor-only endpoint, the request is blocked before it even hits the business logic.

The Benefit: This follows the Single Responsibility Principle. Controllers only handle requests; Middleware handles security.

4. Database Selection: PostgreSQL (Supabase)
I opted for PostgreSQL over MySQL for this project.

The Logic: Postgres handles complex transactions and row-level locking more robustly than standard MySQL configurations.

Assumption: I assumed a cloud-hosted database (Supabase) is preferred over a local one to demonstrate "Production-ready" connectivity and environment variable management.

5. Authentication via Laravel Sanctum
I used Sanctum for token-based authentication.

The Logic: It is lightweight, secure, and the industry standard for SPA and Mobile APIs in the Laravel ecosystem.

Assumption: I assumed that "Stateful" session cookies were not required since the primary goal is a stateless REST API.