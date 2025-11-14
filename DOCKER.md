# Docker - Laravel Orders API

## Comandos Docker

### Levantar el proyecto
```bash
docker-compose up -d
```

### Ver logs
```bash
docker-compose logs -f
```

### Ejecutar migraciones
```bash
docker-compose exec app php artisan migrate
```

### Ejecutar tests
```bash
docker-compose exec app php artisan test
```

### Entrar al contenedor de la aplicación
```bash
docker-compose exec app bash
```

### Entrar a PostgreSQL
```bash
docker-compose exec postgres psql -U postgres -d orders_payments
```

### Detener contenedores
```bash
docker-compose down
```

### Detener y eliminar volúmenes (⚠️ Borra la base de datos)
```bash
docker-compose down -v
```

### Reconstruir imágenes
```bash
docker-compose up -d --build
```

## Primera vez (Setup inicial)

1. Levantar contenedores:
```bash
docker-compose up -d
```

2. Copiar .env para Docker:
```bash
cp .env.docker .env
```

3. Ejecutar migraciones:
```bash
docker-compose exec app php artisan migrate
```

4. Acceder a la aplicación:
```
http://localhost:8000
```

## Verificar que todo funciona

### Test de la API
```bash
curl http://localhost:8000/api/orders
```

### Ver estado de contenedores
```bash
docker-compose ps
```

## Estructura Docker

- **app**: Contenedor PHP-FPM con Laravel
- **web**: Contenedor Nginx (puerto 8000)
- **postgres**: Contenedor PostgreSQL (puerto 5432)
- **Volumen**: `postgres_data` para persistencia de datos

## Troubleshooting

### Permisos
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Limpiar cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

### Ver logs de Nginx
```bash
docker-compose logs web
```

### Ver logs de PHP
```bash
docker-compose logs app
```
