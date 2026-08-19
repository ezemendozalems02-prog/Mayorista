"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import {
    SlidersHorizontal,
    Save,
    Wifi,
    WifiOff,
    ShieldCheck,
    ShieldAlert,
    UploadCloud,
    RefreshCw,
    CheckCircle2,
    AlertCircle,
    Loader2,
    Info,
    AlertTriangle,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { toast } from "sonner"

// ─── types ────────────────────────────────────────────────────────────────────

interface FiscalSetting {
    cuit: string
    razon_social: string
    condicion_iva: string
    punto_venta: number
    tipo_comprobante_default: string
    ambiente: string
    activo: boolean
}

interface CertStatus {
    has_certificate: boolean
    has_private_key: boolean
    certificate_alias: string | null
    expires_at: string | null
    is_expired: boolean
}

interface ConnectionResult {
    success: boolean
    message: string
    data?: { environment: string; expiration: string; cached: boolean }
}

// ─── helpers ──────────────────────────────────────────────────────────────────

function SectionTitle({ children }: { children: React.ReactNode }) {
    return (
        <h2 className="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">
            {children}
        </h2>
    )
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <div className="space-y-1.5">
            <label className="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                {label}
            </label>
            {children}
        </div>
    )
}

function RadioGroup({
    name,
    value,
    onChange,
    options,
}: {
    name: string
    value: string
    onChange: (v: string) => void
    options: { label: string; value: string }[]
}) {
    return (
        <div className="flex flex-wrap gap-2">
            {options.map((opt) => (
                <button
                    key={opt.value}
                    type="button"
                    onClick={() => onChange(opt.value)}
                    className={`px-3 py-1.5 rounded-md text-sm font-medium border transition-colors ${
                        value === opt.value
                            ? "bg-indigo-600 border-indigo-600 text-white"
                            : "border-gray-200 dark:border-[#2a2a2e] text-gray-600 dark:text-gray-300 hover:border-indigo-400 dark:hover:border-indigo-500"
                    }`}
                >
                    {opt.label}
                </button>
            ))}
        </div>
    )
}

// ─── page ─────────────────────────────────────────────────────────────────────

