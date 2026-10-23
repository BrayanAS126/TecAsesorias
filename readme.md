# TecAsesora

> Sistema de Asesorías Académicas para el Tecnológico de Tijuana.  
> Construido con PHP 8 + PDO + MariaDB, autenticación por roles y sesiones seguras.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.4%2B-003545?logo=mariadb&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Local-FB7A24?logo=xampp&logoColor=white)

---

## Índice

* [Características Principales](#características-principales)
* [Requisitos](#requisitos)
* [Instalación](#instalación)
* [Configuración de la Base de Datos](#configuración-de-la-base-de-datos)
* [Estructura del Proyecto](#estructura-del-proyecto)
* [Seguridad](#seguridad)
* [Trabajo Futuro](#trabajo-futuro)

---

## Características Principales

* **Sistema Multi-Rol Completo:**
  * **Administrador:** Panel de control con estadísticas en tiempo real y gestión completa (CRUD) de Usuarios, Materias y Salones.
  * **Docente:** Supervisión de asesores asignados, control de espacios físicos, asignación directa de horarios y generación de reportes administrativos.
  * **Asesor:** Autogestión de horarios de disponibilidad, procesamiento de solicitudes (Aceptar/Rechazar), bitácora de temas, control de cupos y cálculo automático de progreso/créditos.
  * **Alumno:** Vistas dinámicas para solicitar asesorías, consultar el historial, materias, salones y seguimiento de solicitudes en tiempo real.
* **Flujo End-to-End Funcional:** Ciclo de vida completo de una asesoría (Solicitud -> Aceptación -> Ejecución -> Registro de Tema -> Créditos).
* **Exportación Administrativa:** Generación de reportes en formato CSV compatibles con Excel para Usuarios, Asesorías, Materias y Salones con soporte nativo de caracteres especiales.
* **UX Optimizada:** Buscadores en tiempo real mediante JavaScript, persistencia de tema (Dark/Light) vía `localStorage` y zona horaria configurada localmente.

---

## Requisitos

* **PHP** 8 o superior con PDO MySQL habilitado.
* **MariaDB** 10.4 o superior.
* Servidor local (**XAMPP**, WAMP, Laragon, etc.).

---

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/TuUsuario/TecAsesorias.git
   ```
2. Coloca la carpeta del proyecto en tu directorio raíz del servidor local (por ejemplo, `htdocs/TecAsesora`).
3. Crea un archivo llamado `credenciales.php` en la raíz del proyecto con la siguiente estructura:
   ```php
   <?php
   $db_host     = 'localhost';
   $db_user     = 'root';
   $db_password = '';
   $db_name     = 'db_asesorias_academicas';
   $db_charset  = 'utf8mb4';
   ```
   > ⚠️ *Nota: Este archivo es local y no se sube al repositorio por seguridad.*

4. Importa la base de datos (ver la sección de [Configuración de la Base de Datos](#configuración-de-la-base-de-datos)).
5. Asegúrate de que Apache y MySQL estén activos en tu servidor local e ingresa a `http://localhost/TecAsesora/login.php`.

---

## Configuración de la Base de Datos

El repositorio incluye el archivo `db_asesorias_academicas.sql` con la estructura relacional y catálogos iniciales.

* **Opción A (Terminal):**
  ```bash
  mysql --default-character-set=utf8mb4 -u root -p db_asesorias_academicas < db_asesorias_academicas.sql
  ```
* **Opción B (phpMyAdmin):**
  1. Crea una base de datos vacía llamada `db_asesorias_academicas`.
  2. Selecciona la base de datos, ve a la pestaña **Importar** y selecciona el archivo `.sql`.

> *Nota de compatibilidad:* Si utilizas MariaDB 10.4, verifica el cotejamiento del archivo `.sql` (reemplazando de ser necesario `utf8mb4_uca1400_ai_ci` por `utf8mb4_unicode_ci`). Las versiones 11+ no requieren este ajuste.

### Resumen de Tablas

| Tabla | Descripción | Estado |
| :--- | :--- | :--- |
| `usuarios` | Autenticación y roles del sistema | ✅ Conectada |
| `alumnos` | Expedientes de alumnos | ✅ Conectada |
| `asesores` | Alumnos habilitados como asesores | ✅ Conectada |
| `asesores_materias` | Relación de materias por asesor | ✅ Conectada |
| `disponibilidad_asesor` | Bloques de horarios libres | ✅ Funcional |
| `docentes` | Expedientes de docentes | ✅ Conectada |
| `historial_asesorias` | Bitácora histórica de sesiones | ✅ Dinámica |
| `materias` | Catálogo institucional | ✅ Conectada |
| `notificaciones` | Avisos internos del sistema | ⏳ En desarrollo |
| `salones` | Espacios físicos y enlaces virtuales | ✅ Conectada |
| `solicitudes_asesoria` | Cola de solicitudes activas | ✅ Funcional |

---

## Estructura del Proyecto

```text
TecAsesora/
│
├── assets/             # Estilos CSS y recursos gráficos
├── components/         # Elementos reutilizables (navbar, sidebar)
├── layout/             # Estructura visual principal de las vistas
├── pages/              # Vistas organizadas por rol (administrador, asesor, alumno, docente)
├── config.php          # Configuración global y rutas permitidas
├── conexion.php        # Conexión a base de datos mediante PDO
├── queries_*.php       # Capa de persistencia y consultas SQL centralizadas
├── index.php           # Enrutador central del sistema
└── db_asesorias_academicas.sql
```

---

## Seguridad

* **Control de Acceso:** Enrutamiento centralizado validando strictly páginas permitidas según el rol de la sesión (`ALLOWED_PAGES`).
* **Integridad de Datos:** Uso de consultas preparadas con PDO para prevenir ataques de Inyección SQL.
* **Gestión de Credenciales:** Contraseñas cifradas mediante `password_hash()` y validación segura con `password_verify()`.
* **Mitigación de Vulnerabilidades:** Protección CSRF global en formularios de acción (`POST`) y aplicación del patrón PRG (Post/Redirect/Get) para evitar reenvíos accidentales.

---

## Trabajo Futuro

* [ ] Implementación completa del flujo de registro público y recuperación de contraseña.
* [ ] Módulo de notificaciones en tiempo real.
* [ ] Exportación de reportes institucionales oficiales en formato PDF.