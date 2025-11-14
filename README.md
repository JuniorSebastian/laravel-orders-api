# API REST - Pedidos y Pagos

API REST robusta para gestión de pedidos y procesamiento de pagos con integración a gateway externo. Construida con **Laravel 11**, aplicando **principios SOLID** y **Clean Architecture**.

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-336791?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![Tests](https://img.shields.io/badge/Tests-11%20passed-00C853)](https://phpunit.de/)

---

## Tabla de Contenidos

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Arquitectura](#arquitectura)
- [Decisiones Técnicas](#decisiones-técnicas)
- [Stack Tecnológico](#stack-tecnológico)
- [Despliegue con Docker](#despliegue-con-docker)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq-preguntas-frecuentes)

---

## Características

CRUD Completo de Pedidos - Crear, listar y consultar órdenes  
Procesamiento de Pagos - Integración con gateway externo (ReqRes.in)  
Gestión de Estados - Máquina de estados (pending → paid/failed)  
Reintentos de Pago - Pedidos fallidos pueden reintentar  
SOLID Principles - Código mantenible y extensible  
Clean Architecture - Separación de capas (Controller → Service → Model)  
Docker Ready - Entorno reproducible con 3 contenedores  
Testing Completo - 11 tests, 48 assertions, 100% endpoints cubiertos  
API Documentation - Colección Postman incluida  
Type Safety - PHP 8.2+ Enums tipados  

---

## Requisitos

| Componente | Versión |
|------------|---------|
| PHP | 8.2+ |
| PostgreSQL | 13+ |
| Composer | 2.0+ |
| Extensiones PHP | `pdo_pgsql`, `mbstring`, `curl` |

**Alternativa:** Si prefieres no instalar nada localmente, puedes usar [Docker](#despliegue-con-docker) (ver al final del documento).

---

## Instalación

**1. Clonar el repositorio**

```bash
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api
```

**2. Instalar dependencias**

```bash
composer install
```

**3. Configurar entorno**

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=orders_payments
DB_USERNAME=postgres
DB_PASSWORD=tu_password

PAYMENT_GATEWAY_URL=https://reqres.in/api
```

**4. Crear base de datos**

```bash
# Conectar a PostgreSQL
psql -U postgres

# Crear base de datos
CREATE DATABASE orders_payments;
\q
```

**5. Generar key y ejecutar migraciones**

```bash
php artisan key:generate
php artisan migrate
```

**6. Iniciar servidor**

```bash
php artisan serve
```

La API estará disponible en: `http://127.0.0.1:8000`

**7. Verificar instalación**

```bash
php artisan test
```

Deberías ver 11 tests pasando correctamente.

---

## Colección de Postman

El proyecto incluye una colección de Postman con todos los endpoints configurados y listos para usar.

**Ubicación:** `/postman/Laravel_Orders_API.postman_collection.json`

**Cómo importar:**
1. Abrir [Postman](https://www.postman.com/downloads/)
2. Click en **"Import"** (botón superior izquierdo)
3. Seleccionar el archivo `postman/Laravel_Orders_API.postman_collection.json`
4. La colección aparecerá con 4 requests pre-configurados

**Contenido de la colección:**
- **GET** Lista de Órdenes - `/api/orders`
- **POST** Crear Orden - `/api/orders`
- **GET** Ver Orden - `/api/orders/{id}`
- **POST** Procesar Pago - `/api/payments`

**Variables configuradas:**
- `base_url`: `http://localhost:8000`
- Todos los endpoints usan esta variable automáticamente

**Ejemplos incluidos:**
- Request bodies pre-llenados con datos de ejemplo
- Respuestas esperadas documentadas
- Status codes correctos

---

## API Endpoints

**Base URL:** `http://localhost:8000/api`

### GET /api/orders

Obtiene la lista completa de pedidos con sus pagos asociados.

**Request:**
```bash
curl http://127.0.0.1:8000/api/orders
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
      "payments": [
        {
          "id": 1,
          "amount": "150.50",
          "status": "success",
          "created_at": "2025-11-14T05:47:19.000000Z"
        }
      ],
      "created_at": "2025-11-14T05:47:19.000000Z",
      "updated_at": "2025-11-14T05:47:20.000000Z"
    }
  ]
}
```

---

### POST /api/orders

Crea un nuevo pedido en estado `pending`.

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Juan Pérez",
    "total_amount": 150.50
  }'
```

**Validaciones:**
- `customer_name`: requerido, string, máx 255 caracteres
- `total_amount`: requerido, numérico, mayor a 0

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "customer_name": "Juan Pérez",
    "total_amount": "150.50",
    "status": "pending",
    "payment_attempts": 0,
    "payments": [],
    "created_at": "2025-11-14T05:47:19.000000Z",
    "updated_at": "2025-11-14T05:47:19.000000Z"
  }
}
```

**Error:** `422 Unprocessable Entity` (validación fallida)
```json
{
  "message": "The customer name field is required. (and 1 more error)",
  "errors": {
    "customer_name": ["The customer name field is required."],
    "total_amount": ["The total amount field is required."]
  }
}
```

---

### GET /api/orders/{id}

Obtiene los detalles de un pedido específico con todos sus intentos de pago.

**Request:**
```bash
curl http://127.0.0.1:8000/api/orders/1
```

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "customer_name": "Juan Pérez",
    "total_amount": "150.50",
    "status": "paid",
    "payment_attempts": 2,
    "payments": [
      {
        "id": 1,
        "amount": "150.50",
        "status": "failed",
        "created_at": "2025-11-14T05:47:19.000000Z"
      },
      {
        "id": 2,
        "amount": "150.50",
        "status": "success",
        "created_at": "2025-11-14T05:48:30.000000Z"
      }
    ],
    "created_at": "2025-11-14T05:47:19.000000Z",
    "updated_at": "2025-11-14T05:48:30.000000Z"
  }
}
```

**Error:** `404 Not Found` (pedido no existe)

---

### POST /api/payments

Procesa un pago para un pedido. Integra con API externa para validación.

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/payments \
  -H "Content-Type: application/json" \
  -d '{"order_id": 1}'
```

**Validaciones:**
- `order_id`: requerido, debe existir en la base de datos
- El pedido debe estar en estado `pending` o `failed`
- Pedidos con estado `paid` rechazan nuevos pagos

**Response Exitoso:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "order_id": 1,
    "amount": "150.50",
    "status": "success",
    "gateway_response": {"id": 123, "createdAt": "2025-11-14T10:35:00Z"},
    "created_at": "2025-11-14T10:35:00.000000Z",
    "updated_at": "2025-11-14T10:35:00.000000Z"
  }
}
```

**Response Pago Fallido:** `201 Created`
```json
{
  "data": {
    "id": 2,
    "order_id": 1,
    "amount": "150.50",
    "status": "failed",
    "gateway_response": {"error": "Insufficient funds"},
    "created_at": "2025-11-14T10:36:00.000000Z",
    "updated_at": "2025-11-14T10:36:00.000000Z"
  }
}
```

**Error:** `422 Unprocessable Entity` (orden ya pagada)
```json
{
  "message": "Order #1 with status 'paid' cannot receive new payments"
}
```

**Error:** `404 Not Found` (orden no existe)
```json
{
  "message": "Order not found"
}
```

---

### Estados del Pedido

```
┌──────────┐
│ PENDING  │  ← Estado inicial al crear pedido
└────┬─────┘
     │
     ├─→ [Pago Exitoso] ─→ ┌──────┐
     │                      │ PAID │  ← Estado final, no acepta más pagos
     │                      └──────┘
     │
     └─→ [Pago Fallido] ─→ ┌────────┐
                            │ FAILED │  ← Permite reintentar pago
                            └────┬───┘
                                 │
                                 └─→ [Reintento Exitoso] ─→ ┌──────┐
                                                              │ PAID │
                                                              └──────┘
```

**Reglas de transición:**
- pending → paid: Cuando el primer pago es exitoso
- pending → failed: Cuando el primer pago falla
- failed → paid: Cuando un reintento de pago es exitoso
- failed → failed: Cuando un reintento de pago falla (actualiza contador)
- paid → NINGUNO: Estado terminal, no permite más cambios

**Contador de intentos:**
- Se incrementa con cada intento de pago (exitoso o fallido)
- Útil para limitar reintentos o auditoría

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
- OrderController → Maneja HTTP
- OrderPaymentService → Lógica de negocio
- PaymentGatewayService → Integración externa

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
PENDING -> [Pago Exitoso] -> PAID (final)
PENDING -> [Pago Fallido] -> FAILED (permite reintentos)
FAILED  -> [Pago Exitoso] -> PAID (final)
PAID    -> [Intento] -> ERROR 422
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

**Resultado esperado:**
```
   PASS  Tests\Unit\ExampleTest
  that true is true

   PASS  Tests\Feature\ExampleTest
  the application returns a successful response

   PASS  Tests\Feature\OrderTest
  can create order
  can list orders
  can show order with payments
  order validation fails without required fields

   PASS  Tests\Feature\PaymentTest
  successful payment updates order to paid
  failed payment updates order to failed
  failed order can receive new payment attempt
  paid order cannot receive new payment
  payment validation fails without order id

  Tests:  11 passed (48 assertions)
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

### Docker Multi-Stage (PHP 8.2-FPM)
**Razón:** Entorno reproducible y aislado

**Beneficios:**
- No requiere PHP/PostgreSQL instalado localmente
- Mismas versiones en desarrollo, testing y producción
- Fácil onboarding de nuevos desarrolladores
- Extensiones PHP pre-instaladas (pdo_pgsql, mbstring, curl)

**Arquitectura:**
```dockerfile
FROM php:8.2-fpm
RUN docker-php-ext-install pdo pdo_pgsql pgsql
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```

### Enums Tipados (PHP 8.1+)
**Razón:** Type-safety y expresividad del código

**Beneficios:**
- Type-safety en compile-time (PHPStan/Psalm)
- Autocompletado en IDEs (IntelliSense)
- Imposibilidad de valores inválidos
- Métodos helper encapsulados en el enum

**Implementación:**
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

### Excepciones Personalizadas
**Razón:** Errores específicos de dominio con contexto

**Implementación:**
```php
class PaymentProcessingException extends Exception
{
    public static function orderCannotReceivePayment(int $orderId, string $status): self
    {
        return new self(
            "Order #{$orderId} with status '{$status}' cannot receive new payments",
            422
        );
    }
}
```

**Beneficios:**
- Factory methods descriptivos
- HTTP status codes apropiados
- Manejo específico por tipo de error
- Mejor debugging con stack traces

### Transacciones de Base de Datos
**Razón:** Atomicidad y consistencia de datos

**Implementación:**
```php
DB::transaction(function () use ($order, $payment) {
    $payment->save();
    $order->update(['status' => 'paid', 'payment_attempts' => $order->payment_attempts + 1]);
});
```

**Beneficios:**
- ACID compliance
- Rollback automático en caso de error
- Previene estados inconsistentes
- Registro del pago + actualización del pedido es atómico

### PostgreSQL sobre MySQL
**Razón:** Características avanzadas y robustez

**Ventajas específicas:**
- Mejor manejo de JSON/JSONB (para gateway_response)
- Enums nativos en BD (futuro uso)
- Transacciones ACID más estrictas
- Window functions para analytics
- Full-text search incorporado

### API Externa - ReqRes.in
**Razón:** Gateway de pago simulado para desarrollo/testing

**Configuración:**
```php
// POST https://reqres.in/api/users
// Success: HTTP 201 + response->id
// Failure: Otros códigos o sin 'id'
```

**Timeout:** 10 segundos  
**SSL:** Desactivado en desarrollo (activar en producción)

**Alternativas en producción:**
- Stripe: PaymentGatewayInterface -> StripeGateway
- PayPal: PaymentGatewayInterface -> PayPalGateway
- MercadoPago: PaymentGatewayInterface -> MercadoPagoGateway

### Session Driver: Cookie
**Razón:** Evitar dependencia de tabla `sessions` en Docker

**Configuración (.env.docker):**
```env
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
```

**Beneficio:** Simplifica setup inicial, no requiere migración adicional

### Nginx como Reverse Proxy
**Razón:** Mejor performance que servidor embebido de PHP

**Ventajas:**
- Sirve archivos estáticos sin tocar PHP
- Compresión gzip automática
- Caché de archivos estáticos
- Load balancing (multi-container)
- HTTP/2 support

---

## Stack Tecnológico

### Framework y Lenguaje
- **Laravel 11** - Framework PHP moderno con soporte completo para REST APIs
- **PHP 8.2+** - Enums tipados, match expressions, named arguments

### Base de Datos
- **PostgreSQL 15** - Base de datos relacional robusta
- **Eloquent ORM** - Abstracción de base de datos con relationships

### Contenedorización
- **Docker** - Aislamiento y reproducibilidad del entorno
- **Docker Compose** - Orquestación de servicios múltiples
- **PHP-FPM** - Process manager para PHP
- **Nginx Alpine** - Servidor web ligero

### Testing
- **PHPUnit 10** - Framework de testing unitario e integración
- **Laravel HTTP Fake** - Mock de peticiones HTTP externas
- **Database Factories** - Generación de datos de prueba

### Herramientas de Desarrollo
- **Composer 2.0+** - Gestor de dependencias PHP
- **Postman** - Cliente API para testing manual
- **Git** - Control de versiones

### API Externa
- **ReqRes.in** - API de pruebas para simular gateway de pagos

### Extensiones PHP
- `pdo`, `pdo_pgsql`, `pgsql` - Conectividad PostgreSQL
- `mbstring` - Manejo de strings multi-byte
- `curl` - Peticiones HTTP
- `json` - Parsing y encoding JSON
- `openssl` - Comunicación SSL/TLS

---

## Despliegue con Docker

Si prefieres no instalar PHP, PostgreSQL y Composer localmente, puedes usar Docker para ejecutar todo el proyecto en contenedores aislados.

### Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) 20.10+
- Docker Compose 2.0+
- Git

### Instalación Rápida

**1. Clonar el repositorio**

```bash
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api
```

**2. Levantar los contenedores**

```bash
docker compose up -d
```

Este comando construye y levanta 3 contenedores:
- `app` (PHP 8.2-FPM con Laravel)
- `web` (Nginx en puerto 8000)
- `postgres` (PostgreSQL 15)

**3. Configurar y migrar**

```bash
# Windows
Copy-Item .env.docker .env

# Linux/Mac
cp .env.docker .env

# Ejecutar migraciones
docker compose exec app php artisan migrate
```

**4. Probar la instalación**

```bash
docker compose exec app php artisan test
```

La API estará disponible en: `http://localhost:8000`

### Comandos Docker Útiles

**Ver logs:**
```bash
docker compose logs -f app
```

**Ejecutar comandos artisan:**
```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan tinker
```

**Reiniciar servicios:**
```bash
docker compose restart
```

**Detener contenedores:**
```bash
docker compose down
```

**Detener y eliminar datos:**
```bash
docker compose down -v
```

### Arquitectura Docker

```
┌─────────────────────────────────────────┐
│         Docker Host                      │
│                                          │
│  ┌────────────────────────────────────┐ │
│  │  Nginx (puerto 8000)               │ │
│  │       ↓                             │ │
│  │  PHP-FPM (Laravel 11)              │ │
│  │       ↓                             │ │
│  │  PostgreSQL (puerto 5432)          │ │
│  └────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

**Conexión a PostgreSQL desde herramientas externas:**
```
Host: localhost
Puerto: 5432
Usuario: postgres
Contraseña: root
Base de datos: orders_payments
```

### Documentación Completa de Docker

Para más detalles sobre la configuración de Docker:
- [DOCKER.md](DOCKER.md) - Guía completa de Docker

---

## Mejores Prácticas y Recomendaciones

### Para Desarrollo

**1. Ejecutar tests antes de commit**
```bash
php artisan test
```

**2. Verificar migraciones pendientes**
```bash
php artisan migrate:status
```

**3. Usar Tinker para explorar modelos**
```bash
php artisan tinker

>>> App\Models\Order::with('payments')->get()
>>> App\Enums\OrderStatus::cases()
```

### Para Producción

**1. Variables de entorno críticas**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
DB_CONNECTION=pgsql
DB_HOST=<production-host>
SESSION_DRIVER=database
PAYMENT_GATEWAY_URL=<production-gateway>
```

**2. Optimizaciones de Laravel**
```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer install --optimize-autoloader --no-dev
```

**3. SSL/TLS para API externa**
```php
// En PaymentGatewayService.php, activar verificación SSL
Http::timeout(10)
    ->withOptions(['verify' => true])
    ->post($url, $data);
```

**4. Monitoreo y logging**
```env
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Extensiones Futuras

**1. Autenticación API**
```php
// Laravel Sanctum para API tokens
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

**2. Rate Limiting**
```php
// En routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
});
```

**3. Múltiples Gateways de Pago**
```php
// Implementar StripeGateway, PayPalGateway, etc.
class StripeGateway implements PaymentGatewayInterface {
    public function processPayment(float $amount, int $orderId): array {
        // Integración con Stripe API
    }
}

// Cambiar implementación en AppServiceProvider
$this->app->bind(PaymentGatewayInterface::class, StripeGateway::class);
```

**4. Webhooks para notificaciones asíncronas**

POST /api/webhooks/payment - Recibir confirmaciones del gateway de pago

**5. Paginación para listados grandes**
```php
// En OrderController::index()
return OrderResource::collection(Order::paginate(20));
```

**6. Filtros y búsqueda**
```php
// GET /api/orders?status=paid&customer=Juan
Order::where('status', $request->status)
     ->where('customer_name', 'like', "%{$request->customer}%")
     ->get();
```

---

## Troubleshooting

### Problema: "Connection refused" al conectar a PostgreSQL

**Síntoma:**
```
SQLSTATE[08006] [7] Connection refused
```

**Solución:**
```bash
# Verificar que PostgreSQL está corriendo
sudo systemctl status postgresql  # Linux
brew services list  # Mac

# Iniciar PostgreSQL si está detenido
sudo systemctl start postgresql  # Linux
brew services start postgresql  # Mac
```

---

### Problema: Tests fallan con "Database does not exist"

**Síntoma:**
```
SQLSTATE[08006] Database does not exist
```

**Solución:**
```bash
# Crear la base de datos
psql -U postgres
CREATE DATABASE orders_payments;
\q

# Ejecutar migraciones
php artisan migrate
php artisan test
```

---

### Problema: Permisos en storage/ y bootstrap/cache/

**Síntoma:**
```
The stream or file "storage/logs/laravel.log" could not be opened
```

**Solución:**
```bash
# Dar permisos de escritura
chmod -R 775 storage bootstrap/cache

# En algunos casos puede ser necesario
sudo chown -R $USER:www-data storage bootstrap/cache
```

---

### Problema: Composer dependencies desactualizadas

**Síntoma:**
```
Class 'Illuminate\Foundation\Application' not found
```

**Solución:**
```bash
# Reinstalar dependencias
composer install

# O actualizar a últimas versiones
composer update
```

---

### Problema: Cambios en código no se reflejan

**Síntoma:**
El código editado no surte efecto en la aplicación.

**Solución:**
```bash
# Limpiar cachés de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reiniciar servidor
php artisan serve
```

---

### Problema: API retorna HTML en lugar de JSON

**Síntoma:**
```json
<!DOCTYPE html><html>...
```

**Causas posibles:**
1. **Header faltante:** Agregar `Accept: application/json`
2. **Error 500:** Revisar logs en `storage/logs/laravel.log`
3. **Ruta incorrecta:** Verificar que la URL incluya `/api/`

**Solución:**
```bash
# Agregar header Accept
curl -H "Accept: application/json" http://127.0.0.1:8000/api/orders

# Ver logs de errores
tail -f storage/logs/laravel.log
```

---

### Problema: "419 Page Expired" en Postman

**Síntoma:**
```json
{
  "message": "CSRF token mismatch"
}
```

**Solución:**
Las rutas API no requieren CSRF token. Verificar que:
1. La ruta esté en `routes/api.php` (no `routes/web.php`)
2. La URL incluya el prefijo `/api/`
3. No estés enviando cookies de sesión

---

## FAQ (Preguntas Frecuentes)

### ¿Cómo agrego más endpoints?

```bash
# 1. Crear controlador
php artisan make:controller Api/RefundController

# 2. Crear request de validación
php artisan make:request StoreRefundRequest

# 3. Crear resource para respuesta
php artisan make:resource RefundResource

# 4. Agregar ruta en routes/api.php
Route::post('/refunds', [RefundController::class, 'store']);

# 5. Crear test
php artisan make:test RefundTest
```

### ¿Cómo conecto a PostgreSQL con herramientas externas?

**Credenciales de conexión:**
```
Host: localhost (o 127.0.0.1)
Puerto: 5432
Usuario: postgres
Contraseña: tu_password
Base de datos: orders_payments
```

**Herramientas recomendadas:**
- DBeaver (universal)
- pgAdmin (específico PostgreSQL)
- TablePlus (macOS/Windows)

### ¿Cómo deploy a producción?

**Opción 1: Docker Compose en VPS**
```bash
# En servidor (DigitalOcean, AWS EC2, etc.)
git clone <repo>
docker compose -f docker-compose.prod.yml up -d
```

**Opción 2: Kubernetes**
```bash
# Crear manifests k8s
kubectl apply -f k8s/
```

**Opción 3: Laravel Forge/Vapor**
- Forge: VPS gestionado
- Vapor: Serverless en AWS

### ¿Cómo hacer backup de la base de datos?

```bash
# Backup manual
pg_dump -U postgres orders_payments > backup.sql

# Restore
psql -U postgres orders_payments < backup.sql

# Backup con timestamp
pg_dump -U postgres orders_payments > backup-$(date +%Y%m%d).sql
```

### ¿Cómo ver queries SQL ejecutadas?

```php
// En cualquier parte del código
DB::enableQueryLog();

// Después de ejecutar operaciones
dd(DB::getQueryLog());
```

O usar Laravel Telescope:
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
# Visitar http://localhost:8000/telescope
```

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
