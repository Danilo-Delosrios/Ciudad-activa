# 🏙️ Ciudad Activa

Plataforma digital para reportar y gestionar problemas urbanos en tiempo real.

## 📋 Descripción

Ciudad Activa es una aplicación web que permite a los ciudadanos reportar problemas urbanos como baches, basura acumulada, infraestructura dañada, inseguridad, entre otros. Los reportes son gestionados por las autoridades competentes para mejorar la calidad de vida en la ciudad.

## 🚀 Características

- ✅ Registro e inicio de sesión de usuarios
- ✅ Creación y gestión de reportes
- ✅ Dashboard con estadísticas
- ✅ Categorización de reportes
- ✅ Visualización de perfil de usuario
- ✅ Estado de seguimiento de reportes
- ✅ Carga de imágenes
- ✅ Historial de reportes

## 🛠️ Requisitos Técnicos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con soporte para .htaccess
- Navegador web moderno

## 📁 Estructura del Proyecto

```
ciudad-activa/
├── css/
│   ├── estilos.css           # Estilos generales
│   ├── auth.css              # Estilos de autenticación
│   ├── dashboard.css         # Estilos del dashboard
│   ├── reportes.css          # Estilos de reportes
│   └── perfil.css            # Estilos del perfil
│
├── includes/
│   ├── conexion.php          # Conexión a MySQL
│   ├── header.php            # Header reutilizable
│   ├── footer.php            # Footer reutilizable
│   └── sidebar.php           # Menú lateral
│
├── auth/
│   ├── login.php             # Formulario de login
│   ├── registro.php          # Formulario de registro
│   ├── recuperar.php         # Recuperar contraseña
│   ├── procesar_login.php    # Procesar login
│   └── procesar_registro.php # Procesar registro
│
├── dashboard/
│   └── dashboard.php         # Panel principal
│
├── reportes/
│   ├── crear.php             # Crear reporte
│   ├── guardar_reporte.php   # Guardar reporte
│   └── mis_reportes.php      # Lista de reportes
│
├── perfil/
│   └── perfil.php            # Perfil del usuario
│
├── database/
│   ├── database.sql          # Script de creación
│   └── datos_prueba.sql      # Datos de prueba
│
├── index.php                 # Página de inicio
├── logout.php                # Cerrar sesión
└── README.md                 # Este archivo
```

## 🔧 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/Danilo-Delosrios/ciudad-activa.git
cd ciudad-activa
```

### 2. Configurar la base de datos

```bash
# Crear la base de datos
mysql -u root -p < database/database.sql

# (Opcional) Cargar datos de prueba
mysql -u root -p ciudad_activa < database/datos_prueba.sql
```

### 3. Actualizar credenciales

Editar `includes/conexion.php` con tus credenciales de MySQL:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'ciudad_activa');
```

### 4. Crear directorio de uploads

```bash
mkdir -p uploads/reportes
chmod 755 uploads/reportes
```

### 5. Iniciar el servidor

```bash
php -S localhost:8000
```

Acceder a: `http://localhost:8000`

## 👤 Usuarios de Prueba

Por defecto, los siguientes usuarios están disponibles (contraseña: `password123`):

- `juan@example.com`
- `maria@example.com`
- `carlos@example.com`

## 📝 Guía de Uso

### Crear una Cuenta

1. Click en "¿No tienes cuenta? Regístrate"
2. Completar los datos requeridos
3. Click en "Registrarse"

### Crear un Reporte

1. Acceder al Dashboard
2. Click en "Crear Reporte"
3. Completar los datos del reporte
4. (Opcional) Adjuntar una imagen
5. Click en "Enviar Reporte"

### Ver mis Reportes

1. En la barra lateral, click en "Mis Reportes"
2. Se mostrará el historial completo de reportes
3. Puedes editar, ver detalles o eliminar un reporte

## 🔐 Seguridad

- Las contraseñas se almacenan con hash bcrypt
- Se utiliza prepared statements para prevenir SQL Injection
- Validación de entrada en el lado del servidor
- Validación de tipos de archivo para carga de imágenes

## 🚀 Mejoras Futuras

- [ ] Integración con Google Maps API
- [ ] Sistema de comentarios en reportes
- [ ] Notificaciones en tiempo real
- [ ] Panel administrativo
- [ ] Estadísticas y reportes
- [ ] App móvil nativa
- [ ] Sistema de puntuación de usuarios

## 📧 Contacto

- Autor: XxDidixX777
- Email: didiereduardo2006@gmail.com

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

---

Hecho con ❤️ para mejorar nuestras ciudades.
