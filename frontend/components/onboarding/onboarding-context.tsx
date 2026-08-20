"use client"

import React, { createContext, useContext, useEffect, useState } from "react"

export interface ChecklistItem {
  id: string
  title: string
  description: string
  href: string
  category: string
}

export const DEFAULT_CHECKLIST_ITEMS: ChecklistItem[] = [
  {
    id: "organization",
    title: "1. Configurar negocio y datos base",
    description: "Cargá los datos de tu empresa, sucursal y depósitos principales.",
    href: "/organization",
    category: "Configuración",
  },
  {
    id: "categories",
    title: "2. Crear categorías",
    description: "Organizá tu catálogo en familias de productos (ej. Juguetería, Librería, Regalería).",
    href: "/inventory",
    category: "Catálogo",
  },
  {
    id: "brands",
    title: "3. Crear marcas",
    description: "Asigná marcas a tus productos para filtrar rápidamente.",
    href: "/inventory",
    category: "Catálogo",
  },
  {
    id: "suppliers",
    title: "4. Cargar proveedores",
    description: "Registrá a quiénes comprás mercadería para vincular costos y compras.",
    href: "/inventory",
    category: "Catálogo",
  },
  {
    id: "products",
    title: "5. Cargar tus primeros productos",
    description: "Ingresá nombre, código de barras o código interno, costos y precios.",
    href: "/inventory",
    category: "Productos",
  },
  {
    id: "stock",
    title: "6. Revisar y cargar stock inicial",
    description: "Registrá los ingresos de mercadería mediante movimientos de stock.",
    href: "/inventory",
    category: "Stock",
  },
  {
    id: "clients",
    title: "7. Cargar clientes",
    description: "Registrá la información de tus clientes y asignales listas de precio.",
    href: "/clients",
    category: "Clientes",
  },
  {
    id: "facturacion",
    title: "8. Configurar facturación ARCA",
    description: "Cargá tus datos fiscales y punto de venta para emitir comprobantes.",
    href: "/facturacion-arca/configuracion",
    category: "Facturación",
  },
  {
    id: "first_sale",
    title: "9. Realizar tu primera venta",
    description: "Buscá o escaneá un producto, elegí el cliente y completá el pago.",
    href: "/sales",
    category: "Ventas",
  },
]

interface OnboardingContextType {
  isOpen: boolean
  currentStep: number
  hasCompleted: boolean
  checklist: Record<string, boolean>
  openTour: (step?: number) => void
  closeTour: () => void
  setStep: (step: number) => void
  completeTour: () => void
  skipTour: () => void
  toggleChecklistItem: (id: string) => void
  resetOnboarding: () => void
}

const OnboardingContext = createContext<OnboardingContextType | undefined>(undefined)

const ONBOARDING_COMPLETED_KEY = "mito_onboarding_completed"
const ONBOARDING_CHECKLIST_KEY = "mito_onboarding_checklist"

export function OnboardingProvider({ children }: { children: React.ReactNode }) {
  const [isOpen, setIsOpen] = useState(false)
  const [currentStep, setCurrentStep] = useState(0)
  const [hasCompleted, setHasCompleted] = useState(false)
  const [checklist, setChecklist] = useState<Record<string, boolean>>({})

  useEffect(() => {
    if (typeof window !== "undefined") {
      const completed = localStorage.getItem(ONBOARDING_COMPLETED_KEY)
      const savedChecklist = localStorage.getItem(ONBOARDING_CHECKLIST_KEY)

      if (completed === "true") {
        setHasCompleted(true)
      } else {
        setIsOpen(true)
      }

      if (savedChecklist) {
        try {
          setChecklist(JSON.parse(savedChecklist))
        } catch {
          setChecklist({})
        }
      }
    }
  }, [])

  const openTour = (step = 0) => {
    setCurrentStep(step)
    setIsOpen(true)
  }

  const closeTour = () => {
    setIsOpen(false)
  }

  const setStep = (step: number) => {
    setCurrentStep(step)
  }

  const completeTour = () => {
    setHasCompleted(true)
    setIsOpen(false)
    if (typeof window !== "undefined") {
      localStorage.setItem(ONBOARDING_COMPLETED_KEY, "true")
    }
  }

  const skipTour = () => {
    completeTour()
  }

  const toggleChecklistItem = (id: string) => {
    setChecklist((prev) => {
      const updated = { ...prev, [id]: !prev[id] }
      if (typeof window !== "undefined") {
        localStorage.setItem(ONBOARDING_CHECKLIST_KEY, JSON.stringify(updated))
      }
      return updated
    })
  }

  const resetOnboarding = () => {
    setHasCompleted(false)
    if (typeof window !== "undefined") {
      localStorage.removeItem(ONBOARDING_COMPLETED_KEY)
    }
    openTour(0)
  }

  return (
    <OnboardingContext.Provider
      value={{
        isOpen,
        currentStep,
        hasCompleted,
        checklist,
        openTour,
        closeTour,
        setStep,
        completeTour,
        skipTour,
        toggleChecklistItem,
        resetOnboarding,
      }}
    >
      {children}
    </OnboardingContext.Provider>
  )
}

export function useOnboarding() {
  const context = useContext(OnboardingContext)
  if (!context) {
    throw new Error("useOnboarding debe ser usado dentro de un OnboardingProvider")
  }
  return context
}
