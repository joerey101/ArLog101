# Changelog

All notable changes to the **ArLog Jobs** project will be documented in this file.

## [v2.0.5] - 2026-01-14

### Added
- **Job Application Flow**: Complete end-to-end flow for candidates to apply for jobs.
- **Public Job Board**: `/empleos` page with search, filters, and dark mode UI.
- **Job Detail Page**: `/empleos/[id]` with full description and smart "Apply" button.
- **Global Dark Mode**: enforced `slate-950` background.

### Fixed
- **Critical Auth Role Bug**: Fixed issue where 'CANDIDATO' role (uppercase) was not recognized by session logic (lowercase).
- **404 on Job Details**: Fixed Next.js 15+ async params handling preventing job pages from loading.
- **Dashboard Redirection**: Infinite loops and unknown role errors resolved in `/dashboard`.
- **Navigation UX**: "Volver" button now intelligently redirects to Dashboard (if logged in) or Home (if public).
- **Logout Loop**: Candidate logout now redirects cleanly to Home.

### Changed
- Forced dynamic rendering on Job pages to ensure fresh data.

---
## [v2.0.4] - 2026-01-13
- Initial migration foundation.
- Setup Next.js, Tailwind, Prisma.
- Database connection and initial seeding.
