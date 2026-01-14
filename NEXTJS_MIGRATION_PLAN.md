# Plan de Migración a Next.js - ArLog Jobs

## 1. Resumen Ejecutivo
El objetivo es modernizar la plataforma ArLog Jobs migrando de una arquitectura monolítica en PHP plano a una aplicación web moderna ("Web App") utilizando **Next.js 14+ (App Router)**. Esto permitirá una interfaz más rápida, interactiva y escalable, manteniendo la base de datos MySQL existente.

## 2. Tecnologías ("Stack") Propuesto
*   **Framework**: Next.js 14 (App Router)
*   **Lenguaje**: TypeScript (Mejor robustez y menos errores que PHP dinámica)
*   **Estilos**: Tailwind CSS (Ya utilizado, pero ahora integrado nativamente) + Framer Motion (para animaciones premium)
*   **Base de Datos**: Prisma ORM (Conectado a la MySQL existente en DreamHost)
*   **Autenticación**: NextAuth.js (v5)
*   **Iconos**: Lucide React (Más moderno que FontAwesome)
*   **UI Components**: shadcn/ui (Para componentes premium y accesibles)

## 3. Estrategia de Migración

### Fase 0: Inicialización
1.  Crear proyecto Next.js limpio.
2.  Configurar Tailwind CSS y shadcn/ui.
3.  Configurar conectividad a la BD existente (MySQL).

### Fase 1: Capa de Datos (Prisma ORM)
1.  **Introspección**: Ejecutar `npx prisma db pull` para que Prisma "lea" la estructura actual de tablas (`usuarios`, `anuncios`, `perfiles_*`, etc.) y genere el esquema automáticamente.
2.  **Tipado**: Generar los tipos de TypeScript automáticamente. Esto reemplazará a `db.php` y consultas SQL manuales inseguras.

### Fase 2: Autenticación (El paso crítico)
*   Reemplazar `auth.php`.
*   Implementar **NextAuth** con el proveedor de "Credentials".
*   **Reto**: La lógica de hashing de contraseñas (`password_hash` de PHP usa bcrypt). NextAuth/Node.js soportan bcrypt, por lo que los usuarios existentes **podrán loguearse sin cambiar contraseña**.
*   Definir Sesiones: Mapear roles (`admin`, `empresa`, `candidato`) a la sesión del JWT.

### Fase 3: Rutas Públicas (Frontend)
Reconstruir las vistas HTML/PHP usando Componentes React:
*   `index.php` -> `app/page.tsx` (Hero, Buscador, Stats).
*   `empleos.html` -> `app/empleos/page.tsx` (Listado con filtros serverside).
*   `ver_empresa.php` -> `app/empresas/[id]/page.tsx` (Página dinámica).

### Fase 4: Paneles (Dashboards)
Convertir lógica de sesión protegida:
*   **Candidato**: `mis_postulaciones.php` -> `app/candidato/dashboard/page.tsx`.
*   **Empresa**: `dashboard_empresa.php` -> `app/empresa/dashboard/page.tsx`.
*   **Admin**: `admin_dashboard.php` -> `app/admin/page.tsx`.

## 4. Análisis de Riesgos y Notas
*   **SEO**: Next.js mejora el SEO drásticamente con Server Side Rendering (SSR), algo que ya teníamos en PHP pero ahora será más rápido.
*   **Hosting**: PHP corre en cualquier servidor Apache (DreamHost compartido). Next.js requiere un entorno Node.js.
    *   *Solución*: Se puede desplegar en Vercel (gratis/pro) conectado a la BD de DreamHost, o configurar un servidor VPS/Node en DreamHost (más complejo). Recomiendo **Vercel** para el frontend/API y dejar la BD donde está.

## 5. Próximos Pasos Inmediatos
1.  Inicializar el repositorio Next.js.
2.  Conectar Prisma a la base de datos de prueba/dev.
3.  Migrar primero la "Home" para validar diseño visual.
