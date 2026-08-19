@extends('layouts.admin')
@section('title', 'Configuración ARCA')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-20 animate-in fade-in slide-in-from-bottom-5 duration-500" x-data="arcaWizard()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="sliders-horizontal" class="w-8 h-8 text-violet-500"></i>
                Configuración ARCA
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">Datos fiscales, certificado digital y conexión con ARCA</p>
        </div>
        <a href="{{ route('arca.comprobantes') }}"
            class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-dark-alt border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-violet-600 transition-colors">
            <i data-lucide="file-text" class="w-4 h-4"></i> Ver Comprobantes
        </a>
    </div>

    {{-- ── 1. Datos Fiscales ── --}}
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-8">
        <h2 class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
            <i data-lucide="building-2" class="w-4 h-4"></i> Datos Fiscales
        </h2>

        <form action="{{ route('arca.guardar-configuracion') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">CUIT *</label>
                    <input type="text" name="cuit" value="{{ old('cuit', $setting?->cuit) }}" required placeholder="20-12345678-9"
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Razón Social *</label>
                    <input type="text" name="razon_social" value="{{ old('razon_social', $setting?->razon_social) }}" required
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Condición IVA *</label>
                    <select name="condicion_iva" class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                        @foreach(['RESPONSABLE_INSCRIPTO' => 'Responsable Inscripto', 'MONOTRIBUTO' => 'Monotributo', 'EXENTO' => 'Exento'] as $val => $label)
                            <option value="{{ $val }}" {{ old('condicion_iva', $setting?->condicion_iva) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Punto de Venta *</label>
                    <input type="number" name="punto_venta" value="{{ old('punto_venta', $setting?->punto_venta ?? 1) }}" min="1" max="9999" required
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Comprobante por Defecto</label>
                    <select name="tipo_comprobante_default" class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                        @foreach(['A' => 'Factura A', 'B' => 'Factura B', 'C' => 'Factura C'] as $val => $label)
                            <option value="{{ $val }}" {{ old('tipo_comprobante_default', $setting?->tipo_comprobante_default) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Ambiente</label>
                    <select name="ambiente" class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                        <option value="TESTING" {{ old('ambiente', $setting?->ambiente) === 'TESTING' ? 'selected' : '' }}>Homologación (Pruebas)</option>
                        <option value="PRODUCTION" {{ old('ambiente', $setting?->ambiente) === 'PRODUCTION' ? 'selected' : '' }}>Producción (Real)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Motor de integración</label>
                    <select name="motor_integracion" class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                        <option value="manual" {{ old('motor_integracion', $setting?->motor_integracion) === 'manual' ? 'selected' : '' }}>Manual Laravel</option>
                        <option value="afip_sdk" {{ old('motor_integracion', $setting?->motor_integracion) === 'afip_sdk' ? 'selected' : '' }}>Afip SDK</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', $setting?->activo) ? 'checked' : '' }}
                    class="w-5 h-5 accent-violet-600 rounded">
                <label for="activo" class="text-sm font-bold text-gray-700 dark:text-gray-300 cursor-pointer">Módulo de facturación activo</label>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-violet px-8 py-3 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">
                    Guardar Datos Fiscales
                </button>
            </div>
        </form>
    </div>

    {{-- ── 2. Certificado Digital ── --}}
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-8">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Certificado Digital
            </h2>
            {{-- WIZARD TRIGGER BUTTON --}}
            <div class="flex items-center gap-2">
                <button @click="clearProgress(); openWizard()" x-show="hasSavedProgress" x-cloak
                    class="flex items-center gap-2 px-4 py-2.5 text-red-500 hover:text-red-700 bg-red-500/10 hover:bg-red-500/20 rounded-2xl font-black text-xs uppercase tracking-widest transition-all">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reiniciar
                </button>
                <button @click="openWizard()"
                    class="flex items-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg transition-all active:scale-95">
                    <i data-lucide="wand-2" class="w-4 h-4"></i>
                    <span x-text="hasSavedProgress ? 'Continuar configuración...' : 'Configurar Certificado AFIP'"></span>
                </button>
            </div>
        </div>

        {{-- Current status --}}
        @if($cert)
        <div class="flex flex-wrap gap-3 mb-6">
            @if($cert->certificate)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-black uppercase tracking-widest">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Certificado cargado
            </span>
            @endif
            @if($cert->private_key)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-black uppercase tracking-widest">
                <i data-lucide="key" class="w-3.5 h-3.5"></i> Clave privada cargada
            </span>
            @endif
            @if($cert->certificate_alias)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-500/10 text-violet-600 dark:text-violet-400 rounded-xl text-xs font-black uppercase tracking-widest">
                <i data-lucide="tag" class="w-3.5 h-3.5"></i> {{ $cert->certificate_alias }}
            </span>
            @endif
            @if($cert->expires_at)
                @if($cert->isExpired())
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500/10 text-red-600 dark:text-red-400 rounded-xl text-xs font-black uppercase tracking-widest">
                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Vencido: {{ $cert->expires_at->format('d/m/Y') }}
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-xl text-xs font-black uppercase tracking-widest">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Vence: {{ $cert->expires_at->format('d/m/Y') }}
                </span>
                @endif
            @endif
        </div>

        {{-- Botón para validar certificado y clave --}}
        <div class="mb-6 flex flex-col items-start gap-2">
            <button @click.prevent="validarCert()" :disabled="validating"
                class="flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-colors shadow-md disabled:opacity-50">
                <i data-lucide="shield-check" class="w-4 h-4" x-show="!validating"></i>
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="validating" x-cloak></i>
                Validar certificado y clave
            </button>
            <div x-show="validResult" x-cloak class="px-4 py-3 rounded-xl border text-sm font-bold flex items-start gap-2"
                :class="validResult?.success ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600' : 'bg-red-500/10 border-red-500/20 text-red-600'">
                <i :data-lucide="validResult?.success ? 'check-circle' : 'alert-circle'" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                <span x-text="validResult?.message"></span>
            </div>
        </div>
        @else
        <div class="mb-6 flex items-center gap-2 text-sm text-amber-600 dark:text-amber-400 bg-amber-500/10 border border-amber-500/20 rounded-2xl px-4 py-3">
            <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
            <span class="font-bold">No hay certificado cargado. Usá el wizard para configurarlo paso a paso.</span>
        </div>
        @endif

        <div class="text-xs text-gray-400 bg-gray-50 dark:bg-dark rounded-2xl px-4 py-3 mb-5 flex items-start gap-2">
            <i data-lucide="lock" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
            Los datos del certificado se almacenan cifrados. No son accesibles una vez guardados.
        </div>

        <form action="{{ route('arca.guardar-certificado') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                    Certificado (.crt) — contenido completo con BEGIN/END CERTIFICATE
                </label>
                <textarea name="certificate" rows="5" required placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----"
                    class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-mono text-xs resize-none"></textarea>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                    Clave Privada (.key) — contenido completo con BEGIN/END RSA PRIVATE KEY
                </label>
                <textarea name="private_key" rows="5" required placeholder="-----BEGIN RSA PRIVATE KEY-----&#10;...&#10;-----END RSA PRIVATE KEY-----"
                    class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-mono text-xs resize-none"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Alias (opcional)</label>
                    <input type="text" name="certificate_alias" value="{{ old('certificate_alias', $cert?->certificate_alias) }}"
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Fecha de vencimiento (opcional)</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at', $cert?->expires_at?->format('Y-m-d')) }}"
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-violet px-8 py-3 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl">
                    {{ $cert ? 'Actualizar Certificado' : 'Guardar Certificado' }}
                </button>
            </div>
        </form>
    </div>

    {{-- ── 3. Test de Conexión ── --}}
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-8">
        <h2 class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
            <i data-lucide="wifi" class="w-4 h-4"></i> Test de Conexión con ARCA
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Verifica que el certificado y los datos fiscales son correctos autenticándose contra WSAA de ARCA.
        </p>
        <form action="{{ route('arca.test-connection') }}" method="POST">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl transition-colors">
                <i data-lucide="zap" class="w-4 h-4"></i> Probar Conexión
            </button>
        </form>
    </div>


    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- WIZARD MODAL                                               --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        @keydown.escape.window="if(step < 6 || !saved) open = false">

        <div @click.away="if(step < 6 || !saved) open = false"
            class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 dark:border-white/5 flex-shrink-0">
                <div>
                    <h3 class="font-black italic text-lg flex items-center gap-2">
                        <i data-lucide="wand-2" class="w-5 h-5 text-violet-500"></i>
                        Configurar Certificado AFIP
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Paso <span x-text="step"></span> de 6 —
                        <span x-text="['Generar Claves', 'Subir CSR a AFIP', 'Cargar Certificado', 'Asociar WSFE', 'Guardar', 'Probar Conexión'][step - 1]"></span>
                    </p>
                </div>
                <button @click="open = false" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Progress Bar --}}
            <div class="flex gap-1 px-7 py-3 flex-shrink-0 border-b border-gray-100 dark:border-white/5">
                <template x-for="i in 6" :key="i">
                    <div class="flex-1 h-1.5 rounded-full transition-all duration-300"
                        :class="i <= step ? 'bg-violet-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                </template>
            </div>

            {{-- Modal Body --}}
            <div class="overflow-y-auto flex-1 px-7 py-6">

                {{-- Autosave Indicator --}}
                <div x-show="hasSavedProgress && step < 6" class="mb-4 flex items-center justify-center gap-2 text-[10px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-500/10 py-1.5 px-3 rounded-full w-max mx-auto">
                    <i data-lucide="cloud-lightning" class="w-3.5 h-3.5"></i> Progreso guardado automáticamente
                </div>

                {{-- Error Banner --}}
                <div x-show="error" class="mb-5 flex items-start gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-sm text-red-600 dark:text-red-400">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                    <span x-text="error"></span>
                </div>

                {{-- ─── STEP 1: Generar Claves ─── --}}
                <div x-show="step === 1">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Generamos automáticamente un par de claves RSA 2048 bits y un CSR para presentar ante AFIP.
                    </p>

                    @if(!$setting?->cuit)
                    <div class="mb-5 flex items-start gap-3 p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-sm text-amber-600 dark:text-amber-400">
                        <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                        <span class="font-bold">Primero guardá los Datos Fiscales (CUIT y Razón Social) antes de continuar.</span>
                    </div>
                    @endif

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Alias del Certificado</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="alias"
                                    class="flex-1 px-4 py-3 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-mono text-sm"
                                    placeholder="ej: mito_prod">
                                <button type="button" @click="alias = suggestAlias()"
                                    class="px-4 py-3 rounded-2xl bg-gray-100 dark:bg-dark hover:bg-gray-200 dark:hover:bg-white/5 text-xs font-black uppercase tracking-widest text-gray-500 transition-colors whitespace-nowrap">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 inline mr-1"></i>Sugerir
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400">Solo letras, números, guiones y guiones bajos. Ej: <code class="text-violet-400">mito_{{ Auth::user()->organization_id }}</code></p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">CUIT</p>
                                <p class="font-mono font-bold">{{ $setting?->cuit ?? '—' }}</p>
                            </div>
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Razón Social</p>
                                <p class="font-bold truncate">{{ $setting?->razon_social ?? '—' }}</p>
                            </div>
                        </div>

                        <button @click="generateKeys()" :disabled="loading || !alias || !'{{ $setting?->cuit }}'"
                            class="w-full py-3.5 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-lg transition-all flex items-center justify-center gap-2">
                            <span x-show="!loading" class="flex items-center gap-2">
                                <i data-lucide="key" class="w-4 h-4"></i> Generar Clave RSA + CSR
                            </span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Generando...
                            </span>
                        </button>

                        {{-- Results after generation --}}
                        <div x-show="keysGenerated" class="space-y-4">
                            <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex items-center gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                                <div>
                                    <p class="text-sm font-black text-emerald-700 dark:text-emerald-400">¡Claves generadas exitosamente!</p>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-0.5">RSA 2048 bits + CSR SHA-256 listo para AFIP.</p>
                                </div>
                            </div>

                            <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-start gap-3">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5"></i>
                                <p class="text-xs font-bold text-amber-600 dark:text-amber-400">
                                    La clave privada <strong>solo se muestra ahora</strong>. Descargala antes de continuar. No la compartás con nadie.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <button @click="downloadText(privateKey, keyFilename)"
                                    class="flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-violet-500/40 hover:bg-violet-500/10 text-violet-600 dark:text-violet-400 font-black text-xs uppercase tracking-widest transition-colors">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Descargar .key
                                </button>
                                <button @click="downloadText(csr, csrFilename)"
                                    class="flex items-center justify-center gap-2 py-3 rounded-2xl border-2 border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 font-black text-xs uppercase tracking-widest transition-colors">
                                    <i data-lucide="file-code" class="w-4 h-4"></i>
                                    Descargar .csr
                                </button>
                            </div>

                            <div class="rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                                <div class="bg-gray-50 dark:bg-dark px-4 py-2.5 flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Vista previa del CSR</span>
                                    <button @click="copyToClipboard(csr)" class="text-[10px] text-violet-500 font-black uppercase tracking-widest hover:underline flex items-center gap-1">
                                        <i data-lucide="copy" class="w-3 h-3"></i> Copiar
                                    </button>
                                </div>
                                <pre class="px-4 py-3 text-[10px] font-mono text-gray-500 dark:text-gray-400 max-h-28 overflow-auto whitespace-pre-wrap break-all"
                                    x-text="csr ? csr.slice(0, 300) + '...' : ''"></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── STEP 2: Subir CSR a AFIP ─── --}}
                <div x-show="step === 2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Con el archivo <code class="text-violet-400">.csr</code> descargado, seguí estos pasos en el sitio de AFIP:
                    </p>

                    <ol class="space-y-3 mb-6">
                        @foreach([
                            ['Ingresá al portal de AFIP con tu CUIT y clave fiscal.', null],
                            ['Andá a <strong>Administrador de Certificados Digitales</strong> (buscalo en el buscador de servicios).', null],
                            ['Hacé click en <strong>Nueva DN</strong> y creá un nuevo alias usando el nombre: <code class="text-violet-400 font-mono">'. ($setting?->cuit ? 'mito_' . preg_replace('/[^0-9]/', '', $setting->cuit) : 'mito_TUCUIT') . '</code>', null],
                            ['Subí el archivo <code class="text-violet-400 font-mono">.csr</code> que descargaste en el paso anterior.', null],
                            ['Una vez procesado, <strong>descargá el certificado</strong> (.crt) desde esa pantalla.', null],
                        ] as [$text, $_])
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-violet-500/20 text-violet-600 dark:text-violet-400 text-[10px] font-black flex items-center justify-center flex-shrink-0 mt-0.5">{{ $loop->iteration }}</span>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{!! $text !!}</p>
                        </li>
                        @endforeach
                    </ol>

                    <div class="flex flex-col sm:flex-row gap-3 mb-5">
                        <a href="https://auth.afip.gob.ar/contribuyente_/login.xhtml" target="_blank" rel="noopener"
                            class="flex items-center justify-center gap-2 px-5 py-3 bg-[#00a4e8] hover:bg-[#0090cc] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-colors shadow-lg">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            Abrir Portal AFIP
                        </a>
                        <button @click="downloadText(csr, csrFilename)" x-show="csr"
                            class="flex items-center justify-center gap-2 px-5 py-3 border border-gray-200 dark:border-white/10 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Re-descargar CSR
                        </button>
                    </div>

                    <div x-show="csr" class="mb-5 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-dark px-4 py-2.5 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Tu CSR generado</span>
                            <button @click="copyToClipboard(csr)" class="text-[10px] text-violet-500 font-black uppercase tracking-widest hover:underline flex items-center gap-1">
                                <i data-lucide="copy" class="w-3 h-3"></i> Copiar
                            </button>
                        </div>
                        <pre class="px-4 py-3 text-[10px] font-mono text-gray-500 dark:text-gray-400 max-h-20 overflow-auto whitespace-pre-wrap break-all"
                            x-text="csr ? csr.slice(0, 200) + '...' : ''"></pre>
                    </div>

                    <div class="p-4 bg-violet-500/10 border border-violet-500/20 rounded-2xl text-xs text-violet-600 dark:text-violet-400 flex items-start gap-2">
                        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                        <span>Una vez que descargues el <strong>.crt</strong> de AFIP, volvé aquí y hacé click en <strong>Siguiente</strong>.</span>
                    </div>
                </div>

                {{-- ─── STEP 3: Cargar Certificado ─── --}}
                <div x-show="step === 3">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Pegá el contenido del archivo <code class="text-violet-400">.crt</code> que descargaste de AFIP.
                    </p>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                            Contenido del Certificado (.crt)
                        </label>
                        <textarea x-model="certificate" rows="8"
                            placeholder="-----BEGIN CERTIFICATE-----&#10;MIIFxDCCA6ygAwIBAgIRAJ...&#10;-----END CERTIFICATE-----"
                            class="w-full px-4 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent font-mono text-xs resize-none outline-none transition-colors"
                            :class="certificate.length > 10 ? (certValid ? 'border-emerald-500/50' : 'border-red-500/50') : 'focus:border-violet-500/50'"></textarea>

                        <div x-show="certificate.length > 10 && !certValid"
                            class="flex items-center gap-2 text-xs text-red-500 font-bold">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            El formato no es válido. Debe comenzar con <code>-----BEGIN CERTIFICATE-----</code> y terminar con <code>-----END CERTIFICATE-----</code>
                        </div>
                        <div x-show="certValid"
                            class="flex items-center gap-2 text-xs text-emerald-500 font-bold">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            Formato de certificado válido
                        </div>
                    </div>

                    <button @click="pasteFromClipboard()"
                        class="mt-3 flex items-center gap-2 text-xs text-violet-500 hover:text-violet-700 font-black uppercase tracking-widest transition-colors">
                        <i data-lucide="clipboard" class="w-3.5 h-3.5"></i>
                        Pegar desde portapapeles
                    </button>
                </div>

                {{-- ─── STEP 4: Asociar WSFE ─── --}}
                <div x-show="step === 4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Para que AFIP autorice el uso de la Facturación Electrónica (WSFEv1) con tu certificado, necesitás asociar el servicio:
                    </p>

                    <ol class="space-y-3 mb-6">
                        @foreach([
                            'Ingresá nuevamente al portal AFIP.',
                            'Buscá el servicio <strong>Administrador de Relaciones de Clave Fiscal</strong>.',
                            'Hacé click en <strong>Nueva Relación</strong>.',
                            'En el campo <strong>Representante</strong> elegí <em>Yo como representante</em> (o tu empresa).',
                            'En <strong>Servicio</strong> elegí <strong>WSFE — Facturación Electrónica (WSFEv1)</strong>.',
                            'En <strong>Certificado / Alias</strong> seleccioná el alias que creaste en el Paso 2.',
                            'Confirmá y guardá. La relación se activa de inmediato.',
                        ] as $idx => $text)
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-violet-500/20 text-violet-600 dark:text-violet-400 text-[10px] font-black flex items-center justify-center flex-shrink-0 mt-0.5">{{ $idx + 1 }}</span>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{!! $text !!}</p>
                        </li>
                        @endforeach
                    </ol>

                    <a href="https://auth.afip.gob.ar/contribuyente_/login.xhtml" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-[#00a4e8] hover:bg-[#0090cc] text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-colors shadow-lg">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Abrir Portal AFIP
                    </a>

                    <div class="mt-5 p-4 bg-violet-500/10 border border-violet-500/20 rounded-2xl text-xs text-violet-600 dark:text-violet-400 flex items-start gap-2">
                        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                        <span>Una vez que hayas asociado el servicio WSFE en AFIP, hacé click en <strong>Siguiente</strong> para guardar todo en el sistema.</span>
                    </div>
                </div>

                {{-- ─── STEP 5: Guardar ─── --}}
                <div x-show="step === 5">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Revisá el resumen y guardá la configuración en el sistema. La clave privada se almacenará <strong>cifrada</strong>.
                    </p>

                    <div class="space-y-3 mb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Alias</p>
                                <p class="font-mono font-bold text-sm" x-text="alias"></p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Ambiente</p>
                                <p class="font-bold text-sm">{{ $setting?->ambiente === 'PRODUCTION' ? 'Producción' : 'Homologación (Test)' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Clave Privada</p>
                                <p class="text-sm" x-text="privateKey ? '✓ Generada (RSA 2048)' : '⚠ No disponible'"></p>
                            </div>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Certificado</p>
                                <p class="text-sm" x-text="certValid ? '✓ Válido' : '⚠ No cargado'"></p>
                            </div>
                        </div>

                        <div x-show="privateKey" class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-start gap-3">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5"></i>
                            <div class="text-xs text-amber-600 dark:text-amber-400">
                                <p class="font-black mb-1">Último aviso: descargá la clave privada ahora</p>
                                <p>Una vez guardada, la clave <strong>nunca más se mostrará</strong>. Si la perdés, deberás generar un nuevo certificado.</p>
                                <button @click="downloadText(privateKey, keyFilename)" class="mt-2 flex items-center gap-1.5 text-amber-700 dark:text-amber-300 font-black uppercase tracking-widest text-[10px] hover:underline">
                                    <i data-lucide="download" class="w-3 h-3"></i> Descargar .key ahora
                                </button>
                            </div>
                        </div>
                    </div>

                    <button @click="saveCertificate()" :disabled="loading || !certValid"
                        class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading" class="flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> Guardar Configuración
                        </span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Guardando...
                        </span>
                    </button>
                </div>

                {{-- ─── STEP 6: Test de Conexión ─── --}}
                <div x-show="step === 6">
                    <div class="flex items-center gap-3 mb-5 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-black text-emerald-700 dark:text-emerald-400">Certificado guardado correctamente</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-0.5">Clave privada y certificado almacenados con cifrado AES-256.</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                        Probá la conexión con WSAA para verificar que el certificado funciona correctamente con AFIP.
                    </p>

                    <button @click="runTestConnection()" :disabled="testing"
                        class="w-full py-4 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl transition-all flex items-center justify-center gap-2 mb-5">
                        <span x-show="!testing" class="flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4"></i> Probar Conexión AFIP
                        </span>
                        <span x-show="testing" class="flex items-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Conectando con WSAA...
                        </span>
                    </button>

                    {{-- Test Result --}}
                    <div x-show="testResult">
                        <div :class="testResult?.success ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400' : 'bg-red-500/10 border-red-500/20 text-red-700 dark:text-red-400'"
                            class="p-4 rounded-2xl border flex items-start gap-3 mb-3">
                            <i :data-lucide="testResult?.success ? 'check-circle' : 'x-circle'" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="font-black text-sm" x-text="testResult?.success ? 'Conexión exitosa con AFIP' : 'Error de conexión'"></p>
                                <p class="text-xs mt-1" x-text="testResult?.message"></p>
                            </div>
                        </div>
                        <div x-show="testResult?.success && testResult?.data?.expires_at" class="text-xs text-gray-500 dark:text-gray-400 px-1">
                            Token WSAA válido hasta: <span class="font-bold text-gray-700 dark:text-gray-300" x-text="testResult?.data?.expires_at"></span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between px-7 py-5 border-t border-gray-100 dark:border-white/5 flex-shrink-0">
                {{-- Back --}}
                <button x-show="step > 1 && step < 6"
                    @click="step--; error = ''"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-white/10 text-sm font-black hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Anterior
                </button>
                <div x-show="step === 1 || step === 6" class="w-1"></div>

                {{-- Next / Finish --}}
                <div class="flex items-center gap-3">
                    {{-- Step 1: advance only when keys generated --}}
                    <button x-show="step === 1"
                        @click="if(keysGenerated) { step++; error = '' }"
                        :disabled="!keysGenerated"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-black uppercase tracking-widest transition-all">
                        Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>

                    {{-- Steps 2, 3, 4: normal next (step 3 requires valid cert) --}}
                    <button x-show="step === 2 || step === 3 || step === 4"
                        @click="if(step === 3 && !certValid) { error = 'El certificado no tiene formato válido.' } else { step++; error = '' }"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-black uppercase tracking-widest transition-all">
                        Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>

                    {{-- Step 5: handled by the save button above --}}

                    {{-- Step 6: Finish --}}
                    <button x-show="step === 6"
                        @click="finish()"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-black uppercase tracking-widest transition-all">
                        <i data-lucide="check" class="w-4 h-4"></i> Finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('arcaWizard', () => ({
            open: false,
            step: 1,
            loading: false,
            testing: false,
            error: '',

            // Step 1
            alias: 'mito_{{ Auth::user()->organization_id }}',
            cuit: @js($setting?->cuit ?? ''),
            razonSocial: @js($setting?->razon_social ?? ''),
            privateKey: '',
            csr: '',
            keyFilename: '',
            csrFilename: '',
            keysGenerated: false,

            // Step 3
            certificate: '',

            // Step 6
            saved: false,
            testResult: null,

            get certValid() {
                const t = this.certificate.trim();
                return t.startsWith('-----BEGIN CERTIFICATE-----') && t.includes('-----END CERTIFICATE-----');
            },

            hasSavedProgress: false,
            savedData: null,

            init() {
                this.loadProgress();

                this.$watch('step', () => this.saveProgress());
                this.$watch('alias', () => this.saveProgress());
                this.$watch('privateKey', () => this.saveProgress());
                this.$watch('csr', () => this.saveProgress());
                this.$watch('keyFilename', () => this.saveProgress());
                this.$watch('csrFilename', () => this.saveProgress());
                this.$watch('keysGenerated', () => this.saveProgress());
                this.$watch('certificate', () => this.saveProgress());
            },

            saveProgress() {
                if (this.saved || this.step === 6) return;
                const data = {
                    step: this.step,
                    alias: this.alias,
                    privateKey: this.privateKey,
                    csr: this.csr,
                    keyFilename: this.keyFilename,
                    csrFilename: this.csrFilename,
                    keysGenerated: this.keysGenerated,
                    certificate: this.certificate,
                    timestamp: new Date().getTime()
                };
                localStorage.setItem('arca_wizard_progress', JSON.stringify(data));
                this.hasSavedProgress = true;
            },

            loadProgress() {
                const saved = localStorage.getItem('arca_wizard_progress');
                if (saved) {
                    try {
                        const data = JSON.parse(saved);
                        if (new Date().getTime() - data.timestamp < 86400000) {
                            this.hasSavedProgress = true;
                            this.savedData = data;
                        } else {
                            localStorage.removeItem('arca_wizard_progress');
                        }
                    } catch(e) {}
                }
            },

            clearProgress() {
                localStorage.removeItem('arca_wizard_progress');
                this.hasSavedProgress = false;
                this.savedData = null;
                this.step = 1;
                this.keysGenerated = false;
                this.privateKey = '';
                this.csr = '';
                this.certificate = '';
            },

            openWizard() {
                if (this.hasSavedProgress && this.savedData) {
                    this.step = this.savedData.step;
                    this.alias = this.savedData.alias;
                    this.privateKey = this.savedData.privateKey;
                    this.csr = this.savedData.csr;
                    this.keyFilename = this.savedData.keyFilename;
                    this.csrFilename = this.savedData.csrFilename;
                    this.keysGenerated = this.savedData.keysGenerated;
                    this.certificate = this.savedData.certificate;
                } else {
                    this.step = 1;
                    this.keysGenerated = false;
                    this.privateKey = '';
                    this.csr = '';
                    this.certificate = '';
                    this.saved = false;
                }
                this.error = '';
                this.testResult = null;
                this.open = true;
            },

            suggestAlias() {
                const base = (this.razonSocial || 'mito')
                    .toLowerCase()
                    .replace(/[^a-z0-9]/g, '_')
                    .replace(/_+/g, '_')
                    .slice(0, 20);
                return base + '_{{ Auth::user()->organization_id }}';
            },

            async generateKeys() {
                this.loading = true;
                this.error = '';
                try {
                    const resp = await fetch('{{ route('arca.generar-csr') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            alias: this.alias,
                            cuit: this.cuit,
                            razon_social: this.razonSocial,
                        }),
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.error = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error al generar claves');
                        return;
                    }
                    this.privateKey  = data.private_key;
                    this.csr         = data.csr;
                    this.keyFilename = data.filename_key;
                    this.csrFilename = data.filename_csr;
                    this.keysGenerated = true;
                } catch (e) {
                    this.error = 'Error de red: ' + e.message;
                } finally {
                    this.loading = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            downloadText(content, filename) {
                const a = document.createElement('a');
                a.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(content);
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            },

            async copyToClipboard(text) {
                try {
                    await navigator.clipboard.writeText(text);
                } catch {
                    // fallback — silent fail
                }
            },

            async pasteFromClipboard() {
                try {
                    this.certificate = await navigator.clipboard.readText();
                } catch {
                    this.error = 'No se pudo acceder al portapapeles. Pegá el texto manualmente.';
                }
            },

            async saveCertificate() {
                this.loading = true;
                this.error = '';
                try {
                    const resp = await fetch('{{ route('arca.guardar-certificado') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            certificate: this.certificate,
                            private_key: this.privateKey,
                            certificate_alias: this.alias,
                        }),
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.error = data.message || 'Error al guardar el certificado';
                        return;
                    }
                    this.saved = true;
                    this.step = 6;
                    this.error = '';
                } catch (e) {
                    this.error = 'Error de red: ' + e.message;
                } finally {
                    this.loading = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            async runTestConnection() {
                this.testing = true;
                this.testResult = null;
                try {
                    const resp = await fetch('{{ route('arca.test-connection') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    this.testResult = await resp.json();
                } catch (e) {
                    this.testResult = { success: false, message: 'Error de red: ' + e.message };
                } finally {
                    this.testing = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            finish() {
                this.open = false;
                window.location.reload();
            },

            validating: false,
            validResult: null,

            async validarCert() {
                this.validating = true;
                this.validResult = null;
                try {
                    const resp = await fetch('{{ route('arca.validar-certificado') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    this.validResult = await resp.json();
                } catch (e) {
                    this.validResult = { success: false, message: 'Error de red: ' + e.message };
                } finally {
                    this.validating = false;
                    this.$nextTick(() => lucide.createIcons());
                }
            },
        }));
    });
</script>
@endsection
