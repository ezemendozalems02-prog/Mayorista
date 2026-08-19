"use client"

import { useEffect, useState, useCallback } from "react"
import Link from "next/link"
import Layout from "@/components/kokonutui/layout"
import { apiRequest, API_URL } from "@/lib/api"
import {
    FileText,
    Search,
    Filter,
    Download,
    Eye,
    CheckCircle2,
    AlertCircle,
    Clock,
    XCircle,
    Loader2,
    MoreVertical,
    Copy,
    RefreshCw,
    FilePlus,
    ChevronLeft,
    ChevronRight,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { LoadingScreen } from "@/components/ui/loading-screen"
import { toast } from "sonner"

// ─── types ────────────────────────────────────────────────────────────────────

interface Invoice {
    id: number
    tipo_comprobante: string
    punto_venta: number
    numero_comprobante: number
    cliente_nombre: string
    cliente_documento: string | null
    subtotal: string
    iva: string
    total: string
    cae: string | null
    cae_vencimiento: string | null
    estado: "PENDIENTE" | "AUTORIZADO" | "RECHAZADO" | "ERROR"
    created_at: string
}

interface PaginationMeta {
    current_page: number
    last_page: number
    total: number
    per_page: number
}

// ─── helpers ──────────────────────────────────────────────────────────────────

function StatusBadge({ estado }: { estado: Invoice["estado"] }) {
    switch (estado) {
        case "AUTORIZADO":
            return (
                <Badge className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-none flex items-center gap-1">
                    <CheckCircle2 className="w-3 h-3" /> Autorizado
                </Badge>
            )
        case "PENDIENTE":
            return (
                <Badge className="bg-gray-500/10 text-gray-600 dark:text-gray-400 border-none flex items-center gap-1">
                    <Clock className="w-3 h-3" /> Pendiente
                </Badge>
            )
        case "RECHAZADO":
            return (
                <Badge className="bg-red-500/10 text-red-600 dark:text-red-400 border-none flex items-center gap-1">
                    <XCircle className="w-3 h-3" /> Rechazado
                </Badge>
            )
        case "ERROR":
            return (
                <Badge className="bg-amber-500/10 text-amber-600 dark:text-amber-400 border-none flex items-center gap-1">
                    <AlertCircle className="w-3 h-3" /> Error
                </Badge>
            )
        default:
            return <Badge variant="outline">{estado}</Badge>
    }
}

function pad(n: number, len: number) {
    return String(n).padStart(len, "0")
}

// ─── page ─────────────────────────────────────────────────────────────────────

export default function ComprobantesPage() {
    const [invoices, setInvoices] = useState<Invoice[]>([])
    const [meta, setMeta] = useState<PaginationMeta | null>(null)
    const [loading, setLoading] = useState(true)
    const [authorizing, setAuthorizing] = useState<number | null>(null)
    const [generating, setGenerating] = useState<number | null>(null)

    const [page, setPage] = useState(1)
    const [showFilters, setShowFilters] = useState(false)

    const [filters, setFilters] = useState({
        status: "",
        type: "",
        date_from: "",
        date_to: "",
        client: "",
        number: "",
    })

    const [search, setSearch] = useState("")

    const fetchInvoices = useCallback(async (currentPage: number) => {
        setLoading(true)
        try {
            const params = new URLSearchParams()
            params.set("page", String(currentPage))
            params.set("per_page", "20")
            if (filters.status) params.set("status", filters.status)
            if (filters.type) params.set("type", filters.type)
            if (filters.date_from) params.set("date_from", filters.date_from)
            if (filters.date_to) params.set("date_to", filters.date_to)
            if (filters.client) params.set("client", filters.client)
            if (filters.number) params.set("number", filters.number)
            if (search) params.set("client", search)

            const res = await apiRequest(`/arca/invoices?${params.toString()}`)
            setInvoices(res.data?.data ?? [])
            if (res.data) {
                setMeta({
                    current_page: res.data.current_page,
                    last_page: res.data.last_page,
                    total: res.data.total,
                    per_page: res.data.per_page,
                })
            }
        } catch (err: any) {
            toast.error(err.message || "No se pudieron cargar los comprobantes.")
        } finally {
            setLoading(false)
        }
    }, [filters, search])

    useEffect(() => {
        fetchInvoices(page)
    }, [page, fetchInvoices])

    async function handleAuthorize(invoiceId: number) {
        setAuthorizing(invoiceId)
        try {
            const res = await apiRequest(`/arca/invoices/${invoiceId}/authorize`, { method: "POST" })
            if (res.success) {
                toast.success("Factura autorizada por ARCA.")
                fetchInvoices(page)
            } else {
                toast.error(res.message || "ARCA rechazó la autorización.")
            }
        } catch (err: any) {
            toast.error(err.message || "No se pudo conectar con ARCA.")
        } finally {
            setAuthorizing(null)
        }
    }

    async function handleGeneratePdf(invoiceId: number) {
        setGenerating(invoiceId)
        try {
            await apiRequest(`/arca/invoices/${invoiceId}/generate-pdf`, { method: "POST" })
            toast.success("PDF generado. Descargando…")
            const token = localStorage.getItem("auth_token")
            window.open(`${API_URL}/arca/invoices/${invoiceId}/download-pdf`, "_blank")
        } catch (err: any) {
            toast.error(err.message || "No se pudo generar el PDF.")
        } finally {
            setGenerating(null)
        }
    }

    function handleDownloadPdf(invoiceId: number) {
        const token = localStorage.getItem("auth_token")
        window.open(`${API_URL}/arca/invoices/${invoiceId}/download-pdf`, "_blank")
    }

    function applyFilters() {
        setPage(1)
        fetchInvoices(1)
    }

    function clearFilters() {
        setFilters({ status: "", type: "", date_from: "", date_to: "", client: "", number: "" })
        setSearch("")
        setPage(1)
    }

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500">
                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-2">
                            <FileText className="w-6 h-6 text-indigo-500" /> Comprobantes
                        </h1>
                        <p className="text-sm text-gray-500">Historial de facturas electrónicas ARCA</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setShowFilters(!showFilters)}
                            className="flex items-center gap-2"
                        >
                            <Filter className="w-4 h-4" />
                            Filtros
                        </Button>
                        <Link href="/facturacion-arca/nueva">
                            <Button className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2">
                                <FilePlus className="w-4 h-4" /> Nueva Factura
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Filters panel */}
                {showFilters && (
                    <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] p-4 space-y-4">
                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Estado</label>
                                <select
                                    value={filters.status}
                                    onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                                    className="w-full px-2 py-1.5 text-sm rounded-md border border-gray-200 dark:border-[#2a2a2e] bg-white dark:bg-[#1F1F23] text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="AUTORIZADO">Autorizado</option>
                                    <option value="RECHAZADO">Rechazado</option>
                                    <option value="ERROR">Error</option>
                                </select>
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Tipo</label>
                                <select
                                    value={filters.type}
                                    onChange={(e) => setFilters({ ...filters, type: e.target.value })}
                                    className="w-full px-2 py-1.5 text-sm rounded-md border border-gray-200 dark:border-[#2a2a2e] bg-white dark:bg-[#1F1F23] text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                </select>
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Desde</label>
                                <Input
                                    type="date"
                                    value={filters.date_from}
                                    onChange={(e) => setFilters({ ...filters, date_from: e.target.value })}
                                    className="dark:bg-[#1F1F23] text-sm h-8"
                                />
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Hasta</label>
                                <Input
                                    type="date"
                                    value={filters.date_to}
                                    onChange={(e) => setFilters({ ...filters, date_to: e.target.value })}
                                    className="dark:bg-[#1F1F23] text-sm h-8"
                                />
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Cliente</label>
                                <Input
                                    value={filters.client}
                                    onChange={(e) => setFilters({ ...filters, client: e.target.value })}
                                    placeholder="Nombre..."
                                    className="dark:bg-[#1F1F23] text-sm h-8"
                                />
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Número</label>
                                <Input
                                    type="number"
                                    value={filters.number}
                                    onChange={(e) => setFilters({ ...filters, number: e.target.value })}
                                    placeholder="N°..."
                                    className="dark:bg-[#1F1F23] text-sm h-8"
                                />
                            </div>
                        </div>
                        <div className="flex gap-2">
                            <Button size="sm" onClick={applyFilters} className="bg-indigo-600 hover:bg-indigo-700 text-white">
                                Aplicar filtros
                            </Button>
                            <Button size="sm" variant="outline" onClick={clearFilters}>
                                Limpiar
                            </Button>
                        </div>
                    </div>
                )}

                {/* Table */}
                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-gray-200 dark:border-[#1F1F23]">
                        <div className="relative max-w-sm">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <Input
                                placeholder="Buscar por cliente..."
                                className="pl-9 dark:bg-[#1F1F23]"
                                value={search}
                                onChange={(e) => { setSearch(e.target.value); setPage(1) }}
                            />
                        </div>
                    </div>

                    <div className="relative min-h-[300px]">
                        {loading && (
                            <div className="absolute inset-0 bg-white/50 dark:bg-black/20 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-b-xl">
                                <LoadingScreen />
                            </div>
                        )}

                        <Table>
                            <TableHeader className="bg-gray-50 dark:bg-[#1F1F23]">
                                <TableRow>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>N° Comprobante</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead className="text-right">Total</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>CAE</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.length === 0 && !loading ? (
                                    <TableRow>
                                        <TableCell colSpan={9} className="text-center py-20 text-gray-500">
                                            <div className="flex flex-col items-center gap-2">
                                                <FileText className="w-10 h-10 text-gray-300 dark:text-gray-700" />
                                                <span>Todavía no hay comprobantes emitidos.</span>
                                                <Link href="/facturacion-arca/nueva" className="text-indigo-500 hover:underline text-sm">
                                                    Crear primera factura
                                                </Link>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    invoices.map((inv) => (
                                        <TableRow
                                            key={inv.id}
                                            className="hover:bg-gray-50 dark:hover:bg-[#1F1F23]/50 transition-colors"
                                        >
                                            <TableCell className="text-xs text-gray-500 whitespace-nowrap">
                                                {new Date(inv.created_at).toLocaleDateString("es-AR")}
                                            </TableCell>
                                            <TableCell>
                                                <span className="inline-flex items-center justify-center w-7 h-7 rounded-md bg-indigo-100 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold text-sm">
                                                    {inv.tipo_comprobante}
                                                </span>
                                            </TableCell>
                                            <TableCell className="font-mono text-xs">
                                                {pad(inv.punto_venta, 4)}-{pad(inv.numero_comprobante || 0, 8)}
                                            </TableCell>
                                            <TableCell className="max-w-[140px] truncate text-sm font-medium">
                                                {inv.cliente_nombre}
                                            </TableCell>
                                            <TableCell className="text-xs text-gray-500">
                                                {inv.cliente_documento || "—"}
                                            </TableCell>
                                            <TableCell className="text-right font-medium text-sm">
                                                ${" "}
                                                {Number(inv.total).toLocaleString("es-AR", { minimumFractionDigits: 2 })}
                                            </TableCell>
                                            <TableCell>
                                                <StatusBadge estado={inv.estado} />
                                            </TableCell>
                                            <TableCell className="font-mono text-xs text-gray-500">
                                                {inv.cae ? inv.cae.slice(0, 10) + "…" : "—"}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon">
                                                            {(authorizing === inv.id || generating === inv.id) ? (
                                                                <Loader2 className="w-4 h-4 animate-spin" />
                                                            ) : (
                                                                <MoreVertical className="w-4 h-4" />
                                                            )}
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent
                                                        align="end"
                                                        className="dark:bg-[#0F0F12] border-gray-200 dark:border-[#1F1F23]"
                                                    >
                                                        <DropdownMenuItem asChild>
                                                            <Link href={`/facturacion-arca/comprobantes/${inv.id}`} className="flex items-center gap-2 cursor-pointer">
                                                                <Eye className="w-4 h-4" /> Ver detalle
                                                            </Link>
                                                        </DropdownMenuItem>

                                                        {(inv.estado === "PENDIENTE" || inv.estado === "ERROR") && (
                                                            <DropdownMenuItem
                                                                className="flex items-center gap-2 cursor-pointer text-indigo-600 dark:text-indigo-400"
                                                                onClick={() => handleAuthorize(inv.id)}
                                                                disabled={authorizing === inv.id}
                                                            >
                                                                <CheckCircle2 className="w-4 h-4" /> Emitir con ARCA
                                                            </DropdownMenuItem>
                                                        )}

                                                        {inv.estado === "AUTORIZADO" && (
                                                            <>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                    onClick={() => handleGeneratePdf(inv.id)}
                                                                    disabled={generating === inv.id}
                                                                >
                                                                    <RefreshCw className="w-4 h-4" /> Generar PDF
                                                                </DropdownMenuItem>
                                                                <DropdownMenuItem
                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                    onClick={() => handleDownloadPdf(inv.id)}
                                                                >
                                                                    <Download className="w-4 h-4" /> Descargar PDF
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}

                                                        {inv.estado === "RECHAZADO" && (
                                                            <>
                                                                <DropdownMenuSeparator />
                                                                <DropdownMenuItem
                                                                    className="flex items-center gap-2 cursor-pointer"
                                                                    onClick={() => handleAuthorize(inv.id)}
                                                                >
                                                                    <RefreshCw className="w-4 h-4" /> Reintentar
                                                                </DropdownMenuItem>
                                                            </>
                                                        )}
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>

                    {/* Pagination */}
                    {meta && meta.last_page > 1 && (
                        <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-[#1F1F23] text-sm text-gray-500">
                            <span>
                                {meta.total} comprobante{meta.total !== 1 ? "s" : ""}
                            </span>
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                                    disabled={page === 1}
                                    className="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-[#1F1F23] disabled:opacity-30"
                                >
                                    <ChevronLeft className="w-4 h-4" />
                                </button>
                                <span>
                                    Página {meta.current_page} de {meta.last_page}
                                </span>
                                <button
                                    onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
                                    disabled={page === meta.last_page}
                                    className="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-[#1F1F23] disabled:opacity-30"
                                >
                                    <ChevronRight className="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </Layout>
    )
}
