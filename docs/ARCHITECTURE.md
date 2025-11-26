# Architecture Documentation

## Database Boundary Separation

Each module now has its own database connection configuration. This allows modules to be easily extracted into microservices with separate databases.

### Configuration

Database connections are defined in `config/database.php`:

- `users` - Users module database
- `orders` - Orders module database  
- `payments` - Payments module database
- `inventory` - Inventory module database

### Environment Variables

By default, all modules use the same database (`DB_DATABASE`). To use separate databases:

```env
# Users Module
USERS_DB_DATABASE=laravmo_users
USERS_DB_HOST=db
USERS_DB_USERNAME=laravel
USERS_DB_PASSWORD=secret

# Orders Module
ORDERS_DB_DATABASE=laravmo_orders
ORDERS_DB_HOST=db
ORDERS_DB_USERNAME=laravel
ORDERS_DB_PASSWORD=secret

# Payments Module
PAYMENTS_DB_DATABASE=laravmo_payments
PAYMENTS_DB_HOST=db
PAYMENTS_DB_USERNAME=laravel
PAYMENTS_DB_PASSWORD=secret

# Inventory Module
INVENTORY_DB_DATABASE=laravmo_inventory
INVENTORY_DB_HOST=db
INVENTORY_DB_USERNAME=laravel
INVENTORY_DB_PASSWORD=secret
```

### Models

Each module's models specify their connection:

```php
class Order extends Model
{
    protected $connection = 'orders';
    // ...
}
```

## API Gateway

The API Gateway provides a centralized entry point for all API requests. 

### Gateway Routes

- `GET /api/gateway/health` - Gateway health check
- `ANY /api/gateway/{module}/{path?}` - Route requests to modules

### Usage

Instead of calling modules directly:
```
GET /api/users
POST /api/orders
```

You can route through the gateway:
```
GET /api/gateway/users
POST /api/gateway/orders
```

### Benefits

- Centralized request/response transformation
- Rate limiting per module
- Authentication/authorization
- Request logging

## Cross-Module Communication Abstraction

The Service Locator pattern abstracts cross-module communication, allowing seamless transition from monolith to microservices.

### Service Locator Interface

```php
use App\Infrastructure\ServiceLocator\ServiceLocator;

$inventoryService = ServiceLocator::make()->resolve(InventoryServiceInterface::class);
```

### Modes

#### Local Mode (Monolith)

```env
SERVICE_LOCATOR_MODE=local
```

Services are resolved from the same container. No network calls.

#### HTTP Mode (Full Microservices)

```env
SERVICE_LOCATOR_MODE=http
SERVICE_USERS_URL=http://users-service:8000
SERVICE_ORDERS_URL=http://orders-service:8000
SERVICE_PAYMENTS_URL=http://payments-service:8000
SERVICE_INVENTORY_URL=http://inventory-service:8000
```

All services are resolved via HTTP calls to remote services.

#### Hybrid Mode (Partial Extraction)

```env
SERVICE_LOCATOR_MODE=hybrid

# Per-service configuration
SERVICE_USERS_MODE=local
SERVICE_ORDERS_MODE=local
SERVICE_PAYMENTS_MODE=http
SERVICE_PAYMENTS_URL=http://payments-service:8000
SERVICE_INVENTORY_MODE=local
```

Each service can be configured individually:
- `SERVICE_{NAME}_MODE=local` - Resolve from same container (default)
- `SERVICE_{NAME}_MODE=http` - Resolve via HTTP calls

This is ideal for **gradual migration** where you extract one module at a time.

### Implementation Example

**Before (Direct Dependency):**
```php
class OrderService
{
    public function __construct(
        private readonly InventoryServiceInterface $inventory
    ) {}
}
```

**After (Service Locator):**
```php
class OrderService
{
    private readonly InventoryServiceInterface $inventory;

    public function __construct()
    {
        $this->inventory = ServiceLocator::make()
            ->resolve(InventoryServiceInterface::class);
    }
}
```

### Migration Path

1. **Monolith Phase**: Use `SERVICE_LOCATOR_MODE=local`
2. **Extract Module**: Move module to separate service
3. **Update Config**: Use hybrid mode for gradual migration
   ```env
   # In main monolith
   SERVICE_LOCATOR_MODE=hybrid
   SERVICE_PAYMENTS_MODE=http
   SERVICE_PAYMENTS_URL=http://payments-service:8000
   
   # In extracted Payments service
   SERVICE_LOCATOR_MODE=hybrid
   SERVICE_USERS_MODE=http
   SERVICE_USERS_URL=http://monolith:8080
   SERVICE_ORDERS_MODE=http
   SERVICE_ORDERS_URL=http://monolith:8080
   SERVICE_INVENTORY_MODE=http
   SERVICE_INVENTORY_URL=http://monolith:8080
   ```
