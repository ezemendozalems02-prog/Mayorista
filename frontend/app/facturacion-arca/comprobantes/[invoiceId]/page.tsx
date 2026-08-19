"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import Link from "next/link"
import Layout from "@/components/kokonutui/layout"
import { apiRequest, API_URL } from "@/lib/api"
import {
    FileText,
    ChevronLeft,
    CheckCircle2,
    AlertCircle,
    Clock,
    XCircle,
    Download,
    RefreshCw,
    Loader2,
    User,
    Building2,
    Receipt,
    ShieldCheck,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { LoadingScreen } from "@/components/ui/loading-screen"
import { toast } from "sonner"

// ─── types ────────────────────────────────────────────────────────────────────

interface InvoiceItem {
    id: number
    descripcion: string
    cantidad: string
    precio_unitario: string
    total: string
}

interface Invoice {
    id: number
    tipo_comprobante: string
    punto_venta: number
    numero_comprobante: number | null
    cliente_nombre: string
    cliente_documento: string | null
    cliente_condicion_iva: string | null
    subtotal: string
    iva: string
    total: string
    cae: string | null
    cae_vencimiento: string | null
    estado: "PENDIENTE" | "AUTORIZADO" | "RECHAZADO" | "ERROR"
    error_message: string | null
    created_at: string
    updated_at: string
    items: InvoiceItem[]
}

// ─── helpers ──────────────────────────────────────────────────────────────────

function pad(n: number | null, len: number) {
    return n !== null ? String(n).padStart(len, "0") : "—"
}

function StatusBadge({ estado }: { estado: Invoice["estado"] }) {
    switch (estado) {
        case "AUTORIZADO":
            return (
                <Badge className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-none flex items-center gap-1 text-sm px-3 py-1">
                    <CheckCircle2 className="w-4 h-4" /> Autorizado
                </Badge>
            )
        case "PENDIENTE":
            return (
                <Badge className="bg-gray-500/10 text-gray-600 dark:text-gray-400 border-none flex items-center gap-1 text-sm px-3 py-1">
                    <Clock className="w-4 h-4" /> Pendiente
                </Badge>
            )
        case "RECHAZADO":
            return (
                <Badge className="bg-red-500/10 text-red-600 dark:text-red-400 border-none flex items-center gap-1 text-sm px-3 py-1">
                    <XCircle className="w-4 h-4" /> Rechazado
                </Badge>
            )
        case "ERROR":
            return (
                <Badge className="bg-amber-500/10 text-amber-600 dark:text-amber-400 border-none flex items-center gap-1 text-sm px-3 py-1">
                    <AlertCircle className="w-4 h-4" /> Error
                </Badge>
            )
        default:
            return <Badge variant="outline">{estado}</Badge>
    }
}

function InfoRow({ label, value }: { label: string; value: React.ReactNode }) {
    return (
        <div className="flex justify-between items-center py-2.5 border-b border-gray-100 dark:border-[#1F1F23] last:border-0">
            <span className="text-sm text-gray-500">{label}</span>
            <span className="text-sm font-medium text-right">{value}</span>
        </div>
    )
}

function SectionCard({ title, icon: Icon, children }: { title: string; icon?: any; children: React.ReactNode }) {
    return (
        <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] shadow-sm p-6 space-y-2">
            <h2 className="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4 flex items-center gap-2">
                {Icon && <Icon className="w-4 h-4" />}
                {title}
            </h2>
            {children}
        </div>
    )
}

// ─── page ─────────────────────────────────────────────────────────────────────

