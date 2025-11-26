# Module Development Guide

## Project Structure

```
laravmo/
├── app/
│   ├── Console/Commands/
│   │   └── KafkaConsumeCommand.php      # Kafka consumer command
│   ├── Http/Controllers/
│   │   └── ApiGatewayController.php     # API Gateway for centralized routing
│   ├── Infrastructure/
│   │   ├── EventBus/
│   │   │   ├── Adapters/
│   │   │   │   ├── KafkaAdapter.php     # Kafka remote adapter
│   │   │   │   ├── LocalAdapter.php      # Local event adapter
│   │   │   │   └── NullAdapter.php       # Null adapter for testing
│   │   │   ├── Contracts/
│   │   │   │   └── RemoteEventAdapter.php
│   │   │   ├── EventBus.php              # Main event bus
│   │   │   └── KafkaEventBus.php         # Kafka-backed event bus
│   │   └── ServiceLocator/
│   │       ├── Adapters/
│   │       │   ├── HttpServiceLocator.php    # HTTP adapter (full microservices)
│   │       │   ├── HybridServiceLocator.php  # Hybrid adapter (partial extraction)
│   │       │   └── LocalServiceLocator.php   # Local adapter (monolith)
│   │       ├── Contracts/
│   │       │   └── ServiceLocatorInterface.php
│   │       └── ServiceLocator.php            # Factory
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── EventBusServiceProvider.php
│
├── Modules/
│   ├── Users/
│   │   ├── Http/Controllers/
│   │   │   └── UserController.php
│   │   ├── Models/
│   │   │   └── User.php                   # Uses 'users' connection
│   │   ├── Services/
│   │   │   └── UserService.php            # Implements UserServiceInterface
│   │   ├── Listeners/
│   │   │   └── PublishUserCreatedListener.php
│   │   ├── Providers/
│   │   │   ├── UsersServiceProvider.php   # Binds UserServiceInterface
│   │   │   ├── EventServiceProvider.php   # Registers listeners
│   │   │   └── RouteServiceProvider.php   # Loads routes
│   │   └── routes/
│   │       └── api.php                    # Module API routes
│   │
│   ├── Orders/
│   │   ├── Http/Controllers/
│   │   │   └── OrderController.php
│   │   ├── Models/
│   │   │   └── Order.php                  # Uses 'orders' connection
│   │   ├── Services/
│   │   │   └── OrderService.php           # Uses ServiceLocator for InventoryService
│   │   ├── Listeners/
│   │   │   └── OnUserCreatedListener.php  # Listens to UserCreated event
│   │   └── ...
│   │
│   ├── Payments/
│   │   ├── Models/
│   │   │   └── Payment.php                 # Uses 'payments' connection
│   │   ├── Services/
│   │   │   └── PaymentService.php
│   │   ├── Listeners/
│   │   │   └── OnOrderCreatedListener.php # Listens to OrderCreated event
│   │   └── ...
│   │
│   └── Inventory/
│       ├── Models/
│       │   └── Product.php                 # Uses 'inventory' connection
│       ├── Services/
│       │   └── InventoryService.php
│       └── ...
│
├── shared/
│   ├── Contracts/
│   │   ├── V1/                             # Versioned contracts (recommended)
│   │   │   ├── UserServiceInterface.php
│   │   │   ├── OrderServiceInterface.php
│   │   │   ├── PaymentServiceInterface.php
│   │   │   └── InventoryServiceInterface.php
│   │   ├── UserServiceInterface.php        # Alias to V1 (backward compatible)
│   │   ├── OrderServiceInterface.php
│   │   ├── PaymentServiceInterface.php
│   │   └── InventoryServiceInterface.php
│   ├── DTOs/
│   │   ├── V1/                             # Versioned DTOs (recommended)
│   │   │   ├── UserDTO.php
│   │   │   ├── OrderDTO.php
│   │   │   └── PaymentDTO.php
│   │   ├── UserDTO.php                     # Alias to V1 (backward compatible)
│   │   ├── OrderDTO.php
│   │   └── PaymentDTO.php
│   └── Events/
│       ├── UserCreated.php                 # Domain events
│       ├── OrderCreated.php
│       └── PaymentCompleted.php
│
├── routes/
│   ├── api.php                            # Main API routes (includes modules.php)
│   ├── modules.php                        # Auto-loads all module routes
│   └── web.php
│
├── config/
│   ├── database.php                       # Module-specific DB connections
│   ├── eventbus.php                       # Event bus configuration
│   └── services.php                       # Service URLs (for microservices)
│
└── docs/
    ├── ARCHITECTURE.md                    # Architecture overview
    └── MODULE_GUIDE.md                    # This file
```

