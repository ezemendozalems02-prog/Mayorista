"use client"

import { LogOut, MoveUpRight, Settings, CreditCard, FileText } from "lucide-react"
import Image from "next/image"
import Link from "next/link"
import { useRouter } from "next/navigation"
import { apiRequest } from "@/lib/api"
import { toast } from "sonner"

interface MenuItem {
  label: string
  value?: string
  href: string
  icon?: React.ReactNode
  external?: boolean
}

interface Profile01Props {
  name?: string
  role?: string
  avatar?: string
  subscription?: string
}

export default function Profile01({
  name = "Usuario",
  role = "Vendedor",
  avatar = "https://ferf1mheo22r9ira.public.blob.vercel-storage.com/avatar-02-albo9B0tWOSLXCVZh9rX9KFxXIVWMr.png",
  subscription = "Plan Pro",
}: Profile01Props) {
  const router = useRouter()

  const handleLogout = async () => {
    try {
      await apiRequest("/logout", { method: "POST" })
    } catch (error) {
      console.error("Error logging out from server", error)
    } finally {
      // Always clear local data even if server call fails
      document.cookie = "auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT"
      localStorage.removeItem("auth_token")
      localStorage.removeItem("user")
      toast.success("Sesión cerrada")
      router.push("/login")
    }
  }

  const menuItems: MenuItem[] = [
    {
      label: "Mi Suscripción",
      value: subscription,
      href: "#",
      icon: <CreditCard className="w-4 h-4" />,
      external: false,
    },
    {
      label: "Configuración",
      href: "/settings",
      icon: <Settings className="w-4 h-4" />,
    },
    {
      label: "Términos y Políticas",
      href: "#",
      icon: <FileText className="w-4 h-4" />,
      external: true,
    },
  ]

  return (
    <div className="w-full max-w-sm mx-auto">
      <div className="relative overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-[#0F0F12]">
        <div className="relative px-6 pt-12 pb-6">
          <div className="flex items-center gap-4 mb-8">
            <div className="relative shrink-0">
              <Image
                src={avatar}
                alt={name}
                width={72}
                height={72}
                className="rounded-full ring-4 ring-white dark:ring-zinc-900 object-cover"
              />
              <div className="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-900" />
            </div>

            {/* Profile Info */}
            <div className="flex-1">
              <h2 className="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{name}</h2>
              <p className="text-zinc-600 dark:text-zinc-400 capitalize">{role.replace('_', ' ')}</p>
            </div>
          </div>
          <div className="h-px bg-zinc-200 dark:bg-zinc-800 my-6" />
          <div className="space-y-2">
            {menuItems.map((item) => (
              <Link
                key={item.label}
                href={item.href}
                className="flex items-center justify-between p-2 
                                    hover:bg-zinc-50 dark:hover:bg-zinc-800/50 
                                    rounded-lg transition-colors duration-200"
              >
                <div className="flex items-center gap-2">
                  {item.icon}
                  <span className="text-sm font-medium text-zinc-900 dark:text-zinc-100">{item.label}</span>
                </div>
                <div className="flex items-center">
                  {item.value && <span className="text-sm text-zinc-500 dark:text-zinc-400 mr-2">{item.value}</span>}
                  {item.external && <MoveUpRight className="w-4 h-4" />}
                </div>
              </Link>
            ))}

            <button
              type="button"
              onClick={handleLogout}
              className="w-full flex items-center justify-between p-2 
                                hover:bg-red-50 dark:hover:bg-red-900/10 
                                rounded-lg transition-colors duration-200 text-red-600"
            >
              <div className="flex items-center gap-2">
                <LogOut className="w-4 h-4" />
                <span className="text-sm font-medium">Cerrar Sesión</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