export default function InvoiceDetailPage() {
    const params = useParams()
    const router = useRouter()
    const invoiceId = params?.invoiceId as string

    const [invoice, setInvoice] = useState<Invoice | null>(null)
    const [loading, setLoading] = useState(true)
    const [authorizing, setAuthorizing] = useState(false)
    const [generating, setGenerating] = useState(false)

    async function fetchInvoice() {
        setLoading(true)
        try {
            const res = await apiRequest(`/arca/invoices/${invoiceId}`)
            setInvoice(res.data)
        } catch (err: any) {
            toast.error(err.message || "No se pudo cargar el comprobante.")
            router.push("/facturacion-arca/comprobantes")
        } finally {
            setLoading(false)
        }
    }

    useEffect(() => {
        if (invoiceId) fetchInvoice()
    }, [invoiceId])

    async function handleAuthorize() {
        setAuthorizing(true)
        try {
            const res = await apiRequest(`/arca/invoices/${invoiceId}/authorize`, { method: "POST" })
            if (res.success) {
                toast.success("Factura autorizada por ARCA.")
                fetchInvoice()
            } else {
                toast.error(res.message || "ARCA rechazó la autorización.")
            }
        } catch (err: any) {
            toast.error(err.message || "No se pudo conectar con ARCA.")
        } finally {
            setAuthorizing(false)
        }
    }

    async function handleGeneratePdf() {
        setGenerating(true)
        try {
            await apiRequest(`/arca/invoices/${invoiceId}/generate-pdf`, { method: "POST" })
            toast.success("PDF generado. Descargando…")
            window.open(`${API_URL}/arca/invoices/${invoiceId}/download-pdf`, "_blank")
        } catch (err: any) {
            toast.error(err.message || "No se pudo generar el PDF.")
        } finally {
            setGenerating(false)
        }
    }

    function handleDownloadPdf() {
        window.open(`${API_URL}/arca/invoices/${invoiceId}/download-pdf`, "_blank")
    }

    if (loading) {
        return (
            <Layout>
                <div className="flex items-center justify-center h-64">
                    <LoadingScreen />
                </div>
            </Layout>
        )
    }

    if (!invoice) return null

    const isAuthorized = invoice.estado === "AUTORIZADO"
    const canAuthorize = invoice.estado === "PENDIENTE" || invoice.estado === "ERROR"
    const showIva = invoice.tipo_comprobante !== "C"

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500 max-w-4xl">
                {/* Back + Header */}
                <div>
                    <Link
                        href="/facturacion-arca/comprobantes"
                        className="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 mb-4 transition-colors"
                    >
                        <ChevronLeft className="w-4 h-4" /> Volver a Comprobantes
                    </Link>

                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="space-y-1">
                            <div className="flex items-center gap-3">
                                <h1 className="text-2xl font-bold flex items-center gap-2">
                                    <FileText className="w-6 h-6 text-indigo-500" />
                                    Factura {invoice.tipo_comprobante} —{" "}
                                    {pad(invoice.punto_venta, 4)}-{pad(invoice.numero_comprobante, 8)}
                                </h1>
                                <StatusBadge estado={invoice.estado} />
                            </div>
                            <p className="text-sm text-gray-500">
                                Emitida el {new Date(invoice.created_at).toLocaleDateString("es-AR", {
                                    day: "2-digit",
                                    month: "long",
                                    year: "numeric",
                                })}
                            </p>
                        </div>

                        {/* Actions */}
                        <div className="flex flex-wrap gap-2">
                            {canAuthorize && (
                                <Button
                                    onClick={handleAuthorize}
                                    disabled={authorizing}
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2"
                                >
                                    {authorizing
                                        ? <Loader2 className="w-4 h-4 animate-spin" />
                                        : <CheckCircle2 className="w-4 h-4" />}
                                    Emitir con ARCA
                                </Button>
                            )}
                            {isAuthorized && (
                                <>
                                    <Button
                                        variant="outline"
                                        onClick={handleGeneratePdf}
                                        disabled={generating}
                                        className="flex items-center gap-2"
                                    >
                                        {generating
                                            ? <Loader2 className="w-4 h-4 animate-spin" />
                                            : <RefreshCw className="w-4 h-4" />}
                                        Generar PDF
                                    </Button>
                                    <Button
                                        onClick={handleDownloadPdf}
                                        className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2"
                                    >
                                        <Download className="w-4 h-4" /> Descargar PDF
                                    </Button>
                                </>
                            )}
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* ── Client ─────────────────────────────────────────────── */}
                    <SectionCard title="Datos del receptor" icon={User}>
                        <InfoRow label="Nombre / Razón social" value={invoice.cliente_nombre} />
                        <InfoRow
                            label="Documento"
                            value={invoice.cliente_documento || <span className="text-gray-400">Consumidor Final</span>}
                        />
                        <InfoRow
                            label="Condición IVA"
                            value={
                                invoice.cliente_condicion_iva
                                    ? invoice.cliente_condicion_iva.replace(/_/g, " ")
                                    : <span className="text-gray-400">—</span>
                            }
                        />
                    </SectionCard>

                    {/* ── Invoice meta ─────────────────────────────────────────── */}
                    <SectionCard title="Datos del comprobante" icon={Receipt}>
                        <InfoRow label="Tipo" value={`Factura ${invoice.tipo_comprobante}`} />
                        <InfoRow label="Punto de venta" value={pad(invoice.punto_venta, 4)} />
                        <InfoRow label="Número" value={pad(invoice.numero_comprobante, 8)} />
                        <InfoRow
                            label="Fecha de emisión"
                            value={new Date(invoice.created_at).toLocaleDateString("es-AR")}
                        />
                        <InfoRow label="Estado" value={<StatusBadge estado={invoice.estado} />} />
                    </SectionCard>
                </div>

                {/* ── Items table ─────────────────────────────────────────────── */}
                <SectionCard title="Detalle de productos / servicios" icon={FileText}>
                    <Table>
                        <TableHeader className="bg-gray-50 dark:bg-[#1F1F23]">
                            <TableRow>
                                <TableHead>Descripción</TableHead>
                                <TableHead className="text-right">Cantidad</TableHead>
                                <TableHead className="text-right">Precio unitario</TableHead>
                                <TableHead className="text-right">Subtotal</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {invoice.items && invoice.items.length > 0 ? (
                                invoice.items.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell className="text-sm">{item.descripcion}</TableCell>
                                        <TableCell className="text-right text-sm">
                                            {Number(item.cantidad).toLocaleString("es-AR", { minimumFractionDigits: 2 })}
                                        </TableCell>
                                        <TableCell className="text-right text-sm">
                                            $ {Number(item.precio_unitario).toLocaleString("es-AR", { minimumFractionDigits: 2 })}
                                        </TableCell>
                                        <TableCell className="text-right text-sm font-medium">
                                            $ {Number(item.total).toLocaleString("es-AR", { minimumFractionDigits: 2 })}
                                        </TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={4} className="text-center py-8 text-gray-500">
                                        Sin ítems registrados.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    {/* Totals */}
                    <div className="flex justify-end mt-4">
                        <div className="w-full max-w-xs space-y-1 text-sm">
                            <div className="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span>$ {Number(invoice.subtotal).toLocaleString("es-AR", { minimumFractionDigits: 2 })}</span>
                            </div>
                            {showIva && (
                                <div className="flex justify-between text-gray-500">
                                    <span>IVA (21%)</span>
                                    <span>$ {Number(invoice.iva).toLocaleString("es-AR", { minimumFractionDigits: 2 })}</span>
                                </div>
                            )}
                            <div className="flex justify-between font-bold text-base border-t border-gray-200 dark:border-[#1F1F23] pt-2 mt-2">
                                <span>Total</span>
                                <span>$ {Number(invoice.total).toLocaleString("es-AR", { minimumFractionDigits: 2 })}</span>
                            </div>
                            {!showIva && (
                                <p className="text-xs text-gray-400">* IVA incluido en el total (no discriminado)</p>
                            )}
                        </div>
                    </div>
                </SectionCard>

                {/* ── ARCA Data ──────────────────────────────────────────────── */}
                {isAuthorized && invoice.cae && (
                    <SectionCard title="Datos ARCA" icon={ShieldCheck}>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-0">
                            <InfoRow label="CAE N°" value={<span className="font-mono text-xs">{invoice.cae}</span>} />
                            <InfoRow
                                label="Vencimiento CAE"
                                value={
                                    invoice.cae_vencimiento
                                        ? new Date(invoice.cae_vencimiento).toLocaleDateString("es-AR")
                                        : "—"
                                }
                            />
                            <InfoRow
                                label="Fecha de autorización"
                                value={new Date(invoice.updated_at).toLocaleString("es-AR")}
                            />
                        </div>
                        <div className="mt-3 flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                            <CheckCircle2 className="w-4 h-4" />
                            Comprobante autorizado por ARCA
                        </div>
                    </SectionCard>
                )}

                {/* ── Error / Rejection ──────────────────────────────────────── */}
                {(invoice.estado === "ERROR" || invoice.estado === "RECHAZADO") && invoice.error_message && (
                    <div className="p-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/20 space-y-3">
                        <div className="flex items-center gap-2 text-red-700 dark:text-red-400 font-semibold text-sm">
                            <AlertCircle className="w-4 h-4" />
                            {invoice.estado === "RECHAZADO" ? "Factura rechazada por ARCA" : "Error al procesar"}
                        </div>
                        <p className="text-sm text-red-600 dark:text-red-400">{invoice.error_message}</p>
                        <Button
                            onClick={handleAuthorize}
                            disabled={authorizing}
                            size="sm"
                            className="bg-red-600 hover:bg-red-700 text-white flex items-center gap-2"
                        >
                            {authorizing ? <Loader2 className="w-3 h-3 animate-spin" /> : <RefreshCw className="w-3 h-3" />}
                            Reintentar autorización
                        </Button>
                    </div>
                )}
            </div>
        </Layout>
    )
}
