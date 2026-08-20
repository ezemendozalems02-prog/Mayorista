"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { Package, Plus, Search, Filter, MoreVertical, Edit, Trash2, Tag, Barcode, Layers } from "lucide-react"
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
import { ContextualHelp } from "@/components/onboarding/contextual-help"

export default function InventoryPage() {
  const [items, setItems] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState("")

  const fetchInventory = async () => {
    try {
      const res = await apiRequest(`/products?search=${search}`)
      if (res && res.data) {
        setItems(res.data)
      } else if (Array.isArray(res)) {
        setItems(res)
      } else {
        setItems([])
      }
    } catch (error: any) {
      // Fallback a /inventory si es la ruta por defecto
      try {
        const resInv = await apiRequest(`/inventory?search=${search}`)
        setItems(resInv.data || resInv || [])
      } catch {
        toast.error(error.message || "Error al cargar catálogo")
      }
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchInventory()
  }, [search])

  const formatARS = (amount: number | string) => {
    const num = Number(amount) || 0
    return new Intl.NumberFormat("es-AR", {
      style: "currency",
      currency: "ARS",
      maximumFractionDigits: 2,
    }).format(num)
  }

  const getStockBadge = (quantity: number, minStock: number = 5) => {
    if (quantity <= 0) {
      return <Badge className="bg-rose-500/10 text-rose-500 border-none">Sin Stock</Badge>
    }
    if (quantity <= minStock) {
      return <Badge className="bg-amber-500/10 text-amber-500 border-none">Stock Bajo ({quantity} u)</Badge>
    }
    return <Badge className="bg-emerald-500/10 text-emerald-500 border-none">{quantity} u disponibles</Badge>
  }

  return (
    <Layout>
      <div className="space-y-6 animate-in fade-in duration-500">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 className="text-2xl font-bold flex items-center gap-2 text-gray-900 dark:text-white">
              <Package className="w-6 h-6 text-blue-600 dark:text-blue-400" /> Productos y Stock
              <ContextualHelp
                title="Gestión de Catálogo"
                content="Acá podés administrar tus productos, sus precios en Pesos Argentinos ($ ARS), códigos de barras, categorías y stock disponible."
              />
            </h1>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Catálogo de artículos (Juguetería, Librería y Regalería)
            </p>
          </div>
          <Button className="bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-2 font-bold rounded-xl shadow-md">
            <Plus className="w-4 h-4" /> Nuevo Producto
          </Button>
        </div>

        <div className="bg-white dark:bg-[#0F0F12] rounded-2xl border border-gray-200 dark:border-[#1F1F23] overflow-hidden shadow-sm">
          <div className="p-4 border-b border-gray-200 dark:border-[#1F1F23] flex items-center gap-4">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <Input
                placeholder="Buscar por nombre, código de barras o código interno..."
                className="pl-9 bg-gray-50 dark:bg-[#151518] rounded-xl border-gray-200 dark:border-[#222226]"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <Button variant="outline" className="flex items-center gap-2 rounded-xl">
              <Filter className="w-4 h-4" /> Filtros
            </Button>
          </div>

          <div className="relative min-h-[300px]">
            {loading && (
              <div className="absolute inset-0 bg-white/50 dark:bg-black/20 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-b-xl">
                <LoadingScreen />
              </div>
            )}

            <Table>
              <TableHeader className="bg-gray-50 dark:bg-[#151518]">
                <TableRow>
                  <TableHead className="font-bold">Producto</TableHead>
                  <TableHead className="font-bold">Código</TableHead>
                  <TableHead className="font-bold">Costo ($ ARS)</TableHead>
                  <TableHead className="font-bold">Precio Minorista</TableHead>
                  <TableHead className="font-bold">Precio Mayorista</TableHead>
                  <TableHead className="font-bold">Stock</TableHead>
                  <TableHead className="text-right font-bold">Acciones</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {items.length === 0 && !loading ? (
                  <TableRow>
                    <TableCell colSpan={7} className="text-center py-16 text-gray-500">
                      No hay productos registrados en el catálogo.
                    </TableCell>
                  </TableRow>
                ) : (
                  items.map((item) => {
                    const name = item.name || item.model || "Producto sin nombre"
                    const category = item.category?.name || item.storage || "General"
                    const brand = item.brand?.name || item.color || ""
                    const barcode = item.barcode || item.imei || item.serial_number || "SIN CÓDIGO"
                    const internalCode = item.sku || `MIT-${String(item.id).padStart(6, "0")}`
                    const cost = item.cost_price || item.purchase_price || 0
                    const priceRetail = item.retail_price || item.sale_price || 0
                    const priceWholesale = item.wholesale_price || priceRetail * 0.9
                    const stock = item.stock_quantity ?? item.current_stock ?? (item.status === "in_stock" ? 1 : 0)

                    return (
                      <TableRow key={item.id} className="hover:bg-gray-50 dark:hover:bg-[#151518]/50 transition-colors">
                        <TableCell>
                          <div className="flex items-center gap-3">
                            <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                              <Package className="w-4 h-4" />
                            </div>
                            <div>
                              <p className="font-semibold text-sm text-gray-900 dark:text-white">{name}</p>
                              <p className="text-[11px] text-gray-400">
                                {category} {brand ? `• ${brand}` : ""}
                              </p>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div className="text-xs space-y-0.5">
                            <p className="flex items-center gap-1 text-gray-600 dark:text-gray-300 font-mono">
                              <Barcode className="w-3 h-3 text-purple-500" />
                              {barcode}
                            </p>
                            <p className="text-[10px] text-gray-400 font-mono">INT: {internalCode}</p>
                          </div>
                        </TableCell>
                        <TableCell className="text-xs text-gray-500 font-medium">
                          {formatARS(cost)}
                        </TableCell>
                        <TableCell className="text-xs font-bold text-gray-900 dark:text-white">
                          {formatARS(priceRetail)}
                        </TableCell>
                        <TableCell className="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                          {formatARS(priceWholesale)}
                        </TableCell>
                        <TableCell>{getStockBadge(stock)}</TableCell>
                        <TableCell className="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="icon" className="rounded-lg">
                                <MoreVertical className="w-4 h-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="dark:bg-[#0F0F12] border-gray-200 dark:border-[#1F1F23]">
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
                    )
                  })
                )}
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </Layout>
  )
}