## Module Interactions

### 1. Direct Service Call (via Service Locator)

**Scenario**: Orders module needs to check inventory availability.

**✅ Correct Approach** (Using Service Locator):

```php
// Modules/Orders/Services/OrderService.php
namespace Modules\Orders\Services;

use App\Infrastructure\ServiceLocator\ServiceLocator;
use Shared\Contracts\V1\InventoryServiceInterface;
use Shared\Contracts\V1\OrderServiceInterface;

class OrderService implements OrderServiceInterface
{
    private readonly InventoryServiceInterface $inventory;

    public function __construct()
    {
        // Service Locator resolves to:
        // - LocalServiceLocator in monolith (SERVICE_LOCATOR_MODE=local)
        // - HttpServiceLocator in full microservices (SERVICE_LOCATOR_MODE=http)
        // - HybridServiceLocator for partial extraction (SERVICE_LOCATOR_MODE=hybrid)
        $this->inventory = ServiceLocator::make()
            ->resolve(InventoryServiceInterface::class);
    }

    public function create(OrderDTO $data): Order
    {
        return DB::connection('orders')->transaction(function () use ($data) {
            // Cross-module call via interface
            $product = $this->inventory->reserve($data->productSku, $data->quantity);

            $order = Order::create([
                'user_id' => $data->userId,
                'product_sku' => $product->sku,
                'quantity' => $data->quantity,
                'total' => $product->price * $data->quantity,
            ]);

            // Emit event for other modules
            event(new OrderCreated(OrderDTO::fromModel($order)));

            return $order;
        });
    }
}
```

**❌ Anti-Pattern** (Direct Model Access):

```php
// DON'T DO THIS - Direct model access across modules
use Modules\Inventory\Models\Product;

class OrderService
{
    public function create(OrderDTO $data): Order
    {
        // ❌ Direct model access breaks module boundaries
        $product = Product::where('sku', $data->productSku)->first();
        
        // ❌ Cross-database queries won't work in microservices
        $order = Order::create([...]);
    }
}
```

### 2. Event-Driven Communication

**Scenario**: When a user is created, automatically create a welcome order.

**✅ Correct Approach** (Event Listener):

```php
// Modules/Users/Services/UserService.php
namespace Modules\Users\Services;

use Shared\Events\UserCreated;

class UserService implements UserServiceInterface
{
    public function create(UserDTO $data): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        // Emit domain event
        event(new UserCreated(UserDTO::fromModel($user)));

        return $user;
    }
}
```

```php
// Modules/Users/Listeners/PublishUserCreatedListener.php
namespace Modules\Users\Listeners;

use App\Infrastructure\EventBus\EventBus;
use Shared\Events\UserCreated;

class PublishUserCreatedListener
{
    public function __construct(private readonly EventBus $bus) {}

    public function handle(UserCreated $event): void
    {
        // Publish to Kafka for external systems
        $this->bus->publish($event->topic(), $event->payload());
    }
}
```

```php
// Modules/Orders/Listeners/OnUserCreatedListener.php
namespace Modules\Orders\Listeners;

use App\Infrastructure\ServiceLocator\ServiceLocator;
use Modules\Orders\Services\OrderService;
use Shared\Contracts\OrderServiceInterface;
use Shared\Events\UserCreated;

class OnUserCreatedListener
{
    private readonly OrderServiceInterface $orderService;

    public function __construct()
    {
        // Use Service Locator for consistency
        $this->orderService = ServiceLocator::make()
            ->resolve(OrderServiceInterface::class);
    }

    public function handle(UserCreated $event): void
    {
        // Create welcome order for new user
        $this->orderService->createFromUser($event->user);
    }
}
```

**Registration**:

