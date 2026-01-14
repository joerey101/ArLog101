# ArLog v2.0.5 Release Notes

**Version:** v2.0.5  
**Date:** January 14, 2026  
**Status:** Stable / Production Ready

## 🚀 Key Features Implemented

### 1. Job Application Flow (Flujo de Postulación)
- **Public Job Board (`/empleos`)**: 
  - Full search functionality (Title & Location).
  - Premium Dark Mode UI for job cards.
  - "Ver Detalle" button with clear call-to-action.
- **Job Detail Page (`/empleos/[id]`)**:
  - Complete job description view.
  - **Smart "Apply" Button**:
    - Redirects to Login if unauthenticated.
    - Shows "Postularme Ahora" for Candidates.
    - Shows "Solo Candidatos" for Company/Admin users.
    - Changes to "¡CV Enviado!" (Green) if already applied.
- **Backend Logic**:
  - **Async Params Fix**: Solved Next.js 15+ compatibility issue preventing 404 errors on job details.
  - **Role Management**: Fixed critical bug where 'CANDIDATO' (uppercase in DB) was not matching session role.

### 2. User Experience Improvements
- **Dashboard Redirection**: Fixed infinite loops and "Unknown Role" errors in `/dashboard`.
- **Smart Back Navigation**: 
  - In `/empleos`, the "Volver" button now intelligently redirects logged-in users to their Dashboard and anonymous visitors to the Home page.
- **Logout Flow**: Fixed "Sign Out" button in Candidate profile to redirect cleanly to Home (`/`) instead of a login loop.

### 3. Technical Enhancements
- **Global Dark Mode**: Enforced `slate-950` background consistency across all pages.
- **Force Dynamic Rendering**: Applied to job pages to ensure users always see the latest data (preventing stale caches).

## ✅ Verified Flows
- [x] Search Job -> View Detail -> Apply -> Success Feedback.
- [x] Login (Candidate) -> Dashboard -> Browse Jobs -> Apply.
- [x] Login (Company) -> View Candidates (Partial).
- [x] Logout -> Redirect to Home.

## 🔜 Next Steps (v2.1 Roadmap)
1. **PDF Uploads**: Allow candidates to upload real PDF CVs (currently using URL placeholders).
2. **Company Profile Editing**: Allow companies to update their logo and description.
3. **Advanced Candidate Profile**: Skill tags and experience timeline.
