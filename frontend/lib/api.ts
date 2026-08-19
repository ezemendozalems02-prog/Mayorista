export const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

export async function apiRequest(endpoint: string, options: RequestInit = {}) {
    const token = typeof window !== 'undefined' ? localStorage.getItem('auth_token') : null;

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...options.headers,
    };

    const response = await fetch(`${API_URL}${endpoint}`, {
        ...options,
        headers,
    });

    if (response.status === 401) {
        if (typeof window !== 'undefined') {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
    }

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message || 'Error en la petición');
    }

    return data;
}
