"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { ShoppingCart, Plus, Search, DollarSign, User, Receipt, MoreVertical, Eye, Printer, Trash2 } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
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
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from "@/components/ui/badge"
import { toast } from "sonner"

import { LoadingScreen } from "@/components/ui/loading-screen"

export default function SalesPage() {
    const [sales, setSales] = useState<any[]>([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")

    const fetchSales = async () => {
        try {
            const res = await apiRequest(`/sales?search=${search}`)
            setSales(res.data)
        } catch (error: any) {
            toast.error(error.message)
        } finally {
            setLoading(false)
        }
    }

    useEffect(() => {
        fetchSales()
    }, [search])

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-2">
                            <ShoppingCart className="w-6 h-6 text-emerald-500" /> Ventas
                        </h1>
                        <p className="text-sm text-gray-500">Listado histórico de ventas realizadas</p>
                    </div>
                    <Button className="bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-2">
                        <Plus className="w-4 h-4" /> Nueva Venta
                    </Button>
                </div>

                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-gray-200 dark:border-[#1F1F23]">
                        <div className="relative flex-1 max-w-sm">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <Input
                                placeholder="Buscar por número de venta o cliente..."
                                className="pl-9 bg-gray-50 dark:bg-[#1F1F23]"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </div>

                    <div className="relative min-h-[300px]">
                        {loading && <div className="absolute inset-0 bg-white/50 dark:bg-black/20 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-b-xl"><LoadingScreen /></div>}

                        <Table>
                            <TableHeader className="bg-gray-50 dark:bg-[#1F1F23]">
                                <TableRow>
                                    <TableHead>Venta #</TableHead>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Vendedor</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {sales.length === 0 && !loading ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center py-20 text-gray-500">No hay ventas registradas</TableCell>
                                    </TableRow>
                                ) : (
                                    sales.map((sale) => (
                                        <TableRow key={sale.id} className="hover:bg-gray-50 dark:hover:bg-[#1F1F23]/50 transition-colors">
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <div className="p-1.5 rounded bg-emerald-50 dark:bg-emerald-500/10">
                                                        <Receipt className="w-3.5 h-3.5 text-emerald-500" />
                                                    </div>
                                                    <span className="font-bold text-sm tracking-tight">{sale.sale_number}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-xs text-gray-500">
                                                {new Date(sale.sold_at).toLocaleString()}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-col text-sm">
                                                    <span className="font-medium">{sale.client?.full_name || 'Consumidor Final'}</span>
                                                    <span className="text-[10px] text-gray-400">{sale.client?.phone || '-'}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-sm">
                                                <div className="flex items-center gap-1">
                                                    <User className="w-3 h-3 text-gray-400" /> {sale.seller?.name}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-col">
                                                    <span className="font-bold text-emerald-600 tracking-tight">{sale.currency} {sale.total}</span>
                                                    {sale.discount > 0 && (
                                                        <span className="text-[10px] text-red-400">Desc: -{sale.currency}{sale.discount}</span>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="icon">
                                                            <MoreVertical className="w-4 h-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end" className="dark:bg-[#0F0F12] border-gray-200 dark:border-[#1F1F23]">
                                                        <DropdownMenuItem className="flex items-center gap-2 cursor-pointer">
                                                            <Eye className="w-4 h-4" /> Ver Comprobante
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem className="flex items-center gap-2 cursor-pointer">
                                                            <Printer className="w-4 h-4" /> Imprimir Ticket
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem className="flex items-center gap-2 text-red-500 cursor-pointer">
                                                            <Trash2 className="w-4 h-4" /> Anular Venta
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </Layout>
    )
}
