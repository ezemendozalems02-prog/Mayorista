<?php

namespace App\Http\Requests\Arca;

use App\Rules\ValidCuit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFiscalSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'cuit'                     => ['required', 'string', new ValidCuit],
            'razon_social'             => ['required', 'string', 'max:255'],
            'condicion_iva'            => ['required', 'in:MONOTRIBUTO,RESPONSABLE_INSCRIPTO,EXENTO'],
            'punto_venta'              => ['required', 'integer', 'min:1', 'max:99999'],
            'tipo_comprobante_default' => ['required', 'in:A,B,C'],
            'ambiente'                 => ['required', 'in:TESTING,PRODUCTION'],
            'motor_integracion'        => ['required', 'in:manual,afip_sdk'],
            'activo'                   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'condicion_iva.in'            => 'La condición IVA debe ser MONOTRIBUTO, RESPONSABLE_INSCRIPTO o EXENTO.',
            'tipo_comprobante_default.in' => 'El tipo de comprobante debe ser A, B o C.',
            'ambiente.in'                 => 'El ambiente debe ser TESTING o PRODUCTION.',
            'punto_venta.min'             => 'El punto de venta debe ser mayor a 0.',
        ];
    }
}
