"use client"

import { Loader2 } from "lucide-react"

export function LoadingScreen() {
    return (
        <div className="flex flex-col items-center justify-center min-h-[400px] w-full gap-4">
            <div className="relative">
                <Loader2 className="w-10 h-10 text-blue-600 animate-spin" />
                <div className="absolute inset-0 w-10 h-10 text-blue-600/20 blur-sm animate-pulse">
                    <Loader2 className="w-10 h-10" />
                </div>
            </div>
            <p className="text-sm font-medium text-gray-500 animate-pulse">Cargando datos del sistema...</p>
        </div>
    )
}

export function SkeletonCard() {
    return (
        <div className="bg-white dark:bg-[#0F0F12] p-5 rounded-xl border border-gray-200 dark:border-[#1F1F23] flex items-center gap-4 animate-pulse">
            <div className="w-12 h-12 rounded-lg bg-gray-200 dark:bg-[#1F1F23]" />
            <div className="flex-1 space-y-2">
                <div className="h-2 w-20 bg-gray-200 dark:bg-[#1F1F23] rounded" />
                <div className="h-4 w-32 bg-gray-200 dark:bg-[#1F1F23] rounded" />
            </div>
        </div>
    )
}