```php
// Modules/Orders/Providers/EventServiceProvider.php
namespace Modules\Orders\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Orders\Listeners\OnUserCreatedListener;
use Shared\Events\UserCreated;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserCreated::class => [
            OnUserCreatedListener::class,
        ],
    ];
}
```

### 3. Database Boundary Separation

**Scenario**: Each module has its own database connection.

**✅ Correct Approach** (Module-Specific Connection):

```php
// Modules/Orders/Models/Order.php
namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Specify module-specific connection
    protected $connection = 'orders';
    
    protected $fillable = [
        'user_id',
        'product_sku',
        'quantity',
        'total',
        'status',
    ];

}
```

**Configuration**:

```php
// config/database.php
'connections' => [
    'orders' => [
        'driver' => 'mysql',
        'host' => env('ORDERS_DB_HOST', env('DB_HOST', '127.0.0.1')),
        'database' => env('ORDERS_DB_DATABASE', env('DB_DATABASE', 'laravmo_orders')),
        'username' => env('ORDERS_DB_USERNAME', env('DB_USERNAME', 'root')),
        'password' => env('ORDERS_DB_PASSWORD', env('DB_PASSWORD', '')),
        // ...
    ],
],
```

```env
# .env
# Default (all modules use same DB)
DB_DATABASE=laravmo

# Or separate databases
USERS_DB_DATABASE=laravmo_users
ORDERS_DB_DATABASE=laravmo_orders
PAYMENTS_DB_DATABASE=laravmo_payments
INVENTORY_DB_DATABASE=laravmo_inventory
```

## Critical Guidelines for Microservice Migration

### ✅ DO: Use Service Locator for Cross-Module Calls

**Why**: Service Locator abstracts the communication layer, allowing seamless transition from local to HTTP calls.

```php
// ✅ GOOD - Use V1 contracts explicitly
use Shared\Contracts\V1\InventoryServiceInterface;

$inventory = ServiceLocator::make()->resolve(InventoryServiceInterface::class);
$product = $inventory->reserve($sku, $quantity);
```

```php
// ❌ BAD - Direct dependency injection
public function __construct(
    private readonly InventoryServiceInterface $inventory
) {}
```

**Note**: The direct injection approach works in monolith but requires refactoring when extracting to microservices. Service Locator handles both cases automatically.

### ✅ DO: Use Hybrid Mode for Gradual Migration

**Why**: Allows extracting one module at a time without affecting others.

```env
# ✅ GOOD - Hybrid mode for partial extraction
SERVICE_LOCATOR_MODE=hybrid
SERVICE_PAYMENTS_MODE=http
SERVICE_PAYMENTS_URL=http://payments-service:8000
# Other services remain local by default
```

```env
# ❌ INEFFICIENT - Using full HTTP mode when most services are local
SERVICE_LOCATOR_MODE=http
SERVICE_USERS_URL=http://localhost:8080  # Wasteful self HTTP call
SERVICE_ORDERS_URL=http://localhost:8080  # Wasteful self HTTP call
SERVICE_PAYMENTS_URL=http://payments-service:8000
SERVICE_INVENTORY_URL=http://localhost:8080  # Wasteful self HTTP call
```

**Modes summary**:
- `local`: All services in same container (monolith)
- `hybrid`: Per-service configuration (gradual migration) ← **Recommended for partial extraction**
- `http`: All services via HTTP (full microservices)

### ✅ DO: Use DTOs for Data Transfer

**Why**: DTOs provide a stable contract between modules, independent of internal model structure.

```php
// ✅ GOOD - Using DTO
use Shared\DTOs\OrderDTO;

public function create(OrderDTO $data): Order
{
    // DTO is stable, model structure can change
    $order = Order::create([
        'user_id' => $data->userId,
        'product_sku' => $data->productSku,
        // ...
    ]);
}
```

```php
// ❌ BAD - Direct model passing
public function create(Order $order): Order
{
    // Model structure is internal, breaks when extracted
}
```

### ✅ DO: Use Events for Asynchronous Communication

**Why**: Events decouple modules and work seamlessly in both monolith and microservices (via Kafka).

```php
// ✅ GOOD - Event-driven
event(new OrderCreated(OrderDTO::fromModel($order)));
```

