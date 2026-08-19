<?php

namespace App\Http\Requests\Arca;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'certificate'       => ['required', 'string'],
            'private_key'       => ['required', 'string'],
            'certificate_alias' => ['nullable', 'string', 'max:255'],
            'expires_at'        => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'certificate.required' => 'El certificado ARCA es requerido.',
            'private_key.required' => 'La clave privada es requerida.',
        ];
    }
}
