# SaaS Taller - Ambiente local

## Release Log - MVP Taller (Backend + Frontend + Auth + Demo Data)

Titulo sugerido para commit:
`feat: implementar MVP de taller con auth, ordenes, UI renovada y datos demo`

### Resumen de cambios
- Se construyo la base funcional del sistema del taller de punta a punta.
- Se dejo operativo el flujo principal: login -> gestion de clientes/vehiculos/mecanicos -> creacion y seguimiento de ordenes.
- Se integro semilla de datos demo para pruebas rapidas.

### Backend (Laravel)
- Se creo y configuro API REST para:
  - `clientes`
  - `vehiculos`
  - `mecanicos`
  - `ordenes-servicio`
  - actualizacion de estado de orden
- Se implementaron migraciones y relaciones del dominio:
  - clientes, vehiculos, mecanicos
  - ordenes_servicio, orden_mecanicos
  - actualizaciones_orden, pagos
- Se habilito autenticacion por token en API:
  - endpoint de login
  - endpoint de sesion actual (`me`)
  - endpoint de logout
  - middleware de proteccion para rutas privadas
- Se agrego usuario de acceso inicial en seeder:
  - correo: `gutierrezrodrigo1709@gmail.com`
  - clave: `Ingeniero1709!`
- Se mejoro creacion de orden para soportar:
  - cliente existente
  - cliente nuevo en el mismo flujo
  - vehiculo existente
  - vehiculo nuevo en el mismo flujo

### Frontend (Vue)
- Se construyo layout principal con navegacion lateral.
- Se implementaron vistas funcionales:
  - Login
  - Clientes
  - Vehiculos
  - Mecanicos
  - Ordenes
- Se conecto cliente HTTP con `axios` hacia la API.
- Se agrego guard de rutas para login obligatorio.
- Se guarda token y usuario en `localStorage`.
- Se renovo la pantalla de ordenes para hacerla mas limpia:
  - menos saturada visualmente
  - secciones opcionales para cliente/vehiculo nuevo
  - autoseleccion de vehiculo al elegir cliente
  - visualizacion de datos rapidos del vehiculo seleccionado
  - formato legible de estados (`en_reparacion` -> `En Reparacion`)
- Se incorporaron assets visuales:
  - logo en sidebar (`image.png`)
  - ilustracion integrada en login (`login_imagen.png`)
  - inputs del login en formato vertical clasico

### Datos demo
- Se agrego `DemoWorkshopSeeder` para cargar:
  - 15 clientes random
  - 5 mecanicos random
  - vehiculos random por cliente (toyota, honda, mercedes, mazda, kia, hyundai, ford, suzuki)
  - ordenes por cliente con asignacion de mecanico principal y auxiliares
  - actualizacion inicial de estado y abono de pago por orden

### Operacion local validada
- Backend y frontend compilando y ejecutando localmente.
- Migraciones aplicadas en MySQL.
- Seeders ejecutados correctamente.

Stack configurado:
- Backend: Laravel 12 + PHP 8.2
- Frontend: Vue 3 + Vite
- Base de datos: MySQL (XAMPP)
- Administrador DB: phpMyAdmin (XAMPP)

## Estructura
- `backend/` API Laravel
- `frontend/` App Vue

## Estado actual
- Laravel instalado y funcional en `backend/`
- `.env` de Laravel configurado para MySQL local
- Base de datos `saas_taller` creada
- Migraciones base ejecutadas en MySQL
- Vue configurado e instalado en `frontend/`
- Build de Vue validado correctamente

## Requisitos
- XAMPP instalado en `C:\xampp`
- Node.js 18+
- Composer 2+

## Iniciar servicios XAMPP
En PowerShell:

```powershell
Start-Process -FilePath 'C:\xampp\mysql_start.bat'
Start-Process -FilePath 'C:\xampp\apache_start.bat'
```

phpMyAdmin:
- http://localhost/phpmyadmin

## Levantar backend (Laravel)

```powershell
cd backend
php artisan serve
```

API local:
- http://127.0.0.1:8000

## Levantar frontend (Vue)
En otra terminal:

```powershell
cd frontend
npm run dev
```

Frontend local:
- http://127.0.0.1:5173

## Credenciales DB usadas por Laravel
Archivo: `backend/.env`

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=saas_taller`
- `DB_USERNAME=root`
- `DB_PASSWORD=` (vacia por defecto en XAMPP)

## Comandos utiles
Migrar:

```powershell
cd backend
php artisan migrate
```

Crear migracion:

```powershell
cd backend
php artisan make:migration create_clientes_table
```

Crear controlador API:

```powershell
cd backend
php artisan make:controller Api/ClienteController --api
```
