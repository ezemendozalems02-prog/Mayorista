"use client"

import React from "react"
import Layout from "@/components/kokonutui/layout"
import { useOnboarding } from "@/components/onboarding/onboarding-context"
import { SetupChecklist } from "@/components/onboarding/setup-checklist"
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion"
import { Button } from "@/components/ui/button"
import {
  HelpCircle,
  PlayCircle,
  Package,
  Boxes,
  ShoppingCart,
  Users,
  CreditCard,
  Receipt,
  BarChart2,
  Scan,
  Layers,
  Building2,
  Tags,
} from "lucide-react"

export default function HelpPage() {
  const { openTour, resetOnboarding } = useOnboarding()

  const helpTopics = [
    {
      id: "productos",
      icon: Package,
      title: "Productos y Ficha de Artículo",
      content:
        "Los productos son todos los artículos que comercializás. Cada producto incluye Nombre, Categoría, Marca, Costo, Precio minorista y Precio mayorista. También podés configurar un 'Stock Mínimo' para que el sistema te alerte antes de quedarte sin existencias.",
    },
    {
      id: "codigos",
      icon: Scan,
      title: "Código de Barras vs. Código Interno (MIT-XXXXXX)",
      content:
        "El Código de Barras es el código numérico impreso en el producto por el fabricante. Si el artículo no lo posee, Mito le genera automáticamente un 'Código Interno' (ej. MIT-000245) para que puedas etiquetarlo e identificarlo en tu comercio sin depender de códigos de fábrica.",
    },
    {
      id: "escaner",
      icon: Tags,
      title: "Uso del Escáner de Código de Barras",
      content:
        "Podés conectar cualquier lector de código de barras USB o Bluetooth. Al estar en la pantalla de ventas o inventario, al escanear un código el sistema buscará automáticamente el producto. Si no lo encuentra, te ofrecerá un acceso directo para [Crear producto].",
    },
    {
      id: "stock",
      icon: Boxes,
      title: "Stock y Movimientos de Mercadería",
      content:
        "El stock es la cantidad física disponible. Mito registra todo movimiento: Compras (+), Ventas (-), Pérdidas (-), Devoluciones (+) y Transferencias entre depósitos. No se recomienda modificar cantidades sin justificar el movimiento para mantener el historial impecable.",
    },
    {
      id: "clientes",
      icon: Users,
      title: "Clientes y Listas de Precio",
      content:
        "Podés registrar clientes finales o mayoristas con sus datos de contacto (WhatsApp, CUIT, Dirección). Asignales una Lista de Precios determinada (Minorista, Mayorista o con descuentos por volumen) para que el sistema calcule los montos exactos en cada venta.",
    },
    {
      id: "cuenta-corriente",
      icon: CreditCard,
      title: "Cuenta Corriente y Saldos",
      content:
        "La Cuenta Corriente permite vender a crédito y registrar entregas de dinero a cuenta. Cada venta fianza el saldo (+ compra, - pago). Podés consultar el estado de cuenta e imprimir comprobantes de entrega o pago en cualquier momento.",
    },
    {
      id: "ventas",
      icon: ShoppingCart,
      title: "Ventas y Formas de Pago",
      content:
        "Registrá ventas buscando o escaneando productos. Podés cobrar en Efectivo, Mercado Pago, Transferencia o Cuenta Corriente. La venta actualiza automáticamente las existencias de stock y genera el movimiento de caja.",
    },
    {
      id: "arca",
      icon: Receipt,
      title: "Facturación Electrónica ARCA (ex AFIP)",
      content:
        "Desde la sección Facturación ARCA podés asociar tus certificados fiscales y CUIT. Podés emitir Facturas A, B o C directamente y descargar el PDF o enviar el comprobante electrónico autorizado.",
    },
    {
      id: "reportes",
      icon: BarChart2,
      title: "Reportes y Estadísticas",
      content:
        "Obtené reportes detallados de ventas por día/mes, ganancias estimadas, artículos más vendidos y productos que requieren reposición urgente por stock bajo.",
    },
  ]

  return (
    <Layout>
      <div className="max-w-5xl mx-auto space-y-8 pb-12">
        {/* Banner principal */}
        <div className="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 sm:p-8 text-white shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div className="space-y-2 max-w-xl">
            <div className="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-xs font-semibold backdrop-blur-sm">
              <HelpCircle className="w-4 h-4" />
              Centro de Ayuda Mito Yamile
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
              ¿Querés repasar cómo funciona el sistema?
            </h1>
            <p className="text-blue-100 text-sm leading-relaxed">
              Volvé a ejecutar el recorrido interactivo paso a paso cuando quieras para recordar cómo manejar productos, stock, ventas y facturación.
            </p>
          </div>

          <Button
            onClick={() => openTour(0)}
            size="lg"
            className="bg-white text-blue-700 hover:bg-blue-50 font-bold shadow-md gap-2 shrink-0 border-0"
          >
            <PlayCircle className="w-5 h-5 text-blue-600" />
            Ver recorrido inicial
          </Button>
        </div>

        {/* Checklist de inicio */}
        <SetupChecklist />

        {/* Preguntas frecuentes y guias por módulo */}
        <div className="bg-white dark:bg-[#0F0F12] border border-gray-200 dark:border-[#1F1F23] rounded-2xl p-6 shadow-sm space-y-4">
          <div>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">
              Guía de Funcionalidades por Módulo
            </h2>
            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
              Hacé clic en cualquier tema para leer la explicación resumida.
            </p>
          </div>

          <Accordion type="single" collapsible className="w-full">
            {helpTopics.map((topic) => {
              const Icon = topic.icon
              return (
                <AccordionItem key={topic.id} value={topic.id} className="border-gray-100 dark:border-[#1F1F23]">
                  <AccordionTrigger className="hover:no-underline py-4">
                    <div className="flex items-center gap-3 text-left">
                      <div className="p-2 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-lg">
                        <Icon className="w-4 h-4" />
                      </div>
                      <span className="font-semibold text-sm text-gray-900 dark:text-white">
                        {topic.title}
                      </span>
                    </div>
                  </AccordionTrigger>
                  <AccordionContent className="text-sm text-gray-600 dark:text-gray-300 leading-relaxed pl-11 pb-4">
                    {topic.content}
                  </AccordionContent>
                </AccordionItem>
              )
            })}
          </Accordion>
        </div>
      </div>
    </Layout>
  )
}
