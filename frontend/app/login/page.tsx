"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { API_URL } from "@/lib/api"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { toast } from "sonner"
import { Lock, Mail, Store, ShieldCheck } from "lucide-react"

export default function LoginPage() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const router = useRouter()

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)

    try {
      const res = await fetch(`${API_URL}/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ email, password }),
      })

      const data = await res.json()

      if (!res.ok) {
        throw new Error(data.message || "Credenciales incorrectas")
      }

      // Guardar token y usuario
      document.cookie = `auth_token=${data.token}; path=/; max-age=86400`
      localStorage.setItem("auth_token", data.token)
      localStorage.setItem("user", JSON.stringify(data.user))

      toast.success("Bienvenido a Mito Yamile")
      router.push("/dashboard")
    } catch (error: any) {
      toast.error(error.message)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-4 text-slate-100">
      <div className="w-full max-w-md space-y-6">
        <div className="text-center space-y-2">
          <div className="inline-flex items-center justify-center p-3.5 bg-blue-600/10 border border-blue-500/20 rounded-2xl mb-2 backdrop-blur-md">
            <Store className="w-8 h-8 text-blue-400" />
          </div>
          <h1 className="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-blue-400 via-indigo-300 to-purple-400 bg-clip-text text-transparent">
            Mito Yamile
          </h1>
          <p className="text-sm text-slate-400 font-medium">
            Sistema de Gestión Mayorista — Juguetería, Librería y Regalería
          </p>
        </div>

        <Card className="bg-slate-900/80 border-slate-800/80 shadow-2xl backdrop-blur-xl rounded-2xl overflow-hidden">
          <CardHeader className="space-y-1 pb-4 text-center border-b border-slate-800/60">
            <CardTitle className="text-lg font-bold text-slate-100 flex items-center justify-center gap-2">
              <ShieldCheck className="w-5 h-5 text-emerald-400" />
              Acceso Único al Sistema
            </CardTitle>
            <CardDescription className="text-xs text-slate-400">
              Ingresá tus credenciales autorizadas de administración
            </CardDescription>
          </CardHeader>

          <CardContent className="pt-6">
            <form onSubmit={handleLogin} className="space-y-4">
              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-300">Correo Electrónico</label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                  <Input
                    type="email"
                    placeholder="admin@mito.com"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    className="pl-9 bg-slate-950/60 border-slate-800 text-slate-100 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500/20"
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-semibold text-slate-300">Contraseña</label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                  <Input
                    type="password"
                    placeholder="••••••••"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                    className="pl-9 bg-slate-950/60 border-slate-800 text-slate-100 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500/20"
                  />
                </div>
              </div>

              <Button
                type="submit"
                disabled={isLoading}
                className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold h-11 rounded-xl shadow-lg shadow-blue-600/25 transition-all mt-2"
              >
                {isLoading ? "Iniciando sesión..." : "Ingresar a Mito"}
              </Button>
            </form>
          </CardContent>
        </Card>

        <p className="text-center text-xs text-slate-500">
          Mito Yamile © Sistema Exclusivo de Gestión
        </p>
      </div>
    </div>
  )
}
