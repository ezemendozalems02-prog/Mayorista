"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { API_URL } from "@/lib/api"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { toast } from "sonner"

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
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ email, password }),
            })

            const data = await res.json()

            if (!res.ok) {
                throw new Error(data.message || "Error al iniciar sesión")
            }

            // Store token (using Cookies for middleware support)
            document.cookie = `auth_token=${data.token}; path=/; max-age=86400`
            localStorage.setItem("auth_token", data.token)
            localStorage.setItem("user", JSON.stringify(data.user))

            toast.success("Bienvenido al sistema")
            router.push("/dashboard")
        } catch (error: any) {
            toast.error(error.message)
        } finally {
            setIsLoading(false)
        }
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-[#09090B] px-4">
            <Card className="w-full max-w-md border-gray-200 dark:border-[#1F1F23]">
                <CardHeader className="space-y-1 text-center">
                    <CardTitle className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        Mito Yamile
                    </CardTitle>
                    <CardDescription>
                        Gestión profesional para locales de iPhone
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleLogin} className="space-y-4">
                        <div className="space-y-2">
                            <Input
                                type="email"
                                placeholder="email@demo.com"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                required
                                className="bg-white dark:bg-[#0F0F12]"
                            />
                        </div>
                        <div className="space-y-2">
                            <Input
                                type="password"
                                placeholder="********"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                required
                                className="bg-white dark:bg-[#0F0F12]"
                            />
                        </div>
                        <Button className="w-full bg-blue-600 hover:bg-blue-700 text-white" disabled={isLoading}>
                            {isLoading ? "Iniciando sesión..." : "Ingresar"}
                        </Button>
                    </form>
                    <div className="mt-6 text-center text-sm text-gray-500">
                        <p>Demo Admin: admin@admin.com / password</p>
                        <p>Demo Owner: owner@demo.com / password</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    )
}
