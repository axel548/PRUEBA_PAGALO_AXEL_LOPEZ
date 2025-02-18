# Documentación del Proyecto

## Base de Datos

### Diagrama ER
![Diagram_database_from_mysql](https://github.com/user-attachments/assets/ba25c1cf-9529-4bc1-9710-2e96cb245de3)

## Configuración del Entorno

### Requisitos

- Laravel 11

### Pasos para la Configuración

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/axel548/PRUEBA_PAGALO_AXEL_LOPEZ.git
   cd .\PRUEBA_PAGALO_AXEL_LOPEZ\
   ```
   
2. Limpiar y preparar el caché:

   ```bash
   composer clear-cache
   composer install
   composer dump-autoload
   ```
   
3. Copiar el archivo .env de ejemplo:

   ```bash
   cp .env.example .env
   ```
   
4. Configuración en el archivo .env:

   ```bash
   # JOB CONFIGURATION
    QUEUE_CONNECTION=database
    
    # DB CONFIGURATION
    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=api_ecommerce
    DB_USERNAME=root
    DB_PASSWORD=
    
    # MAILTRAP CONFIGURATION
    MAIL_MAILER=smtp
    MAIL_HOST=
    MAIL_PORT=
    MAIL_USERNAME=
    MAIL_PASSWORD=
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=""
    MAIL_FROM_NAME="E-commerce"
   ```

5. Generar las claves necesarias:
   ```bash
    php artisan key:generate
    php artisan jwt:secret
   ```
   
6. Ejecutar las migraciones y seeders:
   ```bash
    php artisan migrate --seed
   ```

7. Crear la carpeta reports en storage/app:
   ```bash
    mkdir storage\app\reports
   ```

8. Ejecutar el servidor:
   ```bash
    php artisan serve
   ```

## Testing

Para ejecutar las pruebas de la API, puedes usar el siguiente comando:

   ```bash
    php artisan test --filter PedidoApiTest
   ```
![Tests](https://github.com/user-attachments/assets/7868d82e-e99c-4d1b-9155-3dd51a366f15)


## Uso de la API y Endpoints

### Usar la colección de Postman:
- **[Colección de Postman](https://documenter.getpostman.com/view/10653114/2sAYXFidJ7)**

### Registrar un nuevo usuario:
- **Realiza una solicitud `POST` a `/api/register`**.

### Gestionar Pedidos:
- **Listar Pedidos**: `GET` a `/api/pedidos`
- **Filtrar Pedidos**: `GET` a `/api/pedidos/filter`
- **Crear Pedido**: `POST` a `/api/pedidos`
- **Cancelar Pedido**: `POST` a `/api/pedidos/cancel`
- **Actualizar Pedido**: `PATCH` a `/api/pedidos/update`

### Generar Reporte de Pedidos:
- **Realiza una solicitud `GET` a `/api/pedidos/report`**.











   
