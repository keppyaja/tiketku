// Klien tipis untuk memanggil REST API Laravel memakai fetch bawaan Node.
const BASE = process.env.API_URL || "http://127.0.0.1:8000/api";

class ApiError extends Error {
  constructor(message, status, errors = {}) {
    super(message);
    this.status = status;
    this.errors = errors;
  }
}

async function api(req, method, path, body) {
  const headers = { Accept: "application/json" };
  const isFormData = body instanceof FormData;
  if (body && !isFormData) headers["Content-Type"] = "application/json";
  if (req.session && req.session.token) headers.Authorization = `Bearer ${req.session.token}`;

  let res;
  try {
    res = await fetch(BASE + path, {
      method,
      headers,
      body: body ? (isFormData ? body : JSON.stringify(body)) : undefined,
    });
  } catch (e) {
    throw new ApiError("API Laravel tidak dapat dihubungi. Pastikan php artisan serve berjalan.", 503);
  }

  const data = await res.json().catch(() => null);
  if (!res.ok) {
    throw new ApiError(data?.message || "Terjadi kesalahan pada API.", res.status, data?.errors || {});
  }
  return data;
}

module.exports = { api, ApiError };
