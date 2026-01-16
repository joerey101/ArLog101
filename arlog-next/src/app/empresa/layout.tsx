import Link from "next/link";
import { Briefcase, Building2, LayoutDashboard, LogOut, PlusCircle, Users } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function EmpresaLayout({
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
                        <span className="font-bold text-xl text-white tracking-tight">ArLog<span className="text-cyan-400">Biz</span></span>
                    </Link>
                </div>

                <nav className="flex-1 p-4 space-y-2">
                    <Link href="/empresa/dashboard">
                        <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                            <LayoutDashboard className="mr-2 h-4 w-4 text-cyan-400" />
                            Panel General
                        </Button>
                    </Link>

                    <div className="py-2">
                        <h4 className="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Reclutamiento</h4>
                        <Link href="/empresa/anuncios">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Briefcase className="mr-2 h-4 w-4 text-slate-400" />
                                Mis Avisos
                            </Button>
                        </Link>
                        <Link href="/empresa/anuncios/nuevo">
                            <Button variant="ghost" className="w-full justify-start text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 font-medium">
                                <PlusCircle className="mr-2 h-4 w-4" />
                                Publicar Nuevo
                            </Button>
                        </Link>
                    </div>

                    <div className="py-2">
                        <h4 className="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Configuración</h4>
                        <Link href="/empresa/perfil">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Building2 className="mr-2 h-4 w-4 text-slate-400" />
                                Perfil de Empresa
                            </Button>
                        </Link>
                    </div>
                </nav>

                <div className="p-4 border-t border-white/10">
                    <Link href="/api/auth/signout?callbackUrl=/">
                        <Button variant="ghost" className="w-full justify-start text-red-400 hover:text-red-300 hover:bg-red-500/10">
                            <LogOut className="mr-2 h-4 w-4" />
                            Cerrar Sesión
                        </Button>
                    </Link>
                </div>
            </aside>

            {/* Main Content Area */}
            <main className="flex-1 p-4 md:p-8 overflow-y-auto bg-gradient-to-br from-slate-950 to-slate-900">
                {children}
            </main>
        </div>
    );
}