export default function ConfiguracionFiscalPage() {
    const [loadingFiscal, setLoadingFiscal] = useState(true)
    const [loadingCert, setLoadingCert] = useState(true)
    const [savingFiscal, setSavingFiscal] = useState(false)
    const [savingCert, setSavingCert] = useState(false)
    const [testing, setTesting] = useState(false)

    const [fiscal, setFiscal] = useState<FiscalSetting>({
        cuit: "",
        razon_social: "",
        condicion_iva: "RESPONSABLE_INSCRIPTO",
        punto_venta: 1,
        tipo_comprobante_default: "B",
        ambiente: "TESTING",
        activo: true,
    })

    const [certStatus, setCertStatus] = useState<CertStatus | null>(null)
    const [certForm, setCertForm] = useState({
        certificate: "",
        private_key: "",
        certificate_alias: "",
        expires_at: "",
    })

    const [connectionResult, setConnectionResult] = useState<ConnectionResult | null>(null)

    const [confirmProduction, setConfirmProduction] = useState(false)

    // ── fetch on mount ──────────────────────────────────────────────────────

    useEffect(() => {
        fetchFiscal()
        fetchCert()
    }, [])

    async function fetchFiscal() {
        try {
            const res = await apiRequest("/arca/fiscal-settings")
            if (res.data) setFiscal(res.data)
        } catch {
            // no config yet — show empty form
        } finally {
            setLoadingFiscal(false)
        }
    }

    async function fetchCert() {
        try {
            const res = await apiRequest("/arca/certificate")
            if (res.data) setCertStatus(res.data)
        } catch {
            // ignore
        } finally {
            setLoadingCert(false)
        }
    }

    // ── save fiscal ─────────────────────────────────────────────────────────

    async function handleSaveFiscal(e: React.FormEvent) {
        e.preventDefault()

        if (fiscal.ambiente === "PRODUCTION" && !confirmProduction) {
            setConfirmProduction(true)
            return
        }

        setSavingFiscal(true)
        setConfirmProduction(false)
        try {
            const res = await apiRequest("/arca/fiscal-settings", {
                method: "POST",
                body: JSON.stringify(fiscal),
            })
            toast.success(res.message || "Configuración guardada.")
            fetchFiscal()
        } catch (err: any) {
            toast.error(err.message || "No se pudo guardar la configuración.")
        } finally {
            setSavingFiscal(false)
        }
    }

    // ── save certificate ────────────────────────────────────────────────────

    async function handleSaveCert(e: React.FormEvent) {
        e.preventDefault()
        setSavingCert(true)
        try {
            const payload: Record<string, string> = {
                certificate: certForm.certificate,
                private_key: certForm.private_key,
            }
            if (certForm.certificate_alias) payload.certificate_alias = certForm.certificate_alias
            if (certForm.expires_at) payload.expires_at = certForm.expires_at

            const res = await apiRequest("/arca/certificate", {
                method: "POST",
                body: JSON.stringify(payload),
            })
            toast.success(res.message || "Certificado guardado.")
            setCertStatus(res.data)
            setCertForm({ certificate: "", private_key: "", certificate_alias: "", expires_at: "" })
        } catch (err: any) {
            toast.error(err.message || "No se pudo guardar el certificado.")
        } finally {
            setSavingCert(false)
        }
    }

    // ── test connection ──────────────────────────────────────────────────────

    async function handleTestConnection() {
        setTesting(true)
        setConnectionResult(null)
        try {
            const res = await apiRequest("/arca/test-connection", { method: "POST" })
            setConnectionResult({ success: true, message: res.message, data: res.data })
        } catch (err: any) {
            setConnectionResult({ success: false, message: err.message || "No se pudo conectar con ARCA." })
        } finally {
            setTesting(false)
        }
    }

    // ── render ───────────────────────────────────────────────────────────────

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500 max-w-3xl">
                {/* Header */}
                <div>
                    <h1 className="text-2xl font-bold flex items-center gap-2">
                        <SlidersHorizontal className="w-6 h-6 text-indigo-500" />
                        Configuración Fiscal
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Gestioná los datos fiscales y el certificado ARCA para emitir comprobantes electrónicos.
                    </p>
                </div>

                {/* ── Fiscal Data Card ─────────────────────────────────────────── */}
                <form
                    onSubmit={handleSaveFiscal}
                    className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] shadow-sm p-6 space-y-6"
                >
                    <SectionTitle>Datos fiscales</SectionTitle>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Field label="Razón social">
                            <Input
                                value={fiscal.razon_social}
                                onChange={(e) => setFiscal({ ...fiscal, razon_social: e.target.value })}
                                placeholder="Ej: Vortex Tech S.A."
                                required
                                className="dark:bg-[#1F1F23]"
                            />
                        </Field>

                        <Field label="CUIT">
                            <Input
                                value={fiscal.cuit}
                                onChange={(e) => setFiscal({ ...fiscal, cuit: e.target.value })}
                                placeholder="20-12345678-9"
                                required
                                className="dark:bg-[#1F1F23]"
                            />
                        </Field>

                        <Field label="Punto de venta">
                            <Input
                                type="number"
                                min={1}
                                max={99999}
                                value={fiscal.punto_venta}
                                onChange={(e) => setFiscal({ ...fiscal, punto_venta: parseInt(e.target.value) || 1 })}
                                required
                                className="dark:bg-[#1F1F23]"
                            />
                        </Field>

                        <Field label="Estado">
                            <RadioGroup
                                name="activo"
                                value={fiscal.activo ? "true" : "false"}
                                onChange={(v) => setFiscal({ ...fiscal, activo: v === "true" })}
                                options={[
                                    { label: "Activo", value: "true" },
                                    { label: "Inactivo", value: "false" },
                                ]}
                            />
                        </Field>
                    </div>

                    <Field label="Condición frente al IVA">
                        <RadioGroup
                            name="condicion_iva"
                            value={fiscal.condicion_iva}
                            onChange={(v) => setFiscal({ ...fiscal, condicion_iva: v })}
                            options={[
                                { label: "Monotributo", value: "MONOTRIBUTO" },
                                { label: "Responsable Inscripto", value: "RESPONSABLE_INSCRIPTO" },
                                { label: "Exento", value: "EXENTO" },
                            ]}
                        />
                    </Field>

                    <Field label="Tipo de comprobante por defecto">
                        <RadioGroup
                            name="tipo_comprobante_default"
                            value={fiscal.tipo_comprobante_default}
                            onChange={(v) => setFiscal({ ...fiscal, tipo_comprobante_default: v })}
                            options={[
                                { label: "Factura A", value: "A" },
                                { label: "Factura B", value: "B" },
                                { label: "Factura C", value: "C" },
                            ]}
                        />
                    </Field>

                    <Field label="Ambiente ARCA">
                        <RadioGroup
                            name="ambiente"
                            value={fiscal.ambiente}
                            onChange={(v) => setFiscal({ ...fiscal, ambiente: v })}
                            options={[
                                { label: "Testing (Homologación)", value: "TESTING" },
                                { label: "Producción", value: "PRODUCTION" },
                            ]}
                        />
                        {fiscal.ambiente === "PRODUCTION" && (
                            <div className="flex items-start gap-2 mt-2 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-400">
                                <AlertTriangle className="w-4 h-4 mt-0.5 flex-shrink-0" />
                                <span>
                                    En modo <strong>PRODUCCIÓN</strong> las facturas emitidas serán reales y tendrán validez fiscal.
                                </span>
                            </div>
                        )}
                    </Field>

                    {/* Production confirmation */}
                    {confirmProduction && (
                        <div className="p-4 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 space-y-3">
                            <p className="text-sm font-semibold text-red-700 dark:text-red-400 flex items-center gap-2">
                                <AlertTriangle className="w-4 h-4" />
                                Confirmación requerida
                            </p>
                            <p className="text-sm text-red-600 dark:text-red-400">
                                Estás por cambiar a <strong>PRODUCCIÓN</strong>. A partir de este momento, las facturas emitidas
                                serán reales y tendrán validez fiscal.
                            </p>
                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    onClick={() => { setConfirmProduction(false); setSavingFiscal(true); handleSaveFiscal({ preventDefault: () => {} } as any) }}
                                    className="bg-red-600 hover:bg-red-700 text-white"
                                    size="sm"
                                >
                                    Confirmar cambio a PRODUCCIÓN
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        setConfirmProduction(false)
                                        setFiscal({ ...fiscal, ambiente: "TESTING" })
                                    }}
                                >
                                    Cancelar
                                </Button>
                            </div>
                        </div>
                    )}

                    <div className="flex items-center gap-3 pt-2">
                        <Button
                            type="submit"
                            disabled={savingFiscal || loadingFiscal}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2"
                        >
                            {savingFiscal ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                            Guardar configuración
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={testing}
                            onClick={handleTestConnection}
                            className="flex items-center gap-2"
                        >
                            {testing ? <Loader2 className="w-4 h-4 animate-spin" /> : <Wifi className="w-4 h-4" />}
                            Testear conexión ARCA
                        </Button>
                    </div>
                </form>

                {/* ── Certificate Card ─────────────────────────────────────────── */}
                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] shadow-sm p-6 space-y-6">
                    <SectionTitle>Certificado ARCA</SectionTitle>

                    {!loadingCert && certStatus && (
                        <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-2">
                            <div className="flex items-center gap-2 p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23] text-sm">
                                {certStatus.has_certificate
                                    ? <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    : <AlertCircle className="w-4 h-4 text-gray-400" />}
                                <span className={certStatus.has_certificate ? "text-emerald-600 dark:text-emerald-400" : "text-gray-500"}>
                                    {certStatus.has_certificate ? "Certificado cargado" : "Sin certificado"}
                                </span>
                            </div>
                            <div className="flex items-center gap-2 p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23] text-sm">
                                {certStatus.has_private_key
                                    ? <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    : <AlertCircle className="w-4 h-4 text-gray-400" />}
                                <span className={certStatus.has_private_key ? "text-emerald-600 dark:text-emerald-400" : "text-gray-500"}>
                                    {certStatus.has_private_key ? "Clave privada cargada" : "Sin clave privada"}
                                </span>
                            </div>
                            {certStatus.expires_at && (
                                <div className="flex items-center gap-2 p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23] text-sm">
                                    {certStatus.is_expired
                                        ? <ShieldAlert className="w-4 h-4 text-red-500" />
                                        : <ShieldCheck className="w-4 h-4 text-emerald-500" />}
                                    <span className={certStatus.is_expired ? "text-red-500" : "text-gray-600 dark:text-gray-300"}>
                                        Vto: {certStatus.expires_at}
                                    </span>
                                </div>
                            )}
                        </div>
                    )}

                    <div className="flex items-start gap-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900 text-sm text-blue-700 dark:text-blue-400">
                        <Info className="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <span>
                            El certificado y la clave privada se almacenan encriptados. No se muestran después de guardarse.
                            Para reemplazarlos, pegá los nuevos valores a continuación.
                        </span>
                    </div>

                    <form onSubmit={handleSaveCert} className="space-y-4">
                        <Field label="Certificado ARCA (PEM)">
                            <textarea
                                value={certForm.certificate}
                                onChange={(e) => setCertForm({ ...certForm, certificate: e.target.value })}
                                placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----"
                                required
                                rows={6}
                                className="w-full px-3 py-2 text-xs font-mono rounded-md border border-gray-200 dark:border-[#2a2a2e] bg-gray-50 dark:bg-[#1F1F23] text-gray-700 dark:text-gray-300 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </Field>

                        <Field label="Clave privada (PEM)">
                            <textarea
                                value={certForm.private_key}
                                onChange={(e) => setCertForm({ ...certForm, private_key: e.target.value })}
                                placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"
                                required
                                rows={6}
                                className="w-full px-3 py-2 text-xs font-mono rounded-md border border-gray-200 dark:border-[#2a2a2e] bg-gray-50 dark:bg-[#1F1F23] text-gray-700 dark:text-gray-300 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </Field>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Field label="Alias del certificado (opcional)">
                                <Input
                                    value={certForm.certificate_alias}
                                    onChange={(e) => setCertForm({ ...certForm, certificate_alias: e.target.value })}
                                    placeholder="Ej: cert-2026-prod"
                                    className="dark:bg-[#1F1F23]"
                                />
                            </Field>
                            <Field label="Fecha de vencimiento (opcional)">
                                <Input
                                    type="date"
                                    value={certForm.expires_at}
                                    onChange={(e) => setCertForm({ ...certForm, expires_at: e.target.value })}
                                    className="dark:bg-[#1F1F23]"
                                />
                            </Field>
                        </div>

                        <Button
                            type="submit"
                            disabled={savingCert}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2"
                        >
                            {savingCert
                                ? <Loader2 className="w-4 h-4 animate-spin" />
                                : certStatus?.has_certificate ? <RefreshCw className="w-4 h-4" /> : <UploadCloud className="w-4 h-4" />}
                            {certStatus?.has_certificate ? "Reemplazar certificado" : "Guardar certificado"}
                        </Button>
                    </form>
                </div>

                {/* ── Connection Status Card ───────────────────────────────────── */}
                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] shadow-sm p-6 space-y-4">
                    <div className="flex items-center justify-between">
                        <SectionTitle>Estado de conexión</SectionTitle>
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={testing}
                            onClick={handleTestConnection}
                            className="flex items-center gap-2"
                        >
                            {testing ? <Loader2 className="w-3 h-3 animate-spin" /> : <Wifi className="w-3 h-3" />}
                            Probar ahora
                        </Button>
                    </div>

                    {!connectionResult && !testing && (
                        <div className="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-[#1F1F23] text-sm text-gray-500">
                            <WifiOff className="w-5 h-5" />
                            <span>No se ha probado la conexión todavía.</span>
                        </div>
                    )}

                    {testing && (
                        <div className="flex items-center gap-3 p-4 rounded-lg bg-gray-50 dark:bg-[#1F1F23] text-sm text-gray-500">
                            <Loader2 className="w-5 h-5 animate-spin" />
                            <span>Conectando con ARCA…</span>
                        </div>
                    )}

                    {connectionResult && (
                        <div
                            className={`p-4 rounded-lg border text-sm space-y-2 ${
                                connectionResult.success
                                    ? "bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800"
                                    : "bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-800"
                            }`}
                        >
                            <div className="flex items-center gap-2 font-medium">
                                {connectionResult.success
                                    ? <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    : <AlertCircle className="w-4 h-4 text-red-500" />}
                                <span className={connectionResult.success ? "text-emerald-700 dark:text-emerald-400" : "text-red-700 dark:text-red-400"}>
                                    {connectionResult.success ? "Conexión exitosa" : "Error de conexión"}
                                </span>
                            </div>
                            <p className="text-gray-600 dark:text-gray-400">{connectionResult.message}</p>
                            {connectionResult.data && (
                                <div className="flex flex-wrap gap-3 pt-1 text-xs text-gray-500">
                                    <span>Ambiente: <strong>{connectionResult.data.environment}</strong></span>
                                    <span>·</span>
                                    <span>Vencimiento token: <strong>{new Date(connectionResult.data.expiration).toLocaleString("es-AR")}</strong></span>
                                    <span>·</span>
                                    <span>{connectionResult.data.cached ? "Token reutilizado" : "Token nuevo"}</span>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </Layout>
    )
}