```php
// ❌ BAD - Direct service call for side effects
$orderService->create($order);
$emailService->sendOrderConfirmation($order); // Tight coupling
```

### ✅ DO: Use Module-Specific Database Connections

**Why**: Makes database extraction straightforward - just point connection to new service.

```php
// ✅ GOOD
class Order extends Model
{
    protected $connection = 'orders';
}
```

```php
// ❌ BAD - Using default connection
class Order extends Model
{
    // No connection specified - uses default
    // Harder to extract later
}
```

### ❌ DON'T: Use Eloquent Relationships Across Modules

**Why**: Cross-module relationships break when modules are in separate databases/services.

```php
// ❌ BAD - Cross-module relationship
class Order extends Model
{
    public function user(): BelongsTo
    {
        // This won't work when Users is a separate service
        return $this->belongsTo(User::class);
    }
    
    // Usage
    $order->user->name; // ❌ Breaks in microservices
}
```

```php
// ✅ GOOD - Use Service Locator or store reference only
// Get user via service call
public function getUser(): UserDTO
{
    $userService = ServiceLocator::make()
        ->resolve(UserServiceInterface::class);
    return UserDTO::fromModel($userService->find($this->user_id));
}
```

### ❌ DON'T: Share Models Between Modules

**Why**: Models are internal implementation details. Sharing them creates tight coupling.

```php
// ❌ BAD - Importing model from another module
use Modules\Users\Models\User;

class OrderService
{
    public function create(int $userId): Order
    {
        $user = User::find($userId); // ❌ Direct model access
    }
}
```

```php
// ✅ GOOD - Use Service Locator with V1 contracts
use Shared\Contracts\V1\UserServiceInterface;

class OrderService
{
    private readonly UserServiceInterface $userService;
    
    public function __construct()
    {
        $this->userService = ServiceLocator::make()
            ->resolve(UserServiceInterface::class);
    }
    
    public function create(int $userId): Order
    {
        $user = $this->userService->find($userId); // ✅ Via interface
    }
}
```

### ❌ DON'T: Use Database Transactions Across Modules

**Why**: Distributed transactions are complex. Use eventual consistency with events.

```php
// ❌ BAD - Cross-module transaction
DB::transaction(function () {
    $user = User::create([...]);
    $order = Order::create([...]); // Different DB connection
    // ❌ Won't work - can't transaction across databases
});
```

```php
// ✅ GOOD - Module-local transaction + events
// In Users module
DB::connection('users')->transaction(function () {
    $user = User::create([...]);
    event(new UserCreated(UserDTO::fromModel($user)));
});

// In Orders module (listener)
public function handle(UserCreated $event): void
{
    DB::connection('orders')->transaction(function () use ($event) {
        $order = Order::create([...]);
        // Each module manages its own transaction
    });
}
```

### ❌ DON'T: Hardcode Module URLs or Paths

**Why**: URLs change when extracting services. Use configuration.

```php
// ❌ BAD - Hardcoded URL
$response = Http::get('http://users-service:8000/api/users/1');
```

```php
// ✅ GOOD - Configuration-based
$baseUrl = config('services.users.url');
$response = Http::get("{$baseUrl}/api/users/1");
```

### ✅ DO: Version Your Contracts

**Why**: Services evolve independently. Versioning prevents breaking changes.

```php
// ✅ GOOD - V1 contracts (current version)
// shared/Contracts/V1/UserServiceInterface.php
namespace Shared\Contracts\V1;

interface UserServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(UserDTO $data): User;
    public function find(int $id): User;
}

// Later, add V2 without breaking V1
// shared/Contracts/V2/UserServiceInterface.php
namespace Shared\Contracts\V2;

interface UserServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function create(UserDTO $data): User;
    public function find(int $id): User;
    public function findByEmail(string $email): User;    // New method
    public function findWithRoles(int $id): User;        // New method
}
```

**Service Provider with versioning:**
```php
// Modules/Users/Providers/UsersServiceProvider.php
public function register(): void
{
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
}
```

### ✅ DO: Use API Gateway for Centralized Routing

**Why**: Provides single entry point, easier to add cross-cutting concerns.

