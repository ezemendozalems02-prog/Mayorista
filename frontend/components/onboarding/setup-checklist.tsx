"use client"

import React from "react"
import Link from "next/link"
import { CheckCircle2, Circle, ArrowRight, Sparkles } from "lucide-react"
import { DEFAULT_CHECKLIST_ITEMS, useOnboarding } from "./onboarding-context"
import { Progress } from "@/components/ui/progress"
import { Badge } from "@/components/ui/badge"

export function SetupChecklist() {
  const { checklist, toggleChecklistItem } = useOnboarding()

  const completedCount = DEFAULT_CHECKLIST_ITEMS.filter((item) => checklist[item.id]).length
  const totalCount = DEFAULT_CHECKLIST_ITEMS.length
  const progressPercentage = Math.round((completedCount / totalCount) * 100)

  return (
    <div className="bg-white dark:bg-[#0F0F12] border border-gray-200 dark:border-[#1F1F23] rounded-2xl p-6 shadow-sm">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-6 border-b border-gray-100 dark:border-[#1F1F23]">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <Sparkles className="w-5 h-5 text-blue-600 dark:text-blue-400" />
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">¿Por dónde empiezo?</h2>
          </div>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Seguí este orden recomendado para configurar Mito Yamile de manera óptima.
          </p>
        </div>

        <div className="flex items-center gap-4 bg-gray-50 dark:bg-[#151518] p-3 rounded-xl border border-gray-100 dark:border-[#1F1F23]">
          <div className="w-32">
            <div className="flex justify-between text-xs font-semibold mb-1">
              <span className="text-gray-500">Progreso</span>
              <span className="text-blue-600 dark:text-blue-400">{progressPercentage}%</span>
            </div>
            <Progress value={progressPercentage} className="h-2" />
          </div>
          <div className="text-right border-l border-gray-200 dark:border-[#26262B] pl-4">
            <span className="text-xs text-gray-400 block">Completados</span>
            <span className="text-lg font-bold text-gray-900 dark:text-white">
              {completedCount} / {totalCount}
            </span>
          </div>
        </div>
      </div>

      <div className="space-y-3">
        {DEFAULT_CHECKLIST_ITEMS.map((item) => {
          const isDone = !!checklist[item.id]
          return (
            <div
              key={item.id}
              className={`flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl transition-all border ${
                isDone
                  ? "bg-emerald-50/40 dark:bg-emerald-950/10 border-emerald-200/60 dark:border-emerald-900/30"
                  : "bg-white dark:bg-[#0F0F12] border-gray-200/80 dark:border-[#1F1F23] hover:border-gray-300 dark:hover:border-[#2A2A30]"
              }`}
            >
              <div className="flex items-start gap-3 flex-1 mb-2 sm:mb-0">
                <button
                  type="button"
                  onClick={() => toggleChecklistItem(item.id)}
                  className="mt-0.5 focus:outline-none"
                >
                  {isDone ? (
                    <CheckCircle2 className="w-5 h-5 text-emerald-600 dark:text-emerald-400 fill-emerald-100 dark:fill-emerald-950" />
                  ) : (
                    <Circle className="w-5 h-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                  )}
                </button>
                <div>
                  <div className="flex items-center gap-2">
                    <span
                      className={`font-semibold text-sm ${
                        isDone
                          ? "line-through text-gray-500 dark:text-gray-400"
                          : "text-gray-900 dark:text-white"
                      }`}
                    >
                      {item.title}
                    </span>
                    <Badge variant="outline" className="text-[10px] py-0 px-2 font-medium">
                      {item.category}
                    </Badge>
                  </div>
                  <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{item.description}</p>
                </div>
              </div>

              <Link
                href={item.href}
                className="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline self-end sm:self-center ml-8 sm:ml-0"
              >
                Ir a la sección
                <ArrowRight className="w-3.5 h-3.5" />
              </Link>
            </div>
          )
        })}
      </div>
    </div>
  )
}
