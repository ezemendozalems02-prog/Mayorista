"use client"

import { useEffect, useState } from "react"
import Layout from "@/components/kokonutui/layout"
import { apiRequest } from "@/lib/api"
import { Wallet, CreditCard, Calendar, BarChart3, Package, Wrench, Users } from "lucide-react"
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
        toast.error(error.message)
      } finally {
        setLoading(false)
      }
    }
    fetchDashboard()
  }, [])

  return (
    <Layout>
      <div className="space-y-6">
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
              <KpiCard title="Ventas del Mes" value={`$${data.metrics.sales_month}`} icon={Wallet} color="text-emerald-500" />
              <KpiCard title="Ganancia" value={`$${data.metrics.profit_month}`} icon={BarChart3} color="text-blue-500" />
              <KpiCard title="Reparaciones" value={data.metrics.active_repairs} icon={Wrench} color="text-orange-500" />
              <KpiCard title="Stock Disponible" value={data.metrics.stock_count} icon={Package} color="text-indigo-500" />
            </>
          )}
        </div>

        {loading ? (
          <LoadingScreen />
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div className="bg-white dark:bg-[#0F0F12] rounded-xl p-6 border border-gray-200 dark:border-[#1F1F23]">
              <h2 className="text-lg font-bold mb-4 flex items-center gap-2">
                <CreditCard className="w-4 h-4" /> Ventas Recientes
              </h2>
              <div className="space-y-4">
                {data.recent_sales.map((sale: any) => (
                  <div key={sale.id} className="flex justify-between items-center p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23]">
                    <div>
                      <p className="font-medium">{sale.sale_number}</p>
                      <p className="text-xs text-gray-500">{sale.client?.full_name || 'Consumidor Final'}</p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-emerald-600">${sale.total}</p>
                      <p className="text-[10px] uppercase text-gray-400">{sale.status}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-white dark:bg-[#0F0F12] rounded-xl p-6 border border-gray-200 dark:border-[#1F1F23]">
              <h2 className="text-lg font-bold mb-4 flex items-center gap-2">
                <Wrench className="w-4 h-4" /> Reparaciones Recientes
              </h2>
              <div className="space-y-4">
                {data.recent_repairs.map((repair: any) => (
                  <div key={repair.id} className="flex justify-between items-center p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23]">
                    <div>
                      <p className="font-medium">{repair.device_model}</p>
                      <p className="text-xs text-gray-500">{repair.client?.full_name}</p>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-semibold">{repair.repair_number}</p>
                      <p className="text-[10px] uppercase text-blue-400">{repair.status}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  )
}

function KpiCard({ title, value, icon: Icon, color }: any) {
  return (
    <div className="bg-white dark:bg-[#0F0F12] p-5 rounded-xl border border-gray-200 dark:border-[#1F1F23] flex items-center gap-4">
      <div className={`p-3 rounded-lg bg-gray-50 dark:bg-[#1F1F23] ${color}`}>
        <Icon className="w-6 h-6" />
      </div>
      <div>
        <p className="text-xs text-gray-500 font-medium uppercase">{title}</p>
        <p className="text-xl font-bold">{value}</p>
      </div>
    </div>
  )
}