4. **Full Extraction**: When all modules are extracted, switch to `SERVICE_LOCATOR_MODE=http`
5. **No Code Changes**: Service Locator handles the switch automatically

## Microservices Migration Strategy

### Step 1: Database Separation

1. Create separate databases for each module
2. Update `.env` with module-specific DB configs
3. Run migrations on each database

### Step 2: Extract Module

1. Copy module to new service repository
2. Set up independent Laravel application
3. Configure database connection
4. Deploy as separate service

### Step 3: Update Communication

1. Set `SERVICE_LOCATOR_MODE=hybrid` in remaining services (for partial extraction)
2. Configure per-service mode and URLs:
   ```env
   # Example: Only Payments extracted
   SERVICE_LOCATOR_MODE=hybrid
   SERVICE_PAYMENTS_MODE=http
   SERVICE_PAYMENTS_URL=http://payments-service:8000
   # Other services remain local (default)
   ```
3. Service Locator automatically routes local calls to container, HTTP calls to remote services
4. When all modules are extracted, switch to `SERVICE_LOCATOR_MODE=http`

### Step 4: API Gateway

1. Deploy dedicated API Gateway service
2. Route all requests through gateway
3. Gateway handles service discovery and routing

## Contract Versioning

Contracts and DTOs should be versioned to allow independent service deployment and backward compatibility.

### Directory Structure

```
shared/
├── Contracts/
│   ├── V1/
│   │   ├── UserServiceInterface.php
│   │   ├── OrderServiceInterface.php
│   │   ├── PaymentServiceInterface.php
│   │   └── InventoryServiceInterface.php
│   ├── UserServiceInterface.php      # Alias to V1 (backward compatible)
│   ├── OrderServiceInterface.php
│   ├── PaymentServiceInterface.php
│   └── InventoryServiceInterface.php
├── DTOs/
│   ├── V1/
│   │   ├── UserDTO.php
│   │   ├── OrderDTO.php
│   │   └── PaymentDTO.php
│   ├── UserDTO.php                   # Alias to V1 (backward compatible)
│   ├── OrderDTO.php
│   └── PaymentDTO.php
└── Events/
    └── ...
```

### Usage

**Recommended (explicit V1):**
```php
use Shared\Contracts\V1\UserServiceInterface;
use Shared\DTOs\V1\UserDTO;

class UserService implements UserServiceInterface
{
    public function create(UserDTO $data): User { ... }
}
```

**Backward compatible (alias):**
```php
// Still works - aliases to V1
use Shared\Contracts\UserServiceInterface;
use Shared\DTOs\UserDTO;
```

### When to Create a New Version

| Change Type | New Version Required? |
|-------------|----------------------|
| Add new method to interface | ✅ Yes |
| Add new field to DTO | ✅ Yes |
| Change return type | ✅ Yes |
| Change parameter type | ✅ Yes |
| Fix implementation bug | ❌ No |
| Performance optimization | ❌ No |

### Creating V2

When breaking changes are needed:

1. **Create V2 directory** with new interfaces/DTOs
2. **Keep V1** working (backward compatible)
3. **Migrate services gradually** to V2
4. **Deprecate V1** when all services migrated
5. **Remove V1** after deprecation period (e.g., 6 months)

**Example V2 interface:**
```php
// shared/Contracts/V2/UserServiceInterface.php
namespace Shared\Contracts\V2;

interface UserServiceInterface
{
    public function find(int $id): UserDTO;
    public function findByEmail(string $email): UserDTO;  // New method
    public function findWithRoles(int $id): UserDTO;      // New method
}
```

### Service Provider Registration

```php
// Bind V1 interface
$this->app->singleton(
    \Shared\Contracts\V1\UserServiceInterface::class,
    UserService::class
);

// Backward compatibility alias
$this->app->alias(
    \Shared\Contracts\V1\UserServiceInterface::class,
    \Shared\Contracts\UserServiceInterface::class
);
```

## Best Practices

1. **Always use Service Locator** for cross-module communication
2. **Use DTOs** for data transfer between modules
3. **Use Events** for asynchronous communication
4. **Keep modules independent** - no direct model relationships across modules
5. **Version your contracts** - Shared contracts/DTOs should be versioned
6. **Use explicit V1 imports** - Prefer `Shared\Contracts\V1\*` over `Shared\Contracts\*`

