## Laravmo

Modular Laravel 12 monolith that demonstrates how Users, Orders, Payments and Inventory modules can collaborate via a shared contract layer and a Kafka-backed event bus.

### Stack Highlights

- Laravel 12 routing API & application skeleton
- nWidart/laravel-modules for module boundaries (`Modules/*`)
- Shared contracts/DTOs/events under `shared/`
- Custom Event Bus with local + Kafka adapters (`app/Infrastructure/EventBus`)
- Kafka consumer command (`eventbus:kafka-consume`) for streaming integration

### Project Layout

```
app/
 └── Infrastructure/EventBus (LocalAdapter, KafkaAdapter, KafkaEventBus, Command)
Modules/
 ├── Users        (controller, service, listener, model)
 ├── Orders       (controller, service, listener, model)
 ├── Payments     (controller, service, listener, model)
 └── Inventory    (controller, service, model)
shared/
 ├── Contracts/V1 (versioned service interfaces)
 ├── DTOs/V1      (versioned DTOs: UserDTO, OrderDTO, PaymentDTO)
 └── Events       (UserCreated, OrderCreated, PaymentCompleted)

routes/modules.php  # loads every module's API routes under /api/*
```

### Getting Started

```bash
cp .env.example .env

docker compose up -d --build
# app: http://localhost:8080
# kafdrop UI: http://localhost:9000
```

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### Event Bus Flow

1. `Shared\Events\UserCreated` is emitted inside `Modules\Users\Services\UserService`.
2. `Modules\Users\Listeners\PublishUserCreatedListener` pushes the payload through `KafkaEventBus`, which forwards to both the local adapter (Laravel events/logging) and the Kafka adapter.
3. Orders and Payments modules react to strongly typed events via Laravel listeners; external systems can subscribe directly from Kafka using `php artisan eventbus:kafka-consume`.

### Architecture Features

#### Database Boundary Separation
Each module can use its own database connection. Configure via `.env`:
```env
USERS_DB_DATABASE=laravmo_users
ORDERS_DB_DATABASE=laravmo_orders
PAYMENTS_DB_DATABASE=laravmo_payments
INVENTORY_DB_DATABASE=laravmo_inventory
```

#### API Gateway
Centralized routing via `/api/gateway/{module}/{path}`:
- `GET /api/gateway/health` - Gateway health check
- `GET /api/gateway/users` - Route to Users module
- `POST /api/gateway/orders` - Route to Orders module

#### Cross-Module Communication
Service Locator pattern abstracts module communication:
- **Local mode** (monolith): `SERVICE_LOCATOR_MODE=local` - resolves from same container
- **HTTP mode** (microservices): `SERVICE_LOCATOR_MODE=http` - resolves via HTTP calls
- **Hybrid mode** (partial extraction): `SERVICE_LOCATOR_MODE=hybrid` - per-service configuration

```env
# Example: Only Payments extracted as microservice
SERVICE_LOCATOR_MODE=hybrid
SERVICE_PAYMENTS_MODE=http
SERVICE_PAYMENTS_URL=http://payments-service:8000
# Users, Orders, Inventory remain local (default)
```

**Documentation:**
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) - Architecture overview and migration strategy
- [`docs/MODULE_GUIDE.md`](docs/MODULE_GUIDE.md) - Module development guide with code examples and best practices
