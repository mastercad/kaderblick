/**
 * Jest Global Setup
 * 
 * Wird vor allen Tests geladen. Mockt Module die import.meta.env
 * verwenden, da Jest dies nicht nativ unterstützt.
 */

// config.ts verwendet import.meta.env — global mocken
jest.mock('../config', () => ({
  BACKEND_URL: 'http://localhost:8081',
}));
