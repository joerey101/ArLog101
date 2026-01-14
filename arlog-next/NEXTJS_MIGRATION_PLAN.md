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

### Fase 0: Inicialización ✅
1.  [x] Crear proyecto Next.js limpio.
2.  [x] Configurar Tailwind CSS y shadcn/ui.
3.  [x] Configurar conectividad a la BD existente (MySQL/Postgres).

### Fase 1: Capa de Datos (Prisma ORM) ✅
1.  [x] **Introspección**: Ejecutar `npx prisma db pull` (Hecho).
2.  [x] **Tipado**: Generar los tipos de TypeScript automáticamente (Hecho).

### Fase 2: Autenticación (El paso crítico) ✅
*   [x] Reemplazar `auth.php`.
*   [x] Implementar **NextAuth** con el proveedor de "Credentials".
*   [x] Definir Sesiones: Mapear roles (`admin`, `empresa`, `candidato`) a la sesión del JWT (Corregido bug mayúsculas v2.0.5).

### Fase 3: Rutas Públicas (Frontend) ✅
Reconstruir las vistas HTML/PHP usando Componentes React:
*   [x] `index.php` -> `app/page.tsx` (Hero, Buscador, Stats).
*   [x] `empleos.html` -> `app/empleos/page.tsx` (Listado con filtros serverside).
*   [x] `ver_empresa.php` -> `app/empresas/[id]/page.tsx` (Página de detalle de empleo completa).

### Fase 4: Paneles (Dashboards) 🚧 (En Progreso)
Convertir lógica de sesión protegida:
*   [x] **Candidato**: `mis_postulaciones.php` -> `app/candidato/dashboard`.
*   [ ] **Empresa**: `dashboard_empresa.php` -> `app/empresa/dashboard`.
*   [ ] **Admin**: `admin_dashboard.php` -> `app/admin/page.tsx`.

## 4. Análisis de Riesgos y Notas
*   **SEO**: Next.js mejora el SEO drásticamente con Server Side Rendering (SSR), algo que ya teníamos en PHP pero ahora será más rápido.
*   **Hosting**: PHP corre en cualquier servidor Apache (DreamHost compartido). Next.js requiere un entorno Node.js.
    *   *Solución*: Se puede desplegar en Vercel (gratis/pro) conectado a la BD de DreamHost, o configurar un servidor VPS/Node en DreamHost (más complejo). Recomiendo **Vercel** para el frontend/API y dejar la BD donde está.

## 5. Próximos Pasos Inmediatos
1.  Inicializar el repositorio Next.js.
2.  Conectar Prisma a la base de datos de prueba/dev.
3.  Migrar primero la "Home" para validar diseño visual.
