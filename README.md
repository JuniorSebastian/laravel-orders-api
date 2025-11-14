# API REST - Pedidos y Pagos

API REST para gestión de pedidos y procesamiento de pagos integrando con API externa. Construida con Laravel 11, aplicando principios SOLID y Clean Architecture.

## Requisitos

| Componente | Versión |
|------------|---------|
| PHP | 8.2+ |
| PostgreSQL | 13+ |
| Composer | 2.0+ |
| Extensiones PHP | `pdo_pgsql`, `mbstring`, `curl` |

## Instalación

### 1. Clonar Repositorio

```bash
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api
```

### 2. Instalar Dependencias

```bash
composer install
```

### 3. Configurar .env

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:f8b2WFd6Z7lEwMLRjJYxL9hTHqDNA+x+rRdlh5/AnqM=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=orders_payments
DB_USERNAME=postgres
DB_PASSWORD=root

PAYMENT_GATEWAY_URL=https://reqres.in/api
PAYMENT_GATEWAY_API_KEY=reqres-free-v1
```

### 4. Crear Base de Datos

```bash
psql -U postgres
CREATE DATABASE orders_payments;
\q
```

### 5. Ejecutar Migraciones

```bash
php artisan migrate
```

### 6. Iniciar Servidor

```bash
php artisan serve
# http://127.0.0.1:8000
```

### 7. Ejecutar Tests

```bash
php artisan test
# Tests: 11 passed (48 assertions)
```

---

## API Endpoints

Base URL: `http://localhost:8000/api`

### GET /api/orders

Listar todos los pedidos con sus pagos.

**Request:**
```bash
curl http://localhost:8000/api/orders
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "customer_name": "Juan Pérez",
      "total_amount": "150.50",
      "status": "paid",
      "payment_attempts": 1,
      "payments": [...],
      "created_at": "2025-11-14T05:47:19.000000Z"
    }
  ]
}
```

### POST /api/orders

Crear un nuevo pedido.

**Request:**
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Juan Pérez",
    "total_amount": 150.50
  }'
```

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "customer_name": "Juan Pérez",
    "total_amount": "150.50",
    "status": "pending",
    "payment_attempts": 0,
    "payments": []
  }
}
```

### GET /api/orders/{id}

Ver detalles de un pedido.

**Request:**
```bash
curl http://localhost:8000/api/orders/1
```

**Response:** `200 OK`

### POST /api/payments

Procesar un pago para un pedido.

**Request:**
```bash
curl -X POST http://localhost:8000/api/payments \
  -H "Content-Type: application/json" \
  -d '{"order_id": 1}'
```

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "order_id": 1,
    "amount": "150.50",
    "status": "success",
    "created_at": "2025-11-14T10:35:00.000000Z"
  }
}
```

**Estados del Pedido:**
- `pending` → Estado inicial
- `paid` → Pago exitoso (no permite más pagos)
- `failed` → Pago fallido (permite reintentos)

---

## Arquitectura

### Estructura del Proyecto

```
app/
├── Contracts/
│   └── PaymentGatewayInterface.php    # Interface para DI
├── Enums/
│   ├── OrderStatus.php                # PENDING, PAID, FAILED
│   └── PaymentStatus.php              # SUCCESS, FAILED
├── Exceptions/
│   └── PaymentProcessingException.php # Excepciones personalizadas
├── Http/
│   ├── Controllers/Api/
│   │   ├── OrderController.php
│   │   └── PaymentController.php
│   ├── Requests/
│   │   ├── StoreOrderRequest.php     # Validaciones
│   │   └── StorePaymentRequest.php
│   └── Resources/
│       ├── OrderResource.php          # JSON transformation
│       └── PaymentResource.php
├── Models/
│   ├── Order.php
│   └── Payment.php
└── Services/
    ├── OrderPaymentService.php        # Lógica de negocio
    └── PaymentGatewayService.php      # Integración API externa
