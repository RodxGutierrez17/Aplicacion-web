# SaaS Taller - Ambiente local

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
