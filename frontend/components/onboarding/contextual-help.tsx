"use client"

import React from "react"
import { HelpCircle } from "lucide-react"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip"

interface ContextualHelpProps {
  title?: string
  content: string
  side?: "top" | "right" | "bottom" | "left"
}

export function ContextualHelp({ title, content, side = "top" }: ContextualHelpProps) {
  return (
    <TooltipProvider>
      <Tooltip delayDuration={200}>
        <TooltipTrigger asChild>
          <button
            type="button"
            className="inline-flex items-center justify-center p-0.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors focus:outline-none ml-1"
            aria-label="Más información"
          >
            <HelpCircle className="w-3.5 h-3.5" />
          </button>
        </TooltipTrigger>
        <TooltipContent side={side} className="max-w-xs p-3 text-xs leading-relaxed bg-gray-900 text-white dark:bg-gray-800 dark:text-gray-100 shadow-md">
          {title && <p className="font-bold mb-1 border-b border-gray-700 pb-1">{title}</p>}
          <p>{content}</p>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  )
}
