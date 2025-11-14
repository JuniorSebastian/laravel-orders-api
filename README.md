# API REST - Gestión de Pedidos y Pagos

API REST construida con Laravel para gestionar pedidos y pagos, con integración a servicio externo de procesamiento de pagos.

## Características

- Crear pedidos con nombre del cliente y monto total
- Procesar pagos asociados a pedidos
- Integración con API externa (ReqRes.in) para validar pagos
- Actualización automática de estados de pedidos según resultado del pago
- Reintentos de pago para pedidos fallidos
- Tests funcionales completos

## Requisitos

- PHP 8.2+
- PostgreSQL
- Composer

## Instalación

1. Clonar el repositorio
```bash
git clone https://github.com/JuniorSebastian/laravel-orders-api.git
cd laravel-orders-api
```

2. Instalar dependencias
```bash
composer install
```

3. Configurar base de datos en `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=orders_payments
DB_USERNAME=postgres
DB_PASSWORD=root

PAYMENT_GATEWAY_URL=https://reqres.in/api
PAYMENT_GATEWAY_API_KEY=reqres-free-v1
```

4. Generar key y ejecutar migraciones
```bash
php artisan key:generate
php artisan migrate
```

5. Ejecutar tests
```bash
php artisan test
```

## Endpoints API

### Pedidos

**Listar pedidos**
```
GET /api/orders
```

**Crear pedido**
```
POST /api/orders
Content-Type: application/json

{
    "customer_name": "Juan Perez",
    "total_amount": 100.50
}
```

**Ver pedido**
```
GET /api/orders/{id}
```

### Pagos

**Procesar pago**
```
POST /api/payments
Content-Type: application/json

{
    "order_id": 1
}
```

## Estructura del Proyecto

- **Models**: Order y Payment con relaciones definidas
- **Services**: 
  - `PaymentGatewayService`: Comunicación con API externa
  - `OrderPaymentService`: Lógica de procesamiento de pagos
- **Controllers**: OrderController y PaymentController
- **Tests**: Tests funcionales para pedidos y pagos

## Decisiones Técnicas

### API Externa
Se utiliza ReqRes.in como servicio de procesamiento de pagos simulado. El endpoint POST /users retorna 201 indicando éxito en la transacción.

### Estados de Pedidos
- `pending`: Estado inicial al crear un pedido
- `paid`: Pago procesado exitosamente
- `failed`: Pago fallido, permite reintentos

### Transacciones
El procesamiento de pagos se ejecuta dentro de una transacción de base de datos para garantizar consistencia entre el registro del pago y la actualización del estado del pedido.

### Tests
Se implementaron tests funcionales que cubren:
- Creación y listado de pedidos
- Procesamiento exitoso de pagos
- Manejo de pagos fallidos
- Reintentos de pago
- Validaciones

## Licencia

MIT
