# Frontend - SaaS Taller (Vue 3 + Vite)

Aplicacion frontend del sistema de taller, conectada a API Laravel.

## Estado actual
- Proyecto funcional con login obligatorio.
- Navegacion por modulos: Ordenes, Clientes, Vehiculos, Mecanicos.
- Integracion completa con backend por `axios`.
- UI renovada para login y pantalla de ordenes.

## Stack
- Vue 3
- Vue Router 4
- Pinia (base instalada)
- Axios
- Vite

## Estructura principal
- `src/main.js`: bootstrap de Vue.
- `src/App.vue`: shell principal con sidebar y logout.
- `src/router/index.js`: rutas y guard de autenticacion.
- `src/auth.js`: manejo local de token y usuario.
- `src/api/http.js`: cliente axios + interceptores auth.
- `src/views/auth/LoginView.vue`: pantalla de login.
- `src/views/customers/CustomersView.vue`: CRUD basico cliente.
- `src/views/vehicles/VehiclesView.vue`: CRUD basico vehiculo.
- `src/views/mechanics/MechanicsView.vue`: CRUD basico mecanico.
- `src/views/orders/OrdersListView.vue`: creacion/listado/estado de ordenes.
- `src/assets/styles/main.css`: estilos globales.
- `Images/image.png`: logo sidebar.
- `Images/login_imagen.png`: imagen del login.

## Log detallado de cambios

### 1) Base inicial frontend
- Se creo proyecto Vue con Vite.
- Se configuro router con ruta principal.
- Se creo pagina inicial de ordenes.

### 2) Integracion con API Laravel
- Se agrego cliente HTTP en `src/api/http.js`.
- Se definio `VITE_API_URL` opcional con fallback a:
  - `http://127.0.0.1:8000/api`
- Se conectaron vistas a endpoints REST:
  - `clientes`
  - `vehiculos`
  - `mecanicos`
  - `ordenes-servicio`

### 3) Autenticacion frontend
- Se implemento login obligatorio antes de acceder al dashboard.
- Se agrego almacenamiento de token/usuario en `localStorage`.
- Se agregaron interceptores:
  - request: adjunta `Authorization: Bearer <token>`
  - response: si `401`, limpia sesion y redirige a `/login`
- Se agrego guard de rutas:
  - rutas privadas protegidas
  - `/login` publica
- Se agrego logout desde sidebar.

### 4) Vistas funcionales
- `CustomersView.vue`:
  - formulario de registro de cliente
  - tabla con listado de clientes
- `VehiclesView.vue`:
  - formulario de registro de vehiculo
  - selector de cliente
  - tabla de vehiculos con cliente asociado
  - marca en lista fija:
    - toyota, honda, mercedes, mazda, kia, hyundai, ford, suzuki
- `MechanicsView.vue`:
  - formulario de registro de mecanico
  - tabla de mecanicos
- `OrdersListView.vue`:
  - formulario de creacion de orden
  - listado de ordenes
  - cambio de estado por fila

### 5) Rediseño de Ordenes (UX/UI)
- Se simplifico la pantalla para evitar saturacion.
- Se reorganizo el formulario en tarjeta limpia.
- Se agregaron toggles:
  - `Cliente nuevo`
  - `Vehiculo nuevo`
- Flujo inteligente:
  - si eliges cliente existente, se autoselecciona su primer vehiculo
  - se muestran datos cortos del vehiculo seleccionado
  - si no hay vehiculo del cliente, se activa flujo de vehiculo nuevo
- Secciones avanzadas pasaron a opcionales:
  - auxiliares dentro de `details`
- Formato de estados legible en tabla:
  - `en_reparacion` -> `En Reparacion`
  - `esperando_piezas` -> `Esperando Piezas`

### 6) Mejoras visuales globales
- Sidebar renovado con identidad visual.
- Logo del taller encima del titulo `Taller OS`.
- Login rediseñado con layout de dos columnas:
  - formulario a la izquierda
  - imagen `login_imagen` a la derecha
- Inputs del login en columna vertical clasica.
- Ajustes responsive para mobile.

## Credenciales de acceso (demo)
- Email: `gutierrezrodrigo1709@gmail.com`
- Password: `Ingeniero1709!`

## Requisitos para correr
- Backend Laravel activo en `http://127.0.0.1:8000`
- API MySQL operativa
- Node.js y npm instalados

## Ejecutar en desarrollo
```powershell
cd frontend
npm install
npm run dev
```

URL frontend:
- `http://127.0.0.1:5173`

## Build de produccion
```powershell
cd frontend
npm run build
```

## Variables de entorno
Crear `frontend/.env` si deseas cambiar API:
```env
VITE_API_URL=http://127.0.0.1:8000/api
```

## Flujo funcional validado
1. Login.
2. Crear/consultar clientes.
3. Crear/consultar vehiculos.
4. Crear/consultar mecanicos.
5. Crear orden con cliente/vehiculo existente o nuevo.
6. Actualizar estado de orden desde el listado.

## Nota de continuidad
Siguiente mejora recomendada:
1. Pantalla detalle de orden con timeline de actualizaciones, pagos y evidencia (imagenes).
