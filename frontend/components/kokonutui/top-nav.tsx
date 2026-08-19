"use client"

import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import Image from "next/image"
import { Bell, ChevronRight } from "lucide-react"
import Profile01 from "./profile-01"
import Link from "next/link"
import { ThemeToggle } from "../theme-toggle"
import { useEffect, useState } from "react"
import { usePathname } from "next/navigation"

interface BreadcrumbItem {
  label: string
  href?: string
}

export default function TopNav() {
  const [user, setUser] = useState<any>(null)
  const pathname = usePathname()

  useEffect(() => {
    const userData = localStorage.getItem("user")
    if (userData) {
      setUser(JSON.parse(userData))
    }
  }, [])

  const getBreadcrumbs = () => {
    const parts = pathname.split('/').filter(Boolean)
    const items: BreadcrumbItem[] = [{ label: "Vortex Control Phone", href: "/dashboard" }]

    parts.forEach((part, index) => {
      const href = '/' + parts.slice(0, index + 1).join('/')
      items.push({
        label: part.charAt(0).toUpperCase() + part.slice(1),
        href: index === parts.length - 1 ? undefined : href
      })
    })

    return items
  }

  const breadcrumbs = getBreadcrumbs()

  return (
    <nav className="px-3 sm:px-6 flex items-center justify-between bg-white dark:bg-[#0F0F12] border-b border-gray-200 dark:border-[#1F1F23] h-full">
      <div className="font-medium text-sm hidden sm:flex items-center space-x-1 truncate max-w-[300px]">
        {breadcrumbs.map((item, index) => (
          <div key={item.label} className="flex items-center">
            {index > 0 && <ChevronRight className="h-4 w-4 text-gray-500 dark:text-gray-400 mx-1" />}
            {item.href ? (
              <Link
                href={item.href}
                className="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 transition-colors capitalize"
              >
                {item.label}
              </Link>
            ) : (
              <span className="text-gray-900 dark:text-gray-100 capitalize">{item.label}</span>
            )}
          </div>
        ))}
      </div>

      <div className="flex items-center gap-2 sm:gap-4 ml-auto sm:ml-0">
        <button
          type="button"
          className="p-1.5 sm:p-2 hover:bg-gray-100 dark:hover:bg-[#1F1F23] rounded-full transition-colors"
        >
          <Bell className="h-4 w-4 sm:h-5 sm:w-5 text-gray-600 dark:text-gray-300" />
        </button>

        <ThemeToggle />

        <DropdownMenu>
          <DropdownMenuTrigger className="focus:outline-none">
            <div className="flex items-center gap-2">
              <div className="hidden sm:flex flex-col text-right mr-1">
                <span className="text-xs font-bold text-gray-900 dark:text-white">{user?.name || 'Cargando...'}</span>
                <span className="text-[10px] text-gray-500 capitalize">{user?.role?.replace('_', ' ') || ''}</span>
              </div>
              <Image
                src="https://ferf1mheo22r9ira.public.blob.vercel-storage.com/avatar-01-n0x8HFv8EUetf9z6ht0wScJKoTHqf8.png"
                alt="User avatar"
                width={28}
                height={28}
                className="rounded-full ring-1 ring-gray-200 dark:ring-[#2B2B30] sm:w-8 sm:h-8 cursor-pointer"
              />
            </div>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="end"
            sideOffset={8}
            className="w-[280px] sm:w-80 bg-background border-border rounded-lg shadow-lg p-0"
          >
            <Profile01
              name={user?.name}
              role={user?.role}
              avatar="https://ferf1mheo22r9ira.public.blob.vercel-storage.com/avatar-01-n0x8HFv8EUetf9z6ht0wScJKoTHqf8.png"
            />
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </nav>
  )
}

