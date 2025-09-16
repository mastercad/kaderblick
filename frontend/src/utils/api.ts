
import { BACKEND_URL } from '../../config';

// Hilfsfunktion: JWT aus Cookie extrahieren
function getJwtFromCookie(cookieName = 'jwt'): string | null {
  const match = document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'));
  return match ? decodeURIComponent(match[2]) : null;
}

interface ApiRequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  body?: any;
  headers?: Record<string, string>;
}

/**
 * Zentrale API-Funktion die automatisch Cookies mitschickt
 * und korrekte Headers setzt
 */
export async function apiRequest(endpoint: string, options: ApiRequestOptions = {}) {
  const {
    method = 'GET',
    body,
    headers = {}
  } = options;


  // JWT aus Cookie holen und als Authorization-Header setzen
  const jwt = getJwtFromCookie();
  const config: RequestInit = {
    method,
    credentials: 'include', // Immer Cookies mitschicken
    headers: {
      'Content-Type': 'application/json',
      ...(jwt ? { 'Authorization': `Bearer ${jwt}` } : {}),
      ...headers
    }
  };

  if (body && method !== 'GET') {
    config.body = JSON.stringify(body);
  }

  const url = `${BACKEND_URL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
  
  const response = await fetch(url, config);
  
  // Bei 401 könnte das Token abgelaufen sein
  if (response.status === 401) {
    // Hier könnte man einen Event dispatchen um den User automatisch auszuloggen
    console.warn('API request returned 401 - user might need to login again');
  }
  
  return response;
}

/**
 * Vereinfachte API-Funktion die direkt JSON zurückgibt
 */
export async function apiJson<T = any>(endpoint: string, options: ApiRequestOptions = {}): Promise<T> {
  const response = await apiRequest(endpoint, options);
  
  if (!response.ok) {
    const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
    throw new Error(errorData.message || `HTTP ${response.status}`);
  }
  
  return response.json();
}
