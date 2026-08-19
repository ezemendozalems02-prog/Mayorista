"use client"

import { useEffect, useRef, useState } from "react"
import { useRouter } from "next/navigation"
import Layout from "@/components/kokonutui/layout"
import { apiRequest, API_URL } from "@/lib/api"
import {
    FilePlus,
    Plus,
    Trash2,
    Loader2,
    CheckCircle2,
    AlertCircle,
    Download,
    FileText,
    X,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { toast } from "sonner"

// ─── types ────────────────────────────────────────────────────────────────────

interface InvoiceItem {
    id: number
    descripcion: string
    cantidad: string
    precio_unitario: string
}

interface AuthorizeResult {
    invoice: {
        id: number
        tipo_comprobante: string
        punto_venta: number
        numero_comprobante: number
        cae: string
        cae_vencimiento: string
        total: number
        estado: string
    }
}

// ─── helpers ──────────────────────────────────────────────────────────────────

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

function Select({
    value,
    onChange,
    options,
    placeholder,
    required,
}: {
    value: string
    onChange: (v: string) => void
    options: { label: string; value: string }[]
    placeholder?: string
    required?: boolean
}) {
    return (
        <select
            value={value}
            onChange={(e) => onChange(e.target.value)}
            required={required}
            className="w-full px-3 py-2 text-sm rounded-md border border-gray-200 dark:border-[#2a2a2e] bg-white dark:bg-[#1F1F23] text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
            {placeholder && <option value="">{placeholder}</option>}
            {options.map((o) => (
                <option key={o.value} value={o.value}>
                    {o.label}
                </option>
            ))}
        </select>
    )
}

function RadioGroup({
    value,
    onChange,
    options,
}: {
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
                            : "border-gray-200 dark:border-[#2a2a2e] text-gray-600 dark:text-gray-300 hover:border-indigo-400"
                    }`}
                >
                    {opt.label}
                </button>
            ))}
        </div>
    )
}

function SectionCard({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] shadow-sm p-6 space-y-4">
            <h2 className="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {title}
            </h2>
            {children}
        </div>
    )
}

// ─── success modal ─────────────────────────────────────────────────────────────

function SuccessModal({
    invoice,
    onClose,
    onNewInvoice,
}: {
    invoice: AuthorizeResult["invoice"]
    onClose: () => void
    onNewInvoice: () => void
}) {
    const router = useRouter()
    const token = typeof window !== "undefined" ? localStorage.getItem("auth_token") : null

    function handleDownload() {
        window.open(
            `${API_URL}/arca/invoices/${invoice.id}/download-pdf?token=${token}`,
            "_blank",
        )
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div className="bg-white dark:bg-[#0F0F12] rounded-2xl border border-gray-200 dark:border-[#1F1F23] shadow-2xl w-full max-w-md mx-4 p-6 space-y-5">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/40 flex items-center justify-center">
                            <CheckCircle2 className="w-5 h-5 text-emerald-500" />
                        </div>
                        <h3 className="text-base font-semibold">Factura autorizada correctamente</h3>
                    </div>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <X className="w-5 h-5" />
                    </button>
                </div>

                <div className="rounded-lg bg-gray-50 dark:bg-[#1F1F23] border border-gray-100 dark:border-[#2a2a2e] divide-y divide-gray-100 dark:divide-[#2a2a2e] text-sm">
                    {[
                        ["Tipo", `Factura ${invoice.tipo_comprobante}`],
                        ["Punto de venta", String(invoice.punto_venta).padStart(4, "0")],
                        ["Número", String(invoice.numero_comprobante).padStart(8, "0")],
                        ["CAE", invoice.cae],
                        ["Vencimiento CAE", invoice.cae_vencimiento ? new Date(invoice.cae_vencimiento).toLocaleDateString("es-AR") : "—"],
                        ["Total", `$ ${Number(invoice.total).toLocaleString("es-AR", { minimumFractionDigits: 2 })}`],
                    ].map(([k, v]) => (
                        <div key={k} className="flex justify-between px-4 py-2.5">
                            <span className="text-gray-500">{k}</span>
                            <span className="font-medium">{v}</span>
                        </div>
                    ))}
                </div>

                <div className="flex flex-col gap-2 pt-1">
                    <Button
                        onClick={handleDownload}
                        className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2 w-full"
                    >
                        <Download className="w-4 h-4" /> Descargar PDF
                    </Button>
                    <Button
                        variant="outline"
                        onClick={() => router.push(`/facturacion-arca/comprobantes/${invoice.id}`)}
                        className="flex items-center gap-2 w-full"
                    >
                        <FileText className="w-4 h-4" /> Ver comprobante
                    </Button>
                    <Button variant="ghost" onClick={onNewInvoice} className="w-full text-gray-500">
                        Crear otra factura
                    </Button>
                </div>
            </div>
        </div>
    )
}

// ─── error modal ───────────────────────────────────────────────────────────────

function ErrorModal({ message, onClose }: { message: string; onClose: () => void }) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div className="bg-white dark:bg-[#0F0F12] rounded-2xl border border-gray-200 dark:border-[#1F1F23] shadow-2xl w-full max-w-md mx-4 p-6 space-y-4">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/40 flex items-center justify-center">
                        <AlertCircle className="w-5 h-5 text-red-500" />
                    </div>
                    <h3 className="text-base font-semibold">Error al autorizar</h3>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-400">{message}</p>
                <Button onClick={onClose} variant="outline" className="w-full">
                    Cerrar
                </Button>
            </div>
        </div>
    )
}

// ─── main page ─────────────────────────────────────────────────────────────────

export default function NuevaFacturaPage() {
    const [submitting, setSubmitting] = useState(false)
    const [successData, setSuccessData] = useState<AuthorizeResult["invoice"] | null>(null)
    const [errorMsg, setErrorMsg] = useState<string | null>(null)

    const [clienteNombre, setClienteNombre] = useState("")
    const [clienteDocTipo, setClienteDocTipo] = useState("consumidor_final")
    const [clienteDocumento, setClienteDocumento] = useState("")
    const [clienteCondicionIva, setClienteCondicionIva] = useState("CONSUMIDOR_FINAL")
    const [tipoComprobante, setTipoComprobante] = useState("B")

    const nextId = useRef(1)
    const [items, setItems] = useState<InvoiceItem[]>([
        { id: nextId.current++, descripcion: "", cantidad: "1", precio_unitario: "" },
    ])

    // ── computed totals ──────────────────────────────────────────────────────

    const showIva = tipoComprobante !== "C"

    const subtotal = items.reduce((acc, item) => {
        const qty = parseFloat(item.cantidad) || 0
        const price = parseFloat(item.precio_unitario) || 0
        return acc + qty * price
    }, 0)

    const iva = showIva ? subtotal * 0.21 : 0
    const total = subtotal + iva

    // ── items management ─────────────────────────────────────────────────────

    function addItem() {
        setItems([...items, { id: nextId.current++, descripcion: "", cantidad: "1", precio_unitario: "" }])
    }

    function removeItem(id: number) {
        if (items.length === 1) return
        setItems(items.filter((i) => i.id !== id))
    }

    function updateItem(id: number, field: keyof InvoiceItem, value: string) {
        setItems(items.map((i) => (i.id === id ? { ...i, [field]: value } : i)))
    }

    // ── submit ───────────────────────────────────────────────────────────────

    async function buildPayload(draft: boolean = false) {
        return {
            cliente_nombre: clienteNombre,
            cliente_documento: clienteDocTipo !== "consumidor_final" ? clienteDocumento : undefined,
            cliente_condicion_iva: clienteCondicionIva,
            tipo_comprobante: tipoComprobante,
            items: items.map((i) => ({
                descripcion: i.descripcion,
                cantidad: parseFloat(i.cantidad),
                precio_unitario: parseFloat(i.precio_unitario),
            })),
        }
    }

    async function handleSaveDraft(e: React.FormEvent) {
        e.preventDefault()
        setSubmitting(true)
        try {
            const payload = await buildPayload(true)
            const res = await apiRequest("/arca/invoices", {
                method: "POST",
                body: JSON.stringify(payload),
            })
            toast.success("Borrador guardado correctamente.")
        } catch (err: any) {
            toast.error(err.message || "No se pudo guardar el borrador.")
        } finally {
            setSubmitting(false)
        }
    }

    async function handleEmitir(e: React.FormEvent) {
        e.preventDefault()
        setSubmitting(true)
        setErrorMsg(null)
        try {
            const payload = await buildPayload()

            // 1. Create draft
            const draftRes = await apiRequest("/arca/invoices", {
                method: "POST",
                body: JSON.stringify(payload),
            })
            const invoiceId = draftRes.data?.id

            if (!invoiceId) throw new Error("No se pudo crear el borrador de factura.")

            // 2. Authorize
            const authRes = await apiRequest(`/arca/invoices/${invoiceId}/authorize`, {
                method: "POST",
            })

            if (authRes.success && authRes.data?.estado === "AUTORIZADO") {
                setSuccessData(authRes.data)
            } else {
                setErrorMsg(authRes.message || "ARCA rechazó la factura.")
            }
        } catch (err: any) {
            setErrorMsg(err.message || "No se pudo conectar con ARCA.")
        } finally {
            setSubmitting(false)
        }
    }

    function resetForm() {
        setClienteNombre("")
        setClienteDocTipo("consumidor_final")
        setClienteDocumento("")
        setClienteCondicionIva("CONSUMIDOR_FINAL")
        setTipoComprobante("B")
        setItems([{ id: nextId.current++, descripcion: "", cantidad: "1", precio_unitario: "" }])
        setSuccessData(null)
        setErrorMsg(null)
    }

    // ─────────────────────────────────────────────────────────────────────────

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500 max-w-4xl">
                <div>
                    <h1 className="text-2xl font-bold flex items-center gap-2">
                        <FilePlus className="w-6 h-6 text-indigo-500" />
                        Nueva Factura
                    </h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Completá los datos y emitila directamente con ARCA.
                    </p>
                </div>

                <form onSubmit={handleEmitir} className="space-y-6">
                    {/* ── Client ─────────────────────────────────────────────── */}
                    <SectionCard title="Datos del receptor">
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Field label="Nombre / Razón social">
                                <Input
                                    value={clienteNombre}
                                    onChange={(e) => setClienteNombre(e.target.value)}
                                    placeholder="Ej: Juan García"
                                    required
                                    className="dark:bg-[#1F1F23]"
                                />
                            </Field>

                            <Field label="Tipo de documento">
                                <Select
                                    value={clienteDocTipo}
                                    onChange={(v) => {
                                        setClienteDocTipo(v)
                                        if (v === "consumidor_final") {
                                            setClienteCondicionIva("CONSUMIDOR_FINAL")
                                            setClienteDocumento("")
                                        }
                                    }}
                                    options={[
                                        { label: "Consumidor Final", value: "consumidor_final" },
                                        { label: "DNI", value: "dni" },
                                        { label: "CUIT", value: "cuit" },
                                    ]}
                                />
                            </Field>

                            {clienteDocTipo !== "consumidor_final" && (
                                <Field label={clienteDocTipo === "cuit" ? "CUIT" : "DNI"}>
                                    <Input
                                        value={clienteDocumento}
                                        onChange={(e) => setClienteDocumento(e.target.value)}
                                        placeholder={clienteDocTipo === "cuit" ? "20-12345678-9" : "12345678"}
                                        required
                                        className="dark:bg-[#1F1F23]"
                                    />
                                </Field>
                            )}

                            <Field label="Condición frente al IVA">
                                <Select
                                    value={clienteCondicionIva}
                                    onChange={setClienteCondicionIva}
                                    options={[
                                        { label: "Consumidor Final", value: "CONSUMIDOR_FINAL" },
                                        { label: "Monotributo", value: "MONOTRIBUTO" },
                                        { label: "Responsable Inscripto", value: "RESPONSABLE_INSCRIPTO" },
                                        { label: "Exento", value: "EXENTO" },
                                    ]}
                                />
                            </Field>
                        </div>
                    </SectionCard>

                    {/* ── Invoice meta ─────────────────────────────────────────── */}
                    <SectionCard title="Datos del comprobante">
                        <div className="space-y-4">
                            <Field label="Tipo de comprobante">
                                <RadioGroup
                                    value={tipoComprobante}
                                    onChange={setTipoComprobante}
                                    options={[
                                        { label: "Factura A", value: "A" },
                                        { label: "Factura B", value: "B" },
                                        { label: "Factura C", value: "C" },
                                    ]}
                                />
                                {tipoComprobante === "C" && (
                                    <p className="text-xs text-gray-500 mt-1">
                                        Factura C: IVA incluido en el total (no discriminado).
                                    </p>
                                )}
                                {tipoComprobante === "A" && (
                                    <p className="text-xs text-gray-500 mt-1">
                                        Factura A: solo para Responsables Inscriptos. IVA 21% discriminado.
                                    </p>
                                )}
                            </Field>
                        </div>
                    </SectionCard>

                    {/* ── Items ────────────────────────────────────────────────── */}
                    <SectionCard title="Detalle de productos / servicios">
                        <div className="space-y-2">
                            {/* Header row (desktop only) */}
                            <div className="hidden sm:grid grid-cols-12 gap-2 text-xs font-medium text-gray-400 uppercase tracking-wide px-1">
                                <span className="col-span-6">Descripción</span>
                                <span className="col-span-2 text-right">Cantidad</span>
                                <span className="col-span-3 text-right">Precio unitario</span>
                                <span className="col-span-1" />
                            </div>

                            {items.map((item) => (
                                <div key={item.id} className="grid grid-cols-12 gap-2 items-center">
                                    <div className="col-span-12 sm:col-span-6">
                                        <Input
                                            value={item.descripcion}
                                            onChange={(e) => updateItem(item.id, "descripcion", e.target.value)}
                                            placeholder="Descripción del producto o servicio"
                                            required
                                            className="dark:bg-[#1F1F23] text-sm"
                                        />
                                    </div>
                                    <div className="col-span-4 sm:col-span-2">
                                        <Input
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            value={item.cantidad}
                                            onChange={(e) => updateItem(item.id, "cantidad", e.target.value)}
                                            placeholder="Cant."
                                            required
                                            className="dark:bg-[#1F1F23] text-sm text-right"
                                        />
                                    </div>
                                    <div className="col-span-6 sm:col-span-3">
                                        <Input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={item.precio_unitario}
                                            onChange={(e) => updateItem(item.id, "precio_unitario", e.target.value)}
                                            placeholder="$ Precio"
                                            required
                                            className="dark:bg-[#1F1F23] text-sm text-right"
                                        />
                                    </div>
                                    <div className="col-span-2 sm:col-span-1 flex justify-end">
                                        <button
                                            type="button"
                                            onClick={() => removeItem(item.id)}
                                            disabled={items.length === 1}
                                            className="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 disabled:opacity-30 transition-colors"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            ))}

                            <button
                                type="button"
                                onClick={addItem}
                                className="flex items-center gap-1.5 text-sm text-indigo-500 hover:text-indigo-600 mt-2 transition-colors"
                            >
                                <Plus className="w-4 h-4" /> Agregar ítem
                            </button>
                        </div>
                    </SectionCard>

                    {/* ── Totals ───────────────────────────────────────────────── */}
                    <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] shadow-sm p-6">
                        <div className="flex justify-end">
                            <div className="w-full max-w-xs space-y-1 text-sm">
                                <div className="flex justify-between text-gray-500">
                                    <span>Subtotal</span>
                                    <span>$ {subtotal.toLocaleString("es-AR", { minimumFractionDigits: 2 })}</span>
                                </div>
                                {showIva && (
                                    <div className="flex justify-between text-gray-500">
                                        <span>IVA (21%)</span>
                                        <span>$ {iva.toLocaleString("es-AR", { minimumFractionDigits: 2 })}</span>
                                    </div>
                                )}
                                <div className="flex justify-between font-bold text-base border-t border-gray-200 dark:border-[#1F1F23] pt-2 mt-2">
                                    <span>Total</span>
                                    <span>$ {total.toLocaleString("es-AR", { minimumFractionDigits: 2 })}</span>
                                </div>
                                {tipoComprobante === "C" && (
                                    <p className="text-xs text-gray-400 pt-1">* IVA incluido (no discriminado)</p>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* ── Actions ──────────────────────────────────────────────── */}
                    <div className="flex flex-col sm:flex-row items-center gap-3 pb-8">
                        <Button
                            type="submit"
                            disabled={submitting}
                            className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2 w-full sm:w-auto"
                        >
                            {submitting ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin" />
                                    Emitiendo con ARCA…
                                </>
                            ) : (
                                <>
                                    <CheckCircle2 className="w-4 h-4" />
                                    Emitir con ARCA
                                </>
                            )}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={submitting}
                            onClick={handleSaveDraft}
                            className="flex items-center gap-2 w-full sm:w-auto"
                        >
                            <FilePlus className="w-4 h-4" />
                            Guardar borrador
                        </Button>
                    </div>
                </form>

                {/* ── Modals ─────────────────────────────────────────────────── */}
                {successData && (
                    <SuccessModal
                        invoice={successData}
                        onClose={() => setSuccessData(null)}
                        onNewInvoice={resetForm}
                    />
                )}
                {errorMsg && (
                    <ErrorModal message={errorMsg} onClose={() => setErrorMsg(null)} />
                )}
            </div>
        </Layout>
    )
}