```

### Principios SOLID

**Single Responsibility:** Cada clase tiene una responsabilidad única.
- `OrderController` → Maneja HTTP
- `OrderPaymentService` → Lógica de negocio
- `PaymentGatewayService` → Integración externa

**Open/Closed:** Extensible sin modificar código existente.
```php
interface PaymentGatewayInterface {
    public function processPayment(float $amount, int $orderId): array;
}

// Implementaciones intercambiables
class PaymentGatewayService implements PaymentGatewayInterface {}
class StripeGateway implements PaymentGatewayInterface {}
```

**Liskov Substitution:** Cualquier implementación es intercambiable.
```php
class OrderPaymentService {
    public function __construct(
        private PaymentGatewayInterface $gateway  // Cualquier implementación funciona
    ) {}
}
```

**Interface Segregation:** Interfaces específicas y mínimas.
```php
interface PaymentGatewayInterface {
    public function processPayment(float $amount, int $orderId): array;
    // Solo lo necesario, sin métodos innecesarios
}
```

**Dependency Inversion:** Dependemos de abstracciones.
```php
// AppServiceProvider.php
$this->app->bind(
    PaymentGatewayInterface::class,
    PaymentGatewayService::class
);
```

### Patrones de Diseño

**Service Pattern** - Lógica de negocio en servicios reutilizables
**Strategy Pattern** - Múltiples gateways intercambiables
**Repository Pattern** - Eloquent ORM como abstracción de datos
**Factory Pattern** - Factories para testing

### Gestión de Estados

```
PENDING → [Pago Exitoso] → PAID (final)
PENDING → [Pago Fallido] → FAILED (permite reintentos)
FAILED  → [Pago Exitoso] → PAID (final)
PAID    → [Intento] → ERROR 422
```

Implementado con Enums tipados (PHP 8.1+):

```php
enum OrderStatus: string {
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';

    public function canReceivePayment(): bool {
        return match($this) {
            self::PENDING, self::FAILED => true,
            self::PAID => false,
        };
    }
}
```

---

## Testing

### Ejecutar Tests

```bash
php artisan test
```

**Resultado:**
```
Tests:    11 passed (48 assertions)
Duration: 1.63s
```

### Cobertura

| Categoría | Tests | Descripción |
|-----------|-------|-------------|
| Orders | 4 | Crear, listar, consultar, validaciones |
| Payments | 7 | Procesar, reintentar, validar estados |
| Total | 11 | 48 assertions, 100% endpoints cubiertos |

### Estrategia

**Feature Tests** - Flujos end-to-end completos
**HTTP Fakes** - Mock de API externa para tests predecibles
**RefreshDatabase** - Base de datos limpia en cada test

Ejemplo:
```php
public function test_can_process_successful_payment() {
    Http::fake([
        'reqres.in/*' => Http::response(['id' => 123], 201)
    ]);

    $order = Order::factory()->create();
    $response = $this->postJson('/api/payments', ['order_id' => $order->id]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
}
```

---

## Decisiones Técnicas

### Enums Tipados (PHP 8.1+)
Uso de backed enums para estados:
- Type-safety en compile-time
- Autocompletado en IDEs
- Imposibilidad de valores inválidos
- Métodos helper en el enum

### Excepciones Personalizadas
`PaymentProcessingException` con factory methods:
```php
throw PaymentProcessingException::orderCannotReceivePayment($orderId, $status);
```

### Transacciones de Base de Datos
`DB::transaction()` para atomicidad:
- Registro del pago + actualización del pedido en una sola transacción
- Rollback automático en caso de error

### API Externa
ReqRes.in como gateway simulado:
- POST /users retorna 201 con 'id' = éxito
- Otros códigos o sin 'id' = fallo
- Timeout: 10 segundos
- SSL verification desactivado (solo desarrollo)

---

## Tecnologías

- Laravel 11 (PHP 8.2+)
- PostgreSQL 13+
- PHPUnit 10
- ReqRes.in

---

## Licencia

MIT License

Copyright (c) 2025 Junior Sebastian Osorio

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

## Autor

**Junior Sebastian Osorio**  
GitHub: https://github.com/JuniorSebastian
