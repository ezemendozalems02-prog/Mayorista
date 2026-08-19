"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { Package, Plus, Search, Filter, MoreVertical, Edit, Trash2, Smartphone, Battery, Info } from "lucide-react"
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

export default function InventoryPage() {
    const [items, setItems] = useState<any[]>([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")

    const fetchInventory = async () => {
        try {
            const res = await apiRequest(`/inventory?search=${search}`)
            setItems(res.data)
        } catch (error: any) {
            toast.error(error.message)
        } finally {
            setLoading(false)
        }
    }

    useEffect(() => {
        fetchInventory()
    }, [search])

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'in_stock': return <Badge className="bg-emerald-500/10 text-emerald-500 border-none">En Stock</Badge>
            case 'sold': return <Badge variant="secondary">Vendido</Badge>
            case 'reserved': return <Badge className="bg-blue-500/10 text-blue-500 border-none">Reservado</Badge>
            case 'in_service': return <Badge className="bg-orange-500/10 text-orange-500 border-none">En Servicio</Badge>
            default: return <Badge variant="outline">{status}</Badge>
        }
    }

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-2">
                            <Package className="w-6 h-6 text-indigo-500" /> Inventario
                        </h1>
                        <p className="text-sm text-gray-500">Equipos Apple disponibles y vendidos</p>
                    </div>
                    <Button className="bg-indigo-600 hover:bg-indigo-700 text-white flex items-center gap-2">
                        <Plus className="w-4 h-4" /> Nuevo Equipo
                    </Button>
                </div>

                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-gray-200 dark:border-[#1F1F23] flex items-center gap-4">
                        <div className="relative flex-1 max-w-sm">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <Input
                                placeholder="Buscar por modelo, IMEI o S/N..."
                                className="pl-9 bg-gray-50 dark:bg-[#1F1F23]"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                        <Button variant="outline" className="flex items-center gap-2">
                            <Filter className="w-4 h-4" /> Filtros
                        </Button>
                    </div>

                    <div className="relative min-h-[300px]">
                        {loading && <div className="absolute inset-0 bg-white/50 dark:bg-black/20 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-b-xl"><LoadingScreen /></div>}

                        <Table>
                            <TableHeader className="bg-gray-50 dark:bg-[#1F1F23]">
                                <TableRow>
                                    <TableHead>Equipo</TableHead>
                                    <TableHead>Identificación</TableHead>
                                    <TableHead>Batería</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Precios</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.length === 0 && !loading ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center py-20 text-gray-500">No hay equipos en el inventario</TableCell>
                                    </TableRow>
                                ) : (
                                    items.map((item) => (
                                        <TableRow key={item.id} className="hover:bg-gray-50 dark:hover:bg-[#1F1F23]/50 transition-colors">
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div className="p-2 rounded bg-indigo-50 dark:bg-indigo-500/10">
                                                        <Smartphone className="w-4 h-4 text-indigo-500" />
                                                    </div>
                                                    <div>
                                                        <p className="font-medium text-sm">{item.model}</p>
                                                        <p className="text-[10px] text-gray-400 uppercase">{item.storage} • {item.color}</p>
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-xs space-y-0.5">
                                                    <p className="text-gray-500">IMEI: <span className="text-gray-900 dark:text-gray-300">{item.imei || '-'}</span></p>
                                                    <p className="text-gray-500">S/N: <span className="text-gray-900 dark:text-gray-300">{item.serial_number || '-'}</span></p>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1.5 text-xs">
                                                    <Battery className={`w-3 h-3 ${item.battery_health >= 85 ? 'text-emerald-500' : 'text-orange-500'}`} />
                                                    <span className="font-bold">{item.battery_health}%</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {getStatusBadge(item.status)}
                                            </TableCell>
                                            <TableCell>
                                                <div className="text-xs space-y-0.5">
                                                    <p className="text-gray-400 italic">Costo: {item.currency} {item.purchase_price}</p>
                                                    <p className="text-indigo-600 font-bold">Venta: {item.currency} {item.sale_price}</p>
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
                                                            <Info className="w-4 h-4" /> Detalle
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem className="flex items-center gap-2 cursor-pointer">
                                                            <Edit className="w-4 h-4" /> Editar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem className="flex items-center gap-2 text-red-500 cursor-pointer">
                                                            <Trash2 className="w-4 h-4" /> Eliminar
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
