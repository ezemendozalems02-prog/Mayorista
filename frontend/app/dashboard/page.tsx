"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { Wallet, CreditCard, BarChart3, Package, Users, ShoppingCart, ArrowUpRight } from "lucide-react"
import { toast } from "sonner"
import { SkeletonCard, LoadingScreen } from "@/components/ui/loading-screen"

export default function DashboardPage() {
  const [data, setData] = useState<any>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    async function fetchDashboard() {
      try {
        const res = await apiRequest("/dashboard")
        setData(res)
      } catch (error: any) {
        toast.error(error.message || "Error al cargar dashboard")
      } finally {
        setLoading(false)
      }
    }
    fetchDashboard()
  }, [])

  const formatARS = (val: number | string) => {
    const num = Number(val) || 0
    return `$ ${num.toLocaleString("es-AR")} ARS`
  }

  return (
    <Layout>
      <div className="space-y-6 animate-in fade-in duration-500">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <h1 className="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
              Panel Principal — Mito Yamile
            </h1>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Resumen en tiempo real expresado en Pesos Argentinos ($ ARS)
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {loading ? (
            <>
              <SkeletonCard />
              <SkeletonCard />
              <SkeletonCard />
              <SkeletonCard />
            </>
          ) : (
            <>
              <KpiCard
                title="Ventas del Mes"
                value={formatARS(data?.metrics?.sales_month || 0)}
                icon={Wallet}
                color="text-emerald-600 dark:text-emerald-400"
                bg="bg-emerald-50 dark:bg-emerald-950/30"
              />
              <KpiCard
                title="Ganancia Estimada"
                value={formatARS(data?.metrics?.profit_month || 0)}
                icon={BarChart3}
                color="text-blue-600 dark:text-blue-400"
                bg="bg-blue-50 dark:bg-blue-950/30"
              />
              <KpiCard
                title="Stock Disponible"
                value={`${data?.metrics?.stock_count || 0} u`}
                icon={Package}
                color="text-indigo-600 dark:text-indigo-400"
                bg="bg-indigo-50 dark:bg-indigo-950/30"
              />
              <KpiCard
                title="Clientes Activos"
                value={data?.metrics?.active_repairs ?? 12}
                icon={Users}
                color="text-purple-600 dark:text-purple-400"
                bg="bg-purple-50 dark:bg-purple-950/30"
              />
            </>
          )}
        </div>

        {loading ? (
          <LoadingScreen />
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="bg-white dark:bg-[#0F0F12] rounded-2xl p-6 border border-gray-200 dark:border-[#1F1F23] shadow-sm">
              <h2 className="text-lg font-bold mb-4 flex items-center justify-between text-gray-900 dark:text-white">
                <span className="flex items-center gap-2">
                  <CreditCard className="w-5 h-5 text-emerald-600" /> Ventas Recientes ($ ARS)
                </span>
              </h2>
              <div className="space-y-3">
                {(!data?.recent_sales || data.recent_sales.length === 0) ? (
                  <p className="text-sm text-gray-500 py-6 text-center">No hay ventas registradas aún hoy.</p>
                ) : (
                  data.recent_sales.map((sale: any) => (
                    <div
                      key={sale.id}
                      className="flex justify-between items-center p-3.5 rounded-xl bg-gray-50 dark:bg-[#151518] border border-gray-100 dark:border-[#222226]"
                    >
                      <div>
                        <p className="font-semibold text-sm text-gray-900 dark:text-white">
                          Venta #{sale.sale_number || sale.id}
                        </p>
                        <p className="text-xs text-gray-500">{sale.client?.full_name || "Consumidor Final"}</p>
                      </div>
                      <div className="text-right">
                        <p className="font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                          {formatARS(sale.total)}
                        </p>
                        <p className="text-[10px] uppercase font-bold text-gray-400">{sale.status || "Completada"}</p>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>

            <div className="bg-white dark:bg-[#0F0F12] rounded-2xl p-6 border border-gray-200 dark:border-[#1F1F23] shadow-sm">
              <h2 className="text-lg font-bold mb-4 flex items-center gap-2 text-gray-900 dark:text-white">
                <ShoppingCart className="w-5 h-5 text-blue-600" /> Accesos Rápidos
              </h2>
              <div className="grid grid-cols-2 gap-3">
                <a
                  href="/sales"
                  className="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950/20 dark:to-indigo-950/20 border border-blue-100 dark:border-blue-900/30 hover:shadow-md transition-all block group"
                >
                  <p className="font-bold text-sm text-blue-900 dark:text-blue-200 group-hover:text-blue-600 flex items-center justify-between">
                    Nueva Venta <ArrowUpRight className="w-4 h-4" />
                  </p>
                  <p className="text-xs text-blue-600/70 dark:text-blue-400 mt-1">Registrar venta en mostrador</p>
                </a>

                <a
                  href="/inventory"
                  className="p-4 rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-950/20 dark:to-pink-950/20 border border-purple-100 dark:border-purple-900/30 hover:shadow-md transition-all block group"
                >
                  <p className="font-bold text-sm text-purple-900 dark:text-purple-200 group-hover:text-purple-600 flex items-center justify-between">
                    Catálogo <ArrowUpRight className="w-4 h-4" />
                  </p>
                  <p className="text-xs text-purple-600/70 dark:text-purple-400 mt-1">Cargar o editar productos</p>
                </a>

                <a
                  href="/clients"
                  className="p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/20 dark:to-teal-950/20 border border-emerald-100 dark:border-emerald-900/30 hover:shadow-md transition-all block group"
                >
                  <p className="font-bold text-sm text-emerald-900 dark:text-emerald-200 group-hover:text-emerald-600 flex items-center justify-between">
                    Clientes <ArrowUpRight className="w-4 h-4" />
                  </p>
                  <p className="text-xs text-emerald-600/70 dark:text-emerald-400 mt-1">Cuentas corrientes y saldos</p>
                </a>

                <a
                  href="/facturacion-arca/nueva"
                  className="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 border border-amber-100 dark:border-amber-900/30 hover:shadow-md transition-all block group"
                >
                  <p className="font-bold text-sm text-amber-900 dark:text-amber-200 group-hover:text-amber-600 flex items-center justify-between">
                    Factura ARCA <ArrowUpRight className="w-4 h-4" />
                  </p>
                  <p className="text-xs text-amber-600/70 dark:text-amber-400 mt-1">Emitir comprobantes fiscales</p>
                </a>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  )
}

function KpiCard({ title, value, icon: Icon, color, bg }: any) {
  return (
    <div className="bg-white dark:bg-[#0F0F12] p-5 rounded-2xl border border-gray-200 dark:border-[#1F1F23] flex items-center gap-4 shadow-sm">
      <div className={`p-3.5 rounded-xl ${bg} ${color}`}>
        <Icon className="w-6 h-6" />
      </div>
      <div>
        <p className="text-xs text-gray-500 font-bold uppercase tracking-wider">{title}</p>
        <p className="text-lg font-black text-gray-900 dark:text-white mt-0.5">{value}</p>
      </div>
    </div>
  )
}
