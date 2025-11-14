# 🚀 API REST - Pedidos y Pagos

API REST robusta para gestión de pedidos y procesamiento de pagos con integración a gateway externo. Construida con **Laravel 11**, aplicando **principios SOLID** y **Clean Architecture**.

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-336791?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)](https://www.docker.com/)
[![Tests](https://img.shields.io/badge/Tests-11%20passed-00C853)](https://phpunit.de/)

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Demo Rápida](#-demo-rápida-5-minutos)
- [Instalación](#instalación)
  - [Con Docker (Recomendado)](#opción-1-con-docker-recomendado-)
  - [Sin Docker](#opción-2-instalación-local)
- [API Endpoints](#api-endpoints)
- [Arquitectura](#arquitectura)
- [Testing](#testing)
- [Stack Tecnológico](#stack-tecnológico)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq-preguntas-frecuentes)

---

## ✨ Características

✅ **CRUD Completo de Pedidos** - Crear, listar y consultar órdenes  
✅ **Procesamiento de Pagos** - Integración con gateway externo (ReqRes.in)  
✅ **Gestión de Estados** - Máquina de estados (pending → paid/failed)  
✅ **Reintentos de Pago** - Pedidos fallidos pueden reintentar  
✅ **SOLID Principles** - Código mantenible y extensible  
✅ **Clean Architecture** - Separación de capas (Controller → Service → Model)  
✅ **Docker Ready** - Entorno reproducible con 3 contenedores  
✅ **Testing Completo** - 11 tests, 48 assertions, 100% endpoints cubiertos  
✅ **API Documentation** - Colección Postman incluida  
✅ **Type Safety** - PHP 8.2+ Enums tipados  

---

## ⚡ Demo Rápida (5 minutos)

```bash
# 1. Clonar y levantar con Docker
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api
docker compose up -d

# 2. Configurar y migrar
cp .env.docker .env
docker compose exec app php artisan migrate

# 3. Probar con curl
curl http://localhost:8000/api/orders
# {"data":[]}

# 4. Crear una orden
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{"customer_name":"Juan Pérez","total_amount":150.50}'

# 5. Procesar pago
curl -X POST http://localhost:8000/api/payments \
  -H "Content-Type: application/json" \
  -d '{"order_id":1}'
```

**Resultado:** Orden creada y pago procesado exitosamente ✅

Importa la colección de Postman desde `/postman/Laravel_Orders_API.postman_collection.json` para probar todos los endpoints.

---

## 📦 Requisitos

### Con Docker (Recomendado)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) 20.10+
- Docker Compose 2.0+
- Git

### Sin Docker
| Componente | Versión |
|------------|---------|
| PHP | 8.2+ |
| PostgreSQL | 13+ |
| Composer | 2.0+ |
| Extensiones PHP | `pdo_pgsql`, `mbstring`, `curl` |

## Instalación

### Opción 1: Con Docker (Recomendado) 🐳

Docker proporciona un entorno completamente aislado y reproducible. **No necesitas instalar PHP, PostgreSQL ni Composer en tu máquina.**

#### Requisitos Previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y corriendo
- [Docker Compose](https://docs.docker.com/compose/) (incluido en Docker Desktop)
- Git

#### Guía de Instalación

**1. Clonar el repositorio**

```bash
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api
```

**2. Levantar los contenedores**

```bash
docker compose up -d
```

Este comando:
- Construye la imagen PHP 8.2-FPM con todas las extensiones necesarias
- Levanta 3 contenedores: `app` (Laravel), `web` (Nginx), `postgres` (PostgreSQL)
- Crea la red `laravel` para comunicación entre contenedores
- Crea el volumen persistente `postgres_data` para la base de datos

**Salida esperada:**
```
[+] Running 4/4
 ✔ Network laravel-orders-payments_laravel  Created
 ✔ Container laravel-postgres               Started
 ✔ Container laravel-app                    Started
 ✔ Container laravel-nginx                  Started
```

**3. Verificar que los contenedores están corriendo**

```bash
docker compose ps
```

**Salida esperada:**
```
NAME              IMAGE                    STATUS    PORTS
laravel-app       laravel-orders-api-app   Up        9000/tcp
laravel-nginx     nginx:alpine             Up        0.0.0.0:8000->80/tcp
laravel-postgres  postgres:15-alpine       Up        0.0.0.0:5432->5432/tcp
```

**4. Copiar archivo de configuración**

```bash
# Windows (PowerShell)
Copy-Item .env.docker .env

# Linux/Mac
cp .env.docker .env
```

El archivo `.env.docker` ya está configurado para el entorno Docker:
- `DB_HOST=postgres` (nombre del servicio en docker-compose)
- `SESSION_DRIVER=cookie` (para evitar dependencia de tabla sessions)
- Todas las credenciales pre-configuradas

**5. Ejecutar migraciones de base de datos**

```bash
docker compose exec app php artisan migrate
```

Esto crea las tablas: `users`, `cache`, `jobs`, `orders`, `payments`

**6. Verificar instalación con tests**

```bash
docker compose exec app php artisan test
```

**Resultado esperado:**
```
   PASS  Tests\Feature\OrderTest
  ✓ can create order
  ✓ can list orders
  ✓ can show order with payments
  ✓ order validation fails without required fields

   PASS  Tests\Feature\PaymentTest
  ✓ successful payment updates order to paid
  ✓ failed payment updates order to failed
  ✓ failed order can receive new payment attempt
  ✓ paid order cannot receive new payment
  ✓ payment validation fails without order id

  Tests:  11 passed (48 assertions)
  Duration: 7.64s
```

#### Acceso a la Aplicación

| Servicio | URL/Puerto | Descripción |
|----------|------------|-------------|
| **API REST** | http://localhost:8000 | Endpoints de la API |
| **PostgreSQL** | localhost:5432 | Base de datos |
| **PgAdmin/DBeaver** | Host: localhost<br>Puerto: 5432<br>Usuario: `postgres`<br>Password: `root`<br>DB: `orders_payments` | Conexión externa |

#### Comandos Docker Útiles

**Ver logs en tiempo real:**
```bash
# Todos los servicios
docker compose logs -f

# Solo Laravel
docker compose logs -f app

# Solo Nginx
docker compose logs -f web

# Solo PostgreSQL
docker compose logs -f postgres
```

**Entrar a un contenedor (bash/shell):**
```bash
# Contenedor Laravel (para ejecutar comandos artisan)
docker compose exec app bash

# Dentro del contenedor puedes ejecutar:
php artisan route:list
php artisan migrate:status
php artisan tinker
```

**Ejecutar comandos sin entrar al contenedor:**
```bash
# Listar rutas
docker compose exec app php artisan route:list

# Crear migración
docker compose exec app php artisan make:migration create_example_table

# Ejecutar tests
docker compose exec app php artisan test

# Refrescar base de datos y seeders
docker compose exec app php artisan migrate:fresh --seed
```

**Reiniciar servicios:**
```bash
# Reiniciar todos los contenedores
docker compose restart

# Reiniciar solo Laravel
docker compose restart app

# Reiniciar solo Nginx
docker compose restart web
```

**Detener contenedores (sin borrar volúmenes):**
```bash
docker compose stop
```

**Levantar contenedores detenidos:**
```bash
docker compose start
```

**Detener y eliminar contenedores:**
```bash
docker compose down
```

**Detener, eliminar contenedores y volúmenes (⚠️ BORRA LA BASE DE DATOS):**
```bash
docker compose down -v
```

**Reconstruir imágenes (después de cambios en Dockerfile):**
```bash
docker compose up -d --build
```

**Ver estado de recursos Docker:**
```bash
# Contenedores en ejecución
docker ps

# Todos los contenedores (incluidos detenidos)
docker ps -a

# Imágenes construidas
docker images

# Volúmenes creados
docker volume ls

# Redes creadas
docker network ls
```

#### Arquitectura Docker

```
┌─────────────────────────────────────────────────────────┐
│                    Docker Host                          │
│                                                          │
│  ┌──────────────────────────────────────────────────┐  │
│  │              Red: laravel (bridge)                │  │
│  │                                                    │  │
│  │  ┌───────────────┐   ┌───────────────┐          │  │
│  │  │   Nginx       │   │   PHP-FPM     │          │  │
│  │  │   (web)       │   │   (app)       │          │  │
│  │  │               │   │               │          │  │
│  │  │  Escucha:80  │◄──┤ Laravel 11    │          │  │
│  │  │  Sirve:      │   │ PHP 8.2       │          │  │
│  │  │  /public     │   │               │          │  │
│  │  └───────────────┘   └───────┬───────┘          │  │
│  │         │                    │                   │  │
│  │         │                    │                   │  │
│  │         │                    ▼                   │  │
│  │         │            ┌───────────────┐           │  │
│  │         │            │  PostgreSQL   │           │  │
│  │         │            │  (postgres)   │           │  │
│  │         │            │               │           │  │
│  │         │            │  DB: orders_  │           │  │
│  │         │            │      payments │           │  │
│  │         │            │  Puerto: 5432 │           │  │
│  │         │            └───────────────┘           │  │
│  │         │                    │                   │  │
│  └─────────┼────────────────────┼───────────────────┘  │
│            │                    │                       │
│            │                    │                       │
│     Puerto 8000           postgres_data                │
│         ▲                     (volumen)                │
└─────────┼───────────────────────────────────────────────┘
          │
     localhost:8000
```

**Descripción de servicios:**

| Servicio | Imagen | Rol | Puertos |
|----------|--------|-----|---------|
| **app** | PHP 8.2-FPM | Ejecuta código Laravel, procesa requests PHP | 9000 (interno) |
| **web** | Nginx Alpine | Servidor HTTP, sirve archivos estáticos, proxy a PHP-FPM | 8000 → 80 |
| **postgres** | PostgreSQL 15 Alpine | Base de datos relacional | 5432 → 5432 |

#### Solución de Problemas Docker

**Problema: Puerto 8000 en uso**
```bash
# Windows - Ver qué está usando el puerto
netstat -ano | findstr :8000

# Matar proceso
taskkill /PID <PID> /F

# O cambiar puerto en docker-compose.yml
ports:
  - "8080:80"  # Usa puerto 8080 en lugar de 8000
```

**Problema: Permisos en Linux/Mac**
```bash
# Dar permisos a storage y bootstrap/cache
docker compose exec app chmod -R 777 storage bootstrap/cache
```

**Problema: Migraciones fallan**
```bash
# Verificar conexión a PostgreSQL
docker compose exec postgres psql -U postgres -d orders_payments -c "\dt"

# Refrescar migraciones
docker compose exec app php artisan migrate:fresh
```

**Problema: Composer dependencies desactualizadas**
```bash
# Instalar dependencias dentro del contenedor
docker compose exec app composer install

# O actualizar
docker compose exec app composer update
```

**Problema: Limpiar todo y empezar de nuevo**
```bash
# Detener y eliminar todo
docker compose down -v

# Eliminar imágenes construidas
docker rmi laravel-orders-api-app

# Levantar de nuevo
docker compose up -d --build
docker compose exec app php artisan migrate
```

#### Ver Documentación Completa

Para más detalles sobre la arquitectura Docker, configuración avanzada y troubleshooting:
- [DOCKER.md](DOCKER.md) - Documentación completa de Docker

---

### Opción 2: Instalación Local

**Requisitos:**
- PHP 8.2+
- PostgreSQL 13+
- Composer 2.0+

**Pasos:**

```bash
# 1. Clonar repositorio
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api

# 2. Instalar dependencias
composer install

# 3. Configurar .env
cp .env.example .env
# Editar .env con tus credenciales de PostgreSQL

# 4. Crear base de datos
psql -U postgres
CREATE DATABASE orders_payments;
\q

# 5. Generar key y ejecutar migraciones
php artisan key:generate
php artisan migrate

# 6. Iniciar servidor
php artisan serve
# http://127.0.0.1:8000

# 7. Ejecutar tests
php artisan test
```

**Configuración .env para instalación local:**

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
- ✅ **GET** Lista de Órdenes - `/api/orders`
- ✅ **POST** Crear Orden - `/api/orders`
- ✅ **GET** Ver Orden - `/api/orders/{id}`
- ✅ **POST** Procesar Pago - `/api/payments`

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

### 📋 GET /api/orders

Obtiene la lista completa de pedidos con sus pagos asociados.

**Request:**
```bash
# Con Docker
curl http://localhost:8000/api/orders

# Sin Docker
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

### ➕ POST /api/orders

Crea un nuevo pedido en estado `pending`.

**Request:**
```bash
curl -X POST http://localhost:8000/api/orders \
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

### 🔍 GET /api/orders/{id}

Obtiene los detalles de un pedido específico con todos sus intentos de pago.

**Request:**
```bash
curl http://localhost:8000/api/orders/1
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

### 💳 POST /api/payments

Procesa un pago para un pedido. Integra con API externa para validación.

**Request:**
```bash
curl -X POST http://localhost:8000/api/payments \
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
- `pending` → `paid`: Cuando el primer pago es exitoso
- `pending` → `failed`: Cuando el primer pago falla
- `failed` → `paid`: Cuando un reintento de pago es exitoso
- `failed` → `failed`: Cuando un reintento de pago falla (actualiza contador)
- `paid` → **NINGUNO**: Estado terminal, no permite más cambios

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

**Con Docker (recomendado):**
```bash
docker compose exec app php artisan test
```

**Sin Docker:**
```bash
php artisan test
```

**Resultado esperado:**
```
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

   PASS  Tests\Feature\OrderTest
  ✓ can create order
  ✓ can list orders
  ✓ can show order with payments
  ✓ order validation fails without required fields

   PASS  Tests\Feature\PaymentTest
  ✓ successful payment updates order to paid
  ✓ failed payment updates order to failed
  ✓ failed order can receive new payment attempt
  ✓ paid order cannot receive new payment
  ✓ payment validation fails without order id

  Tests:  11 passed (48 assertions)
  Duration: 7.64s (Docker) / 1.63s (local)
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
- Stripe: `PaymentGatewayInterface` → `StripeGateway`
- PayPal: `PaymentGatewayInterface` → `PayPalGateway`
- MercadoPago: `PaymentGatewayInterface` → `MercadoPagoGateway`

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

## Mejores Prácticas y Recomendaciones

### Para Desarrollo

**1. Usar Docker para desarrollo local**
```bash
# Siempre trabajar con Docker para mantener consistencia
docker compose up -d
docker compose exec app php artisan migrate
```

**2. Ejecutar tests antes de commit**
```bash
docker compose exec app php artisan test
```

**3. Verificar migraciones pendientes**
```bash
docker compose exec app php artisan migrate:status
```

**4. Usar Tinker para explorar modelos**
```bash
docker compose exec app php artisan tinker

>>> App\Models\Order::with('payments')->get()
>>> App\Enums\OrderStatus::cases()
```

### Para Producción

**1. Variables de entorno críticas**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # Generar con php artisan key:generate
DB_CONNECTION=pgsql
DB_HOST=<production-host>
SESSION_DRIVER=database # Cambiar a database o redis
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
    ->withOptions(['verify' => true]) // Activar en producción
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
```php
// POST /api/webhooks/payment
// Recibir confirmaciones del gateway de pago
```

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
SQLSTATE[08006] [7] could not translate host name "postgres" to address
```

**Solución:**
```bash
# Verificar que el contenedor postgres está corriendo
docker compose ps

# Si está detenido, levantar servicios
docker compose up -d

# Verificar logs de PostgreSQL
docker compose logs postgres
```

---

### Problema: Tests fallan con "Database does not exist"

**Síntoma:**
```
SQLSTATE[08006] Connection refused
```

**Solución:**
```bash
# Ejecutar migraciones en entorno de testing
docker compose exec app php artisan migrate --env=testing

# O refrescar migraciones antes de tests
docker compose exec app php artisan migrate:fresh
docker compose exec app php artisan test
```

---

### Problema: "Port 8000 already in use"

**Síntoma:**
```
Error response from daemon: Ports are not available: exposing port TCP 0.0.0.0:8000
```

**Solución (Windows):**
```powershell
# Ver qué proceso usa el puerto
netstat -ano | findstr :8000

# Matar el proceso
taskkill /PID <PID> /F

# O cambiar puerto en docker-compose.yml
```

**Solución alternativa:**
```yaml
# docker-compose.yml
web:
  ports:
    - "8080:80"  # Cambiar a puerto 8080
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
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

---

### Problema: Composer dependencies desactualizadas

**Síntoma:**
```
Class 'Illuminate\Foundation\Application' not found
```

**Solución:**
```bash
# Reinstalar dependencias dentro del contenedor
docker compose exec app composer install

# O actualizar a últimas versiones
docker compose exec app composer update
```

---

### Problema: Cambios en código no se reflejan

**Síntoma:**
El código editado no surte efecto en la aplicación.

**Solución:**
```bash
# Limpiar cachés de Laravel
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Reiniciar contenedores
docker compose restart app
```

---

### Problema: API retorna HTML en lugar de JSON

**Síntoma:**
```json
<!DOCTYPE html><html>...
```

**Causas posibles:**
1. **Header faltante:** Agregar `Accept: application/json`
2. **Error 500:** Revisar logs con `docker compose logs app`
3. **Ruta incorrecta:** Verificar que la URL incluya `/api/`

**Solución:**
```bash
# Agregar header Accept
curl -H "Accept: application/json" http://localhost:8000/api/orders

# Ver logs de errores
docker compose logs app -f
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
docker compose exec app php artisan make:controller Api/RefundController

# 2. Crear request de validación
docker compose exec app php artisan make:request StoreRefundRequest

# 3. Crear resource para respuesta
docker compose exec app php artisan make:resource RefundResource

# 4. Agregar ruta en routes/api.php
Route::post('/refunds', [RefundController::class, 'store']);

# 5. Crear test
docker compose exec app php artisan make:test RefundTest
```

### ¿Cómo conecto a PostgreSQL desde fuera de Docker?

**Credenciales (desde .env.docker):**
```
Host: localhost
Puerto: 5432
Usuario: postgres
Contraseña: root
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
docker compose exec postgres pg_dump -U postgres orders_payments > backup.sql

# Restore
docker compose exec -T postgres psql -U postgres orders_payments < backup.sql

# Backup con timestamp
docker compose exec postgres pg_dump -U postgres orders_payments > backup-$(date +%Y%m%d).sql
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
