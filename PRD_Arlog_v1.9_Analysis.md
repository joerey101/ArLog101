# Product Requirements Document (PRD): ArLog Jobs v1.9 - Status Report
**Fecha:** 13 de Enero, 2026  
**Versión:** 1.0 (Post-Despliegue v1.8)  
**Estado Actual:** Producción (v1.8 Stable)

---

## 1. Resumen Ejecutivo
La plataforma ha evolucionado desde un MVP básico (v1.0) a un **Marketplace de Talento Logístico completamente funcional (v1.8)**. Se ha completado una reingeniería visual total ("Dark/Glass Theme") y se han solidificado los flujos críticos de interacción entre Candidatos y Empresas.

El sistema actual permite el ciclo de vida completo de un empleo: **Publicación -> Búsqueda -> Postulación -> Gestión -> Feedback**.

---

## 2. Funcionalidades Implementadas (Done)

### 2.1. Core & Arquitectura
- [x] **Identidad Visual v1.8:** Diseño "Dark Mode" con efectos Glassmorphism y paleta de colores semántica (Esmeralda para candidatos, Cian para empresas).
- [x] **Sistema de Autenticación Unificado:** Login/Registro separado por roles (Empresa/Candidato) con manejo seguro de sesiones.
- [x] **Base de Datos Relacional:** Estructura optimizada para Usuarios, Perfiles, Anuncios, Postulaciones y Etiquetas.
- [x] **Despliegue Automatizado:** Script `deploy.py` para sincronización con Dreamhost vía SFTP.

### 2.2. Módulo Candidatos
- [x] **Perfil Profesional Enriquecido:** Carga de datos personales (Teléfono, CUIT, Ubicación, LinkedIn) y CV en PDF.
- [x] **Búsqueda Inteligente:** Buscador en Home conectado a listado de empleos con filtros por palabras clave y ubicación.
- [x] **Motor de Postulación:**
    - Postulación "en un clic" usando el CV guardado.
    - Postulación como invitado (sin cuenta) con carga de CV temporal.
- [x] **Dashboard de Seguimiento (`mis_postulaciones.php`):**
    - Vista en tiempo real del estado de las candidaturas.
    - Badges de estado: *Enviada, Visto, Contactado*.
    - Historial completo.

### 2.3. Módulo Empresas
- [x] **Dashboard de Gestión (`dashboard_empresa.php`):**
    - Métricas clave (Total Anuncios, Activos, Postulantes).
    - Listado de búsquedas con accesos directos.
- [x] **Gestión de Anuncios (CRUD):**
    - **Crear:** Formulario modal con tags y selectores.
    - **Editar:** Capacidad de modificar anuncios activos.
    - **Eliminar:** Borrado lógico/físico de vacantes y sus dependencias.
- [x] **Gestión de Talentos (`postulantes.php`):**
    - Visualización de candidatos por vacante.
    - Acceso a datos de contacto (WhatsApp, Email, LinkedIn).
    - **Cambio de Estados:** Marcar como "Visto", "Contactado" o "Descartado".

---

## 3. Análisis de Brechas (Gap Analysis)
*Lo que falta para alcanzar la versión v2.0 "Enterprise Ready".*

### 3.1. Comunicación y Notificaciones (Prioridad Alta)
- [ ] **Notificaciones por Email:** Integrar SMTP (ej: PHPMailer/SendGrid) para avisar al candidato cuando es "Contactado".
- [ ] **Alertas de Empleo:** Avisar a candidatos sobre nuevos puestos que coinciden con su perfil.

### 3.2. Experiencia de Empresa (Prioridad Media)
- [ ] **Perfil de Empresa:** Página para cargar Logo y Descripción de la empresa (branding empleador).
- [ ] **Filtros Avanzados de Candidatos:** Ordenar postulantes por "Match" de etiquetas o fecha.
- [ ] **Descarga Masiva:** Exportar lista de postulantes a Excel/CSV.

### 3.3. Seguridad y Administración (Prioridad Baja/Mantenimiento)
- [ ] **Recuperación de Contraseña:** Flujo de "Olvidé mi contraseña".
- [ ] **Panel Super Admin:** Interfaz para moderar anuncios y bloquear usuarios malintencionados (Actualmente existe `admin.php` básico).
- [ ] **Validación de Archivos:** Reforzar seguridad en la subida de PDFs (análisis de virus, validación estricta de MIME types).

---

## 4. Próximos Pasos Recomendados (Roadmap v1.9 -> v2.0)

1.  **Cierre del Feedback Loop:** Asegurar que el cambio de estado en `postulantes.php` dispare, si no un email, al menos una notificación visual clara en la próxima visita del candidato.
2.  **Optimización Mobile:** Realizar un testeo exhaustivo en dispositivos móviles para el dashboard de empresa (tablas complejas suelen romperse).
3.  **Refactorización de Código:** Limpiar archivos legacy (`v1.7`) y estandarizar el manejo de errores (bloques try/catch globales).

---
*Generado automáticamente por Antigravity AI Agent.*
