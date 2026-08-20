import { Inter } from "next/font/google"
import "./globals.css"
import { ThemeProvider } from "@/components/theme-provider"

const inter = Inter({ subsets: ["latin"] })

export const metadata = {
  title: "Mito Yamile",
  description: "Sistema de gestión mayorista — Juguetería, Librería y Regalería",
}

import { OnboardingProvider } from "@/components/onboarding/onboarding-context"

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className={inter.className}>
        <ThemeProvider attribute="class" defaultTheme="system" enableSystem disableTransitionOnChange>
          <OnboardingProvider>
            {children}
          </OnboardingProvider>
        </ThemeProvider>
      </body>
    </html>
  )
}

