"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { Users, Plus, Search, Mail, Phone, MoreVertical, Edit, Trash2 } from "lucide-react"
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
import { toast } from "sonner"

import { LoadingScreen } from "@/components/ui/loading-screen"

export default function ClientsPage() {
    const [clients, setClients] = useState<any[]>([])
    const [loading, setLoading] = useState(true)
    const [search, setSearch] = useState("")

    const fetchClients = async () => {
        try {
            const res = await apiRequest(`/clients?search=${search}`)
            setClients(res.data)
        } catch (error: any) {
            toast.error(error.message)
        } finally {
            setLoading(false)
        }
    }

    useEffect(() => {
        fetchClients()
    }, [search])

    const deleteClient = async (id: number) => {
        if (!confirm("¿Estás seguro de eliminar este cliente?")) return
        try {
            await apiRequest(`/clients/${id}`, { method: "DELETE" })
            toast.success("Cliente eliminado")
            fetchClients()
        } catch (error: any) {
            toast.error(error.message)
        }
    }

    return (
        <Layout>
            <div className="space-y-6 animate-in fade-in duration-500">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 className="text-2xl font-bold flex items-center gap-2">
                            <Users className="w-6 h-6 text-blue-500" /> Clientes
                        </h1>
                        <p className="text-sm text-gray-500">Administra la base de datos de tus clientes</p>
                    </div>
                    <Button className="bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-2">
                        <Plus className="w-4 h-4" /> Nuevo Cliente
                    </Button>
                </div>

                <div className="bg-white dark:bg-[#0F0F12] rounded-xl border border-gray-200 dark:border-[#1F1F23] overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-gray-200 dark:border-[#1F1F23] flex items-center px-4">
                        <div className="relative flex-1 max-w-sm">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <Input
                                placeholder="Buscar por nombre, teléfono o documento..."
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
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Contacto</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead>Fecha Alta</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {clients.length === 0 && !loading ? (
                                    <TableRow>
                                        <TableCell colSpan={5} className="text-center py-20 text-gray-500">No se encontraron clientes</TableCell>
                                    </TableRow>
                                ) : (
                                    clients.map((client) => (
                                        <TableRow key={client.id} className="hover:bg-gray-50 dark:hover:bg-[#1F1F23]/50 transition-colors">
                                            <TableCell className="font-medium">{client.full_name}</TableCell>
                                            <TableCell>
                                                <div className="flex flex-col text-xs text-gray-500">
                                                    <span className="flex items-center gap-1 font-medium text-gray-900 dark:text-gray-300">
                                                        <Phone className="w-3 h-3" /> {client.phone || '-'}
                                                    </span>
                                                    <span className="flex items-center gap-1">
                                                        <Mail className="w-3 h-3" /> {client.email || '-'}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>{client.document_number || '-'}</TableCell>
                                            <TableCell className="text-gray-500 text-sm">
                                                {new Date(client.created_at).toLocaleDateString()}
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
                                                            <Edit className="w-4 h-4" /> Editar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            className="flex items-center gap-2 text-red-500 cursor-pointer"
                                                            onClick={() => deleteClient(client.id)}
                                                        >
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