```php
// ✅ GOOD - Via Gateway
GET /api/gateway/users
GET /api/gateway/orders

// Gateway handles:
// - Authentication
// - Rate limiting
// - Request logging
// - Service discovery
```

## Migration Checklist

When extracting a module to a microservice:

- [ ] **Database**: Create separate database, update connection config
- [ ] **Service Locator**: Configure hybrid mode for gradual migration
  ```env
  # In main monolith (example: extracting Payments)
  SERVICE_LOCATOR_MODE=hybrid
  SERVICE_PAYMENTS_MODE=http
  SERVICE_PAYMENTS_URL=http://payments-service:8000
  
  # In extracted service (Payments)
  SERVICE_LOCATOR_MODE=hybrid
  SERVICE_USERS_MODE=http
  SERVICE_USERS_URL=http://monolith:8080
  SERVICE_ORDERS_MODE=http
  SERVICE_ORDERS_URL=http://monolith:8080
  SERVICE_INVENTORY_MODE=http
  SERVICE_INVENTORY_URL=http://monolith:8080
  ```
- [ ] **Service URLs**: Configure `SERVICE_{MODULE}_MODE` and `SERVICE_{MODULE}_URL`
- [ ] **Events**: Ensure Kafka topics are accessible from new service
- [ ] **API Gateway**: Update gateway routing
- [ ] **Contracts**: Publish shared contracts as composer package
- [ ] **DTOs**: Ensure DTOs are versioned and backward compatible
- [ ] **Remove Relationships**: Replace Eloquent relationships with service calls
- [ ] **Transactions**: Split cross-module transactions into local + events
- [ ] **Full Migration**: When all modules extracted, switch to `SERVICE_LOCATOR_MODE=http`

## Example: Complete Module Interaction Flow

### Creating an Order (End-to-End)

```php
// 1. Client calls API
POST /api/orders
{
    "user_id": 1,
    "product_sku": "SKU-100",
    "quantity": 2
}

// 2. OrderController receives request
// Modules/Orders/Http/Controllers/OrderController.php
public function store(Request $request): JsonResponse
{
    $dto = OrderDTO::fromRequest($request);
    $order = $this->orderService->create($dto);
    return response()->json($order);
}

// 3. OrderService uses Service Locator to call InventoryService
// Modules/Orders/Services/OrderService.php
public function create(OrderDTO $data): Order
{
    // Service Locator resolves InventoryServiceInterface
    $inventory = ServiceLocator::make()
        ->resolve(InventoryServiceInterface::class);
    
    // Cross-module call (local in monolith, HTTP in microservices)
    $product = $inventory->reserve($data->productSku, $data->quantity);
    
    // Local transaction in Orders database
    return DB::connection('orders')->transaction(function () use ($data, $product) {
        $order = Order::create([...]);
        
        // Emit event for other modules
        event(new OrderCreated(OrderDTO::fromModel($order)));
        
        return $order;
    });
}

// 4. Event is published to Kafka
// Modules/Orders/Listeners/PublishOrderCreatedListener.php
public function handle(OrderCreated $event): void
{
    $this->eventBus->publish($event->topic(), $event->payload());
}

// 5. Payments module listens to event
// Modules/Payments/Listeners/OnOrderCreatedListener.php
public function handle(OrderCreated $event): void
{
    // Automatically create payment record
    $this->paymentService->createForOrder($event->order);
}
```

## Summary

**Key Principles**:

1. **Service Locator** for all cross-module calls
2. **Hybrid mode** for gradual microservice extraction
3. **DTOs** for data transfer
4. **Events** for asynchronous communication
5. **Module-specific connections** for database separation
6. **No cross-module relationships** - use service calls
7. **No shared models** - use interfaces
8. **Configuration-based** URLs and settings
9. **Version contracts** for backward compatibility

**Service Locator Modes**:

| Mode | Use Case | Configuration |
|------|----------|---------------|
| `local` | Monolith | `SERVICE_LOCATOR_MODE=local` |
| `hybrid` | Partial extraction | `SERVICE_LOCATOR_MODE=hybrid` + per-service `SERVICE_{NAME}_MODE` |
| `http` | Full microservices | `SERVICE_LOCATOR_MODE=http` + all `SERVICE_{NAME}_URL` |

