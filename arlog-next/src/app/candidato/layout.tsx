import Link from "next/link";
import { User, Briefcase, FileText, LogOut, LayoutDashboard } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function CandidatoLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <div className="min-h-screen bg-slate-950 flex">
            {/* Sidebar Navigation */}
            <aside className="w-64 border-r border-white/10 bg-slate-900/50 hidden md:flex flex-col">
                <div className="p-6 border-b border-white/10">
                    <Link href="/" className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center font-bold text-slate-950">A</div>
                        <span className="font-bold text-xl text-white tracking-tight">ArLog<span className="text-emerald-400">Jobs</span></span>
                    </Link>
                </div>

                <nav className="flex-1 p-4 space-y-2">
                    <Link href="/candidato/dashboard">
                        <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                            <LayoutDashboard className="mr-2 h-4 w-4 text-emerald-400" />
                            Panel Principal
                        </Button>
                    </Link>
                    <Link href="/candidato/perfil">
                        <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                            <User className="mr-2 h-4 w-4 text-cyan-400" />
                            Mi Perfil
                        </Button>
                    </Link>
                    <Link href="/candidato/postulaciones">
                        <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                            <FileText className="mr-2 h-4 w-4 text-purple-400" />
                            Mis Postulaciones
                        </Button>
                    </Link>
                    <div className="pt-4 mt-4 border-t border-white/10">
                        <Link href="/empleos">
                            <Button variant="secondary" className="w-full justify-start bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border-emerald-500/20 border">
                                <Briefcase className="mr-2 h-4 w-4" />
                                Buscar Empleos
                            </Button>
                        </Link>
                    </div>
                </nav>

                <div className="p-4 border-t border-white/10">
                    {/* Logout logic generally handled differently, for now just a link to api auth signout */}
                    <Link href="/api/auth/signout?callbackUrl=/login">
                        <Button variant="ghost" className="w-full justify-start text-red-400 hover:text-red-300 hover:bg-red-500/10">
                            <LogOut className="mr-2 h-4 w-4" />
                            Cerrar Sesión
                        </Button>
                    </Link>
                </div>
            </aside>

            {/* Main Content Area */}
            <main className="flex-1 p-4 md:p-8 overflow-y-auto">
                {children}
            </main>
        </div>
    );
}
