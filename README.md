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


DB_CONNECTION=pgsql
DB_HOST=aws-0-eu-west-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.fjgxpntavmjkkrandbde
DB_PASSWORD="AGr1600473_b##"

2. Docker SetupBuild and start the application environment:Bashdocker-compose up -d --build
3. Database MigrationRun the migrations within the Docker container:Bashdocker-compose exec app php artisan migrate
 API DocumentationBase URL: http://localhost:8000/apiRequired 
 Header: Accept: application/json
 AuthenticationEndpointMethodDescription/registerPOSTCreate 'customer' or 'vendor' account./loginPOSTReturns a Bearer Token for Sanctum.ProductsEndpointMethodAuthRoleDescription/productsGETNoAnyList all products./createProductsPOSTYesVendorAdd a new product listing./products/{id}PUTYesVendorUpdate stock or details.OrdersEndpointMethodAuthDescription/ordersPOSTYesPurchase a product. Handles stock deduction.🏗️ Design Decisions & Assumptions1. Concurrency Control (Race Condition Prevention)To satisfy the requirement of preventing overselling, the OrderService implements Pessimistic Locking (lockForUpdate()).The Logic: When an order is placed, the database locks the specific product row until the transaction is complete. This ensures that even if 100 users hit the 'Buy' button at once, the stock is decremented accurately and never goes below zero.2. Standardized API ResponsesA global exception handler was implemented in bootstrap/app.php.The Result: The API provides a consistent JSON structure for both successes and failures (e.g., 401 Unauthorized returns a clean {"status": "error", "message": "Unauthenticated."} instead of a generic HTML error).3. Role-Based Access Control (RBAC)Instead of putting logic in the controller, I used custom Middleware (EnsureUserIsVendor).Why: This ensures that authorization is handled at the routing layer, keeping the controllers lean and focused only on business logic.4. DockerizationThe app is containerized using a php:8.2-fpm base image. This ensures that the environment (PHP extensions for Postgres, Composer, etc.) is identical for every developer, eliminating "it works on my machine" issues.5. Database ChoicePostgreSQL (Supabase): Chosen for its superior handling of complex transactions and reliable locking mechanisms compared to standard MySQL.🛠️ Tech StackFramework: Laravel 11Database: PostgreSQL (via Supabase)Auth: Laravel Sanctum (Bearer Tokens)Environment: Docker & Docker Compose
---

### **Final GitHub Push**
Since you were getting that "rejected" error earlier, run this final command to push this README and your code:

```powershell
git add .
git commit -m "docs: finalized README and setup instructions"
git push origin main --force