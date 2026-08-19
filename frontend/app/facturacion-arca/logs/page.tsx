"use client"

import { useEffect, useState, useCallback } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import {
    Activity,
    Search,
    Filter,
    ChevronLeft,
    ChevronRight,
    CheckCircle2,
    AlertCircle,
    X,
    Eye,
    Loader2,
    FileText,
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
import { LoadingScreen } from "@/components/ui/loading-screen"
import { toast } from "sonner"

// ─── types ────────────────────────────────────────────────────────────────────

interface ArcaLog {
    id: number
    endpoint: string
    status: string
    error_message: string | null
    request_payload: Record<string, unknown> | null
    response_payload: Record<string, unknown> | null
    invoice_id: number | null
    invoice?: {
        id: number
        tipo_comprobante: string
        punto_venta: number
        numero_comprobante: number
        estado: string
    } | null
    created_at: string
}

interface PaginationMeta {
    current_page: number
    last_page: number
    total: number
}

// ─── helpers ──────────────────────────────────────────────────────────────────

function StatusBadge({ status }: { status: string }) {
    if (status === "OK" || status === "SUCCESS") {
        return (
            <Badge className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-none flex items-center gap-1">
                <CheckCircle2 className="w-3 h-3" /> OK
            </Badge>
        )
    }
    return (
        <Badge className="bg-red-500/10 text-red-600 dark:text-red-400 border-none flex items-center gap-1">
            <AlertCircle className="w-3 h-3" /> {status}
        </Badge>
    )
}

// ─── log detail modal ──────────────────────────────────────────────────────────

function LogDetailModal({ log, onClose }: { log: ArcaLog; onClose: () => void }) {
    function maskSensitive(obj: Record<string, unknown> | null): string {
        if (!obj) return "—"
        const clone = JSON.parse(JSON.stringify(obj))
        // Mask auth fields if present (safety layer on frontend)
        function recurse(o: any) {
            if (typeof o !== "object" || !o) return
            for (const key of Object.keys(o)) {
                if (["Token", "Sign", "token", "sign", "private_key", "certificate"].includes(key)) {
                    o[key] = "[REDACTED]"
                } else {
                    recurse(o[key])
                }
            }
        }
        recurse(clone)
        return JSON.stringify(clone, null, 2)
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div className="bg-white dark:bg-[#0F0F12] rounded-2xl border border-gray-200 dark:border-[#1F1F23] shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col">
                {/* Header */}
                <div className="flex items-center justify-between p-5 border-b border-gray-200 dark:border-[#1F1F23]">
                    <div>
                        <h3 className="font-semibold text-sm">Log #{log.id}</h3>
                        <p className="text-xs text-gray-500 mt-0.5">{log.endpoint} · {new Date(log.created_at).toLocaleString("es-AR")}</p>
                    </div>
                    <div className="flex items-center gap-3">
                        <StatusBadge status={log.status} />
                        <button onClick={onClose} className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <X className="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {/* Body */}
                <div className="overflow-y-auto p-5 space-y-5 flex-1">
                    {log.error_message && (
                        <div className="p-3 rounded-lg bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-sm text-red-600 dark:text-red-400">
                            <span className="font-medium">Error:</span> {log.error_message}
                        </div>
                    )}

                    {log.invoice && (
                        <div className="p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23] border border-gray-100 dark:border-[#2a2a2e] text-sm space-y-1">
                            <p className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Factura relacionada</p>
                            <div className="flex gap-4 text-sm">
                                <span>Factura <strong>{log.invoice.tipo_comprobante}</strong></span>
                                <span>{String(log.invoice.punto_venta).padStart(4, "0")}-{String(log.invoice.numero_comprobante).padStart(8, "0")}</span>
                                <span className="text-gray-400">{log.invoice.estado}</span>
                            </div>
                        </div>
                    )}

                    <div>
                        <p className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Request payload</p>
                        <pre className="text-xs font-mono bg-gray-50 dark:bg-[#1F1F23] border border-gray-100 dark:border-[#2a2a2e] rounded-lg p-4 overflow-x-auto text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-all">
                            {maskSensitive(log.request_payload)}
                        </pre>
                    </div>

                    <div>
                        <p className="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Response payload</p>
                        <pre className="text-xs font-mono bg-gray-50 dark:bg-[#1F1F23] border border-gray-100 dark:border-[#2a2a2e] rounded-lg p-4 overflow-x-auto text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-all">
                            {maskSensitive(log.response_payload)}
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    )
}

// ─── page ─────────────────────────────────────────────────────────────────────

export default function LogsArcaPage() {
    const [logs, setLogs] = useState<ArcaLog[]>([])
    const [meta, setMeta] = useState<PaginationMeta | null>(null)
    const [loading, setLoading] = useState(true)
    const [page, setPage] = useState(1)
    const [showFilters, setShowFilters] = useState(false)
    const [selectedLog, setSelectedLog] = useState<ArcaLog | null>(null)
    const [loadingDetail, setLoadingDetail] = useState(false)

    const [filters, setFilters] = useState({
        endpoint: "",
        status: "",
        date_from: "",
        date_to: "",
        invoice_id: "",
    })

    const fetchLogs = useCallback(async (currentPage: number) => {
        setLoading(true)
        try {
            const params = new URLSearchParams()
            params.set("page", String(currentPage))
            params.set("per_page", "20")
            if (filters.endpoint) params.set("endpoint", filters.endpoint)
            if (filters.status) params.set("status", filters.status)
            if (filters.date_from) params.set("date_from", filters.date_from)
            if (filters.date_to) params.set("date_to", filters.date_to)
            if (filters.invoice_id) params.set("invoice_id", filters.invoice_id)

            const res = await apiRequest(`/arca/logs?${params.toString()}`)
            setLogs(res.data?.data ?? [])
            if (res.data) {
                setMeta({
                    current_page: res.data.current_page,
                    last_page: res.data.last_page,
                    total: res.data.total,
                })
            }
        } catch (err: any) {
            toast.error(err.message || "No se pudieron cargar los logs.")
        } finally {
            setLoading(false)
        }
    }, [filters])

    useEffect(() => {
        fetchLogs(page)
    }, [page, fetchLogs])

    async function handleViewDetail(log: ArcaLog) {
        setLoadingDetail(true)
        try {
            const res = await apiRequest(`/arca/logs/${log.id}`)
            setSelectedLog(res.data)
        } catch (err: any) {
            toast.error(err.message || "No se pudo cargar el detalle del log.")
        } finally {
            setLoadingDetail(false)
        }
    }

    function applyFilters() {
        setPage(1)
        fetchLogs(1)
    }

    function clearFilters() {
        setFilters({ endpoint: "", status: "", date_from: "", date_to: "", invoice_id: "" })
        setPage(1)
    }

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500">
                {/* Header */}
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-2">
                            <Activity className="w-6 h-6 text-indigo-500" /> Logs ARCA
                        </h1>
                        <p className="text-sm text-gray-500">
                            Diagnóstico técnico de todas las interacciones con ARCA
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setShowFilters(!showFilters)}
                        className="flex items-center gap-2"
                    >
                        <Filter className="w-4 h-4" /> Filtros
                    </Button>
                </div>

                {/* Filters */}
                {showFilters && (
                    <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] p-4 space-y-4">
                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Endpoint</label>
                                <Input
                                    value={filters.endpoint}
                                    onChange={(e) => setFilters({ ...filters, endpoint: e.target.value })}
                                    placeholder="wsfe, wsaa..."
                                    className="dark:bg-[#1F1F23] text-sm h-8"
                                />
                            </div>
                            <div>
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">Estado</label>
                                <select
                                    value={filters.status}
                                    onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                                    className="w-full px-2 py-1.5 text-sm rounded-md border border-gray-200 dark:border-[#2a2a2e] bg-white dark:bg-[#1F1F23] text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="OK">OK</option>
                                    <option value="ERROR">ERROR</option>
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
                                <label className="text-xs text-gray-500 uppercase tracking-wide mb-1 block">ID Factura</label>
                                <Input
                                    type="number"
                                    value={filters.invoice_id}
                                    onChange={(e) => setFilters({ ...filters, invoice_id: e.target.value })}
                                    placeholder="ID..."
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
                                    <TableHead>Endpoint</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Factura</TableHead>
                                    <TableHead className="max-w-[200px]">Error</TableHead>
                                    <TableHead className="text-right">Ver</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {logs.length === 0 && !loading ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center py-20 text-gray-500">
                                            <div className="flex flex-col items-center gap-2">
                                                <Activity className="w-10 h-10 text-gray-300 dark:text-gray-700" />
                                                <span>No hay registros técnicos de ARCA por el momento.</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    logs.map((log) => (
                                        <TableRow
                                            key={log.id}
                                            className="hover:bg-gray-50 dark:hover:bg-[#1F1F23]/50 transition-colors"
                                        >
                                            <TableCell className="text-xs text-gray-500 whitespace-nowrap">
                                                {new Date(log.created_at).toLocaleString("es-AR", {
                                                    day: "2-digit",
                                                    month: "2-digit",
                                                    year: "numeric",
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                })}
                                            </TableCell>
                                            <TableCell className="font-mono text-xs text-gray-600 dark:text-gray-400">
                                                {log.endpoint}
                                            </TableCell>
                                            <TableCell>
                                                <StatusBadge status={log.status} />
                                            </TableCell>
                                            <TableCell className="text-xs text-gray-500">
                                                {log.invoice ? (
                                                    <span className="font-medium">
                                                        #{log.invoice.id} — Fact.{" "}
                                                        {log.invoice.tipo_comprobante}
                                                    </span>
                                                ) : (
                                                    "—"
                                                )}
                                            </TableCell>
                                            <TableCell className="max-w-[200px]">
                                                {log.error_message ? (
                                                    <p className="text-xs text-red-500 truncate" title={log.error_message}>
                                                        {log.error_message}
                                                    </p>
                                                ) : (
                                                    <span className="text-gray-400 text-xs">—</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleViewDetail(log)}
                                                    disabled={loadingDetail}
                                                >
                                                    {loadingDetail ? (
                                                        <Loader2 className="w-4 h-4 animate-spin" />
                                                    ) : (
                                                        <Eye className="w-4 h-4" />
                                                    )}
                                                </Button>
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
                            <span>{meta.total} registro{meta.total !== 1 ? "s" : ""}</span>
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                                    disabled={page === 1}
                                    className="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-[#1F1F23] disabled:opacity-30"
                                >
                                    <ChevronLeft className="w-4 h-4" />
                                </button>
                                <span>Página {meta.current_page} de {meta.last_page}</span>
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

            {/* Detail modal */}
            {selectedLog && (
                <LogDetailModal log={selectedLog} onClose={() => setSelectedLog(null)} />
            )}
        </Layout>
    )
}
