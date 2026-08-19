"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { Wrench, Plus, Search, Clock, CheckCircle2, AlertCircle, MoreVertical, Edit, User, Smartphone, Calendar } from "lucide-react"
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

export default function RepairsPage() {
    const [repairs, setRepairs] = useState<any[]>([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")

    const fetchRepairs = async () => {
        try {
            const res = await apiRequest(`/repairs?search=${search}`)
            setRepairs(res.data)
        } catch (error: any) {
            toast.error(error.message)
        } finally {
            setLoading(false)
        }
    }

    useEffect(() => {
        fetchRepairs()
    }, [search])

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'pending': return <Badge variant="secondary" className="bg-gray-100 text-gray-700">Pendiente</Badge>
            case 'diagnosis': return <Badge className="bg-orange-500/10 text-orange-600 border-none">Diagnóstico</Badge>
            case 'in_progress': return <Badge className="bg-blue-500/10 text-blue-600 border-none">En Proceso</Badge>
            case 'ready': return <Badge className="bg-emerald-500/10 text-emerald-600 border-none">Listo para Retiro</Badge>
            case 'delivered': return <Badge className="bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 border-none">Entregado</Badge>
            default: return <Badge variant="outline">{status}</Badge>
        }
    }

    const getPriorityIcon = (priority: string) => {
        switch (priority) {
            case 'urgent': return <AlertCircle className="w-4 h-4 text-red-500" />
            case 'high': return <AlertCircle className="w-4 h-4 text-orange-500" />
            default: return <Clock className="w-4 h-4 text-blue-400" />
        }
    }

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-2">
                            <Wrench className="w-6 h-6 text-orange-500" /> Reparaciones
                        </h1>
                        <p className="text-sm text-gray-500">Gestiona las órdenes de servicio técnico</p>
                    </div>
                    <Button className="bg-orange-600 hover:bg-orange-700 text-white flex items-center gap-2">
                        <Plus className="w-4 h-4" /> Nueva Orden
                    </Button>
                </div>

                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-gray-200 dark:border-[#1F1F23]">
                        <div className="relative flex-1 max-w-sm">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <Input
                                placeholder="Buscar por equipo, cliente o ticket..."
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
                                    <TableHead>Orden / Equipo</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Falla Reportada</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Prioridad</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {repairs.length === 0 && !loading ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center py-20 text-gray-500">No hay órdenes de reparación</TableCell>
                                    </TableRow>
                                ) : (
                                    repairs.map((repair) => (
                                        <TableRow key={repair.id} className="hover:bg-gray-50 dark:hover:bg-[#1F1F23]/50 transition-colors">
                                            <TableCell>
                                                <div className="flex flex-col">
                                                    <span className="text-xs font-bold text-gray-500">{repair.repair_number}</span>
                                                    <span className="font-medium text-sm flex items-center gap-1">
                                                        <Smartphone className="w-3 h-3" /> {repair.device_model}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex flex-col">
                                                    <span className="text-sm font-medium flex items-center gap-1">
                                                        <User className="w-3 h-3 text-gray-400" /> {repair.client?.full_name}
                                                    </span>
                                                    <span className="text-[10px] text-gray-500">Recibido: {new Date(repair.received_at).toLocaleDateString()}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-1 max-w-[200px]" title={repair.reported_issue}>
                                                    {repair.reported_issue}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                {getStatusBadge(repair.status)}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 text-xs font-medium uppercase tracking-tight">
                                                    {getPriorityIcon(repair.priority)}
                                                    {repair.priority}
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
                                                            <Edit className="w-4 h-4" /> Gestionar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem className="flex items-center gap-2 cursor-pointer">
                                                            Diagnosticar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem className="flex items-center gap-2 text-red-500 cursor-pointer">
                                                            Eliminar
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
