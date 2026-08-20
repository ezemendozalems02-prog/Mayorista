"use client"

import React from "react"
import { useOnboarding } from "./onboarding-context"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Button } from "@/components/ui/button"
import {
  Package,
  Barcode,
  Scan,
  Tags,
  Boxes,
  Users,
  ShoppingCart,
  Receipt,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Sparkles,
  LayoutDashboard,
  Layers,
  ListOrdered,
} from "lucide-react"

interface TourStep {
  title: string
  subtitle: string
  icon: any
  badgeColor: string
  content: React.ReactNode
}

export function OnboardingModal() {
  const { isOpen, currentStep, setStep, completeTour, skipTour } = useOnboarding()

  const steps: TourStep[] = [
    {
      title: "Bienvenido a Mito Yamile",
      subtitle: "Tu sistema de gestión mayorista para administrar productos, stock, ventas y clientes en Pesos Argentinos ($ ARS).",
      icon: Sparkles,
      badgeColor: "bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900",
      content: (
        <div className="space-y-4">
          <p className="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
            Vamos a mostrarte rápidamente cómo funciona el sistema y cuál es el orden recomendado para comenzar a operar.
          </p>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
            <div className="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 rounded-xl border border-blue-100 dark:border-blue-900/30">
              <Package className="w-5 h-5 text-blue-600 dark:text-blue-400 mb-1" />
              <h4 className="font-semibold text-xs text-gray-900 dark:text-white">Productos & Stock</h4>
              <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Controlá existencias reales y precios en $ ARS.</p>
            </div>
            <div className="p-3.5 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30">
              <ShoppingCart className="w-5 h-5 text-emerald-600 dark:text-emerald-400 mb-1" />
              <h4 className="font-semibold text-xs text-gray-900 dark:text-white">Ventas & Clientes</h4>
              <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Facturá rápido y llevá cuentas corrientes.</p>
            </div>
            <div className="p-3.5 bg-indigo-50/50 dark:bg-indigo-950/20 rounded-xl border border-indigo-100 dark:border-indigo-900/30">
              <Receipt className="w-5 h-5 text-indigo-600 dark:text-indigo-400 mb-1" />
              <h4 className="font-semibold text-xs text-gray-900 dark:text-white">Facturación ARCA</h4>
              <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Emití comprobantes fiscales homologados.</p>
            </div>
          </div>
        </div>
      ),
    },
    {
      title: "1. Tu Panel Principal (Dashboard)",
      subtitle: "Resumen en tiempo real expresado en Pesos Argentinos ($ ARS).",
      icon: LayoutDashboard,
      badgeColor: "bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-900",
      content: (
        <div className="space-y-4">
          <p className="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
            El Dashboard concentra tus métricas del día y alertas para que sepas el estado de tu local al instante:
          </p>
          <div className="space-y-2.5">
            <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
              <span className="p-2 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-bold">$ ARS</span>
              <div>
                <h5 className="font-semibold text-xs text-gray-900 dark:text-white">Ventas del Día / Mes ($ ARS)</h5>
                <p className="text-[11px] text-gray-500 dark:text-gray-400">Total acumulado de lo que has facturado y vendido.</p>
              </div>
            </div>
            <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
              <span className="p-2 bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 rounded-lg text-xs font-bold">Stock</span>
              <div>
                <h5 className="font-semibold text-xs text-gray-900 dark:text-white">Stock Disponible y Alertas</h5>
                <p className="text-[11px] text-gray-500 dark:text-gray-400">Existencias físicas y aviso de reposición por stock bajo.</p>
              </div>
            </div>
            <div className="flex items-start gap-3 p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
              <span className="p-2 bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 rounded-lg text-xs font-bold">Cuentas</span>
              <div>
                <h5 className="font-semibold text-xs text-gray-900 dark:text-white">Cuentas Corrientes Pendientes</h5>
                <p className="text-[11px] text-gray-500 dark:text-gray-400">Resumen de saldos adeudados por clientes mayoristas.</p>
              </div>
            </div>
          </div>
        </div>
      ),
    },
    {
      title: "2. Ficha de Productos y Precios",
      subtitle: "Administrá los artículos de tu catálogo en Pesos Argentinos.",
      icon: Package,
      badgeColor: "bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900",
      content: (
        <div className="space-y-4">
          <p className="text-gray-600 dark:text-gray-300 text-sm">
            Cada producto tiene su propia ficha completa. Podés encontrarlo por nombre o escaneando su código:
          </p>
          <div className="grid grid-cols-2 gap-2 text-xs">
            <div className="p-2.5 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">
              <span className="font-bold text-gray-900 dark:text-white block">Nombre & Código</span>
              <span className="text-gray-500 text-[11px]">Descripción del artículo.</span>
            </div>
            <div className="p-2.5 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">
              <span className="font-bold text-gray-900 dark:text-white block">Categoría & Marca</span>
              <span className="text-gray-500 text-[11px]">Juguetería, Librería, Regalería.</span>
            </div>
            <div className="p-2.5 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">
              <span className="font-bold text-gray-900 dark:text-white block">Costo & Precios ($ ARS)</span>
              <span className="text-gray-500 text-[11px]">Costo, precio minorista y mayorista.</span>
            </div>
            <div className="p-2.5 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">
              <span className="font-bold text-gray-900 dark:text-white block">Stock Mínimo</span>
              <span className="text-gray-500 text-[11px]">Alerta para no quedarte sin unidades.</span>
            </div>
          </div>
        </div>
      ),
    },
    {
      title: "3. Código de Barras vs. Código Interno",
      subtitle: "Diferenciá los códigos para simplificar la carga.",
      icon: Barcode,
      badgeColor: "bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-900",
      content: (
        <div className="space-y-4">
          <div className="p-3.5 bg-purple-50/50 dark:bg-purple-950/20 rounded-xl border border-purple-100 dark:border-purple-900/30">
            <h5 className="font-bold text-xs text-purple-900 dark:text-purple-300 flex items-center gap-2 mb-1">
              <Barcode className="w-4 h-4" /> Código de Barras (de Fábrica)
            </h5>
            <p className="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
              Es el código de barras que ya viene en el empaque. Al escanearlo, Mito lo reconoce al instante.
            </p>
          </div>

          <div className="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 rounded-xl border border-blue-100 dark:border-blue-900/30">
            <h5 className="font-bold text-xs text-blue-900 dark:text-blue-300 flex items-center gap-2 mb-1">
              <Tags className="w-4 h-4" /> Código Interno (`MIT-000245`)
            </h5>
            <p className="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
              Si un producto no tiene código de barras (artículos sueltos o regalería), el sistema genera una clave única como <span className="font-mono bg-blue-100 dark:bg-blue-900/50 px-1.5 py-0.5 rounded font-bold">MIT-000245</span> para identificarlo en el local.
            </p>
          </div>
        </div>
      ),
    },
    {
      title: "4. Escáner de Código de Barras",
      subtitle: "Leé productos automáticamente con tu lector USB o Bluetooth.",
      icon: Scan,
      badgeColor: "bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-200 dark:border-cyan-900",
      content: (
        <div className="space-y-4">
          <p className="text-gray-600 dark:text-gray-300 text-sm">
            Mito permite usar lectores de código de barras sin necesidad de configurar drivers extra:
          </p>
          <div className="flex flex-col items-center justify-center p-4 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-200 dark:border-[#222226] text-center space-y-2">
            <div className="flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200">
              <span>ESCANEAR</span>
              <span>➔</span>
              <span>BÚSQUEDA AUTOMÁTICA</span>
              <span>➔</span>
              <span>PRODUCTO ENCONTRADO</span>
            </div>
            <p className="text-[11px] text-gray-500 dark:text-gray-400">
              Si el código no está cargado, Mito te da la opción <strong>[Crear producto]</strong> para agregarlo inmediatamente.
            </p>
          </div>
        </div>
      ),
    },
    {
      title: "5. Categorías, Marcas y Proveedores",
      subtitle: "Estructurá tu catálogo antes de cargar existencias.",
      icon: Layers,
      badgeColor: "bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900",
      content: (
        <div className="space-y-3 text-xs">
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
            <span className="font-bold text-gray-900 dark:text-white block mb-0.5">Categorías & Subcategorías</span>
            <p className="text-gray-500 text-[11px]">
              Ejemplos: <strong>Juguetería</strong> (Juegos de mesa, Muñecas, Autos), <strong>Librería</strong> (Cuadernos, Útiles), <strong>Regalería</strong>.
            </p>
          </div>
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
            <span className="font-bold text-gray-900 dark:text-white block mb-0.5">Marcas</span>
            <p className="text-gray-500 text-[11px]">
              Ejemplos: Marvel, Disney, Pelikan, Maped, Rivadavia.
            </p>
          </div>
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
            <span className="font-bold text-gray-900 dark:text-white block mb-0.5">Proveedores</span>
            <p className="text-gray-500 text-[11px]">
              Proveedores de mercadería (Razón Social, CUIT, WhatsApp, lista de costos enviada).
            </p>
          </div>
        </div>
      ),
    },
    {
      title: "6. Stock y Movimientos",
      subtitle: "Auditoría transparente de existencias físicas.",
      icon: Boxes,
      badgeColor: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900",
      content: (
        <div className="space-y-4">
          <p className="text-gray-600 dark:text-gray-300 text-sm">
            Cada cambio en las existencias genera un movimiento registrado:
          </p>
          <div className="grid grid-cols-2 gap-2 text-xs">
            <div className="p-2.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-300 rounded-lg border border-emerald-200 dark:border-emerald-900/40 font-medium">
              + ENTRADA / COMPRA (+50 u)
            </div>
            <div className="p-2.5 bg-blue-50 dark:bg-blue-950/20 text-blue-900 dark:text-blue-300 rounded-lg border border-blue-200 dark:border-blue-900/40 font-medium">
              - VENTA EN LOCAL (-3 u)
            </div>
            <div className="p-2.5 bg-rose-50 dark:bg-rose-950/20 text-rose-900 dark:text-rose-300 rounded-lg border border-rose-200 dark:border-rose-900/40 font-medium">
              - PÉRDIDA / DAÑO (-1 u)
            </div>
            <div className="p-2.5 bg-amber-50 dark:bg-amber-950/20 text-amber-900 dark:text-amber-300 rounded-lg border border-amber-200 dark:border-amber-900/40 font-medium">
              + DEVOLUCIÓN (+2 u)
            </div>
          </div>
        </div>
      ),
    },
    {
      title: "7. Clientes, Precios en $ ARS y Cuenta Corriente",
      subtitle: "Listas de precios en Pesos Argentinos y saldos pendientes.",
      icon: Users,
      badgeColor: "bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-200 dark:border-teal-900",
      content: (
        <div className="space-y-3 text-xs">
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
            <span className="font-bold text-gray-900 dark:text-white block mb-0.5">Listas de Precio ($ ARS)</span>
            <p className="text-gray-500 text-[11px]">
              Ejemplo Cuaderno: Minorista $ 5.000 ARS | Mayorista $ 4.500 ARS (10+ u: $ 4.200 ARS). El sistema liquida automáticamente el valor correspondiente.
            </p>
          </div>
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226]">
            <span className="font-bold text-gray-900 dark:text-white block mb-0.5">Cuenta Corriente</span>
            <p className="text-gray-500 text-[11px]">
              Compra (+ $ 100.000 ARS) ➔ Entrega pago (- $ 40.000 ARS) ➔ Saldo adeudado ($ 60.000 ARS).
            </p>
          </div>
        </div>
      ),
    },
    {
      title: "8. Ventas, Caja y Facturación ARCA",
      subtitle: "Operativa de cobro y emisión de comprobantes.",
      icon: ShoppingCart,
      badgeColor: "bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-900",
      content: (
        <div className="space-y-4">
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226] text-xs">
            <span className="font-bold text-gray-900 dark:text-white block mb-1">Nueva Venta:</span>
            <p className="text-gray-600 dark:text-gray-400 text-[11px]">
              Escanear producto ➔ Seleccionar cliente ➔ Elegir medio de pago (Efectivo ARS, Transferencia, Mercado Pago, Cta. Cte.) ➔ Confirmar.
            </p>
          </div>
          <div className="p-3 bg-gray-50 dark:bg-[#161619] rounded-xl border border-gray-100 dark:border-[#222226] text-xs">
            <span className="font-bold text-gray-900 dark:text-white block mb-1">Facturación ARCA (AFIP):</span>
            <p className="text-gray-600 dark:text-gray-400 text-[11px]">
              Emití facturas electrónicas A, B o C directamente desde el módulo fiscal.
            </p>
          </div>
        </div>
      ),
    },
    {
      title: "9. Orden Recomendado para Empezar",
      subtitle: "Seguí la lista de verificación para poner en marcha Mito Yamile.",
      icon: ListOrdered,
      badgeColor: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900",
      content: (
        <div className="space-y-4">
          <p className="text-xs text-gray-600 dark:text-gray-300">
            Paso a paso recomendado:
          </p>
          <ol className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
            <li className="p-2 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">1. Configurar datos del negocio</li>
            <li className="p-2 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">2. Crear Categorías y Marcas</li>
            <li className="p-2 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">3. Cargar Proveedores</li>
            <li className="p-2 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">4. Cargar Productos y Precios ($ ARS)</li>
            <li className="p-2 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">5. Ingresar Stock Inicial</li>
            <li className="p-2 bg-gray-50 dark:bg-[#161619] rounded-lg border border-gray-100 dark:border-[#222226]">6. Realizar primera Venta</li>
          </ol>
        </div>
      ),
    },
  ]

  const step = steps[currentStep] || steps[0]
  const Icon = step.icon
  const totalSteps = steps.length

  const handleNext = () => {
    if (currentStep < totalSteps - 1) {
      setStep(currentStep + 1)
    } else {
      completeTour()
    }
  }

  const handlePrev = () => {
    if (currentStep > 0) {
      setStep(currentStep - 1)
    }
  }

  return (
    <Dialog open={isOpen} onOpenChange={(open) => !open && skipTour()}>
      <DialogContent className="max-w-2xl bg-white dark:bg-[#0F0F12] border-gray-200 dark:border-[#1F1F23] p-6 shadow-xl rounded-2xl">
        <DialogHeader>
          <div className="flex items-center justify-between gap-3 mb-2">
            <div className="flex items-center gap-2.5">
              <div className={`p-2.5 rounded-xl border ${step.badgeColor}`}>
                <Icon className="w-5 h-5" />
              </div>
              <div>
                <DialogTitle className="text-lg font-bold text-gray-900 dark:text-white">
                  {step.title}
                </DialogTitle>
                <DialogDescription className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  {step.subtitle}
                </DialogDescription>
              </div>
            </div>
          </div>
        </DialogHeader>

        <div className="my-4 min-h-[220px]">{step.content}</div>

        <div className="pt-4 border-t border-gray-100 dark:border-[#1F1F23] flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-1.5">
            {steps.map((_, idx) => (
              <button
                key={idx}
                type="button"
                onClick={() => setStep(idx)}
                className={`w-2.5 h-2.5 rounded-full transition-all ${
                  idx === currentStep
                    ? "bg-blue-600 dark:bg-blue-400 w-6"
                    : "bg-gray-200 dark:bg-gray-700 hover:bg-gray-300"
                }`}
                aria-label={`Ir al paso ${idx + 1}`}
              />
            ))}
            <span className="text-[11px] font-medium text-gray-400 ml-2">
              Paso {currentStep + 1} de {totalSteps}
            </span>
          </div>

          <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
            {currentStep === 0 ? (
              <Button
                variant="outline"
                size="sm"
                onClick={skipTour}
                className="text-gray-500 hover:text-gray-700 dark:text-gray-400"
              >
                Omitir por ahora
              </Button>
            ) : (
              <Button
                variant="outline"
                size="sm"
                onClick={handlePrev}
                className="gap-1"
              >
                <ChevronLeft className="w-4 h-4" />
                Anterior
              </Button>
            )}

            <Button
              size="sm"
              onClick={handleNext}
              className="bg-blue-600 hover:bg-blue-700 text-white gap-1 px-4 font-bold"
            >
              {currentStep === totalSteps - 1 ? (
                <>
                  Empezar a usar Mito
                  <CheckCircle2 className="w-4 h-4 ml-1" />
                </>
              ) : currentStep === 0 ? (
                <>
                  Comenzar recorrido
                  <ChevronRight className="w-4 h-4" />
                </>
              ) : (
                <>
                  Siguiente
                  <ChevronRight className="w-4 h-4" />
                </>
              )}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  )
}
