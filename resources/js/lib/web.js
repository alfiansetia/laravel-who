import { createClient } from '@/lib/http';

// Web (single-controller) client: baseURL '/' ke routes/web.php.
// Controller web dual-mode: Accept JSON → JSON, selain itu Inertia/redirect.
const web = createClient('/');

export default web;
