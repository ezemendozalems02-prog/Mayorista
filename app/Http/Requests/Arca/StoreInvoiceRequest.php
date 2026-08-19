<?php

namespace App\Http\Requests\Arca;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'cliente_nombre'            => ['required', 'string', 'max:255'],
            'cliente_documento'         => ['nullable', 'string', 'max:20'],
            'cliente_condicion_iva'     => ['nullable', 'string', 'in:MONOTRIBUTO,RESPONSABLE_INSCRIPTO,EXENTO,CONSUMIDOR_FINAL'],
            'tipo_comprobante'          => ['required', 'in:A,B,C'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.descripcion'       => ['required', 'string', 'max:255'],
            'items.*.cantidad'          => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario'   => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_comprobante.in'            => 'El tipo de comprobante debe ser A, B o C.',
            'items.required'                 => 'La factura debe tener al menos un ítem.',
            'items.min'                      => 'La factura debe tener al menos un ítem.',
            'items.*.descripcion.required'   => 'Cada ítem debe tener una descripción.',
            'items.*.cantidad.min'           => 'La cantidad de cada ítem debe ser mayor a 0.',
            'items.*.precio_unitario.min'    => 'El precio unitario no puede ser negativo.',
        ];
    }
}
