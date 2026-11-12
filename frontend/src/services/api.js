import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://backend.test/api',
  headers: { Accept: 'application/json' },
  timeout: 20000,
});

// Laravel validation errors (422) -> { field: 'message' }
export const validationErrors = (err) =>
  Object.fromEntries(Object.entries(err?.response?.data?.errors || {}).map(([k, v]) => [k, v[0]]));

export const errorMessage = (err) =>
  err?.response?.data?.message || 'We could not reach the server. Please try again.';

// Accept both { data: ... } (Laravel resources) and plain payloads
export const unwrap = (res) => res.data?.data ?? res.data;

export default api;
