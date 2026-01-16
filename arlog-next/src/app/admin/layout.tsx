
import Link from "next/link";
import { LayoutDashboard, Users, Briefcase, Settings, LogOut, ShieldAlert } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function AdminLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <div className="min-h-screen bg-slate-950 flex">
            {/* Sidebar Navigation - Violet Theme for Admin */}
            <aside className="w-64 border-r border-white/10 bg-slate-900/50 hidden md:flex flex-col">
                <div className="p-6 border-b border-white/10">
                    <Link href="/" className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-600 to-fuchsia-600 flex items-center justify-center font-bold text-white">A</div>
                        <span className="font-bold text-xl text-white tracking-tight">ArLog<span className="text-violet-400">Admin</span></span>
                    </Link>
                </div>

                <nav className="flex-1 p-4 space-y-2">
                    <Link href="/admin/dashboard">
                        <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                            <LayoutDashboard className="mr-2 h-4 w-4 text-violet-400" />
                            Dashboard
                        </Button>
                    </Link>

                    <div className="py-2">
                        <h4 className="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Comunidad</h4>
                        <Link href="/admin/candidatos">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Users className="mr-2 h-4 w-4 text-slate-400" />
                                Candidatos
                            </Button>
                        </Link>
                        <Link href="/admin/empresas">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Briefcase className="mr-2 h-4 w-4 text-slate-400" />
                                Empresas
                            </Button>
                        </Link>
                    </div>

                    <div className="py-2">
                        <h4 className="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Bolsa de Trabajo</h4>
                        <Link href="/admin/anuncios">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Briefcase className="mr-2 h-4 w-4 text-slate-400" />
                                Anuncios
                            </Button>
                        </Link>
                    </div>

                    <div className="py-2">
                        <h4 className="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Sistema</h4>
                        <Link href="/admin/etiquetas">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Settings className="mr-2 h-4 w-4 text-slate-400" />
                                Etiquetas (Tags)
                            </Button>
                        </Link>
                    </div>
                </nav>

                <div className="p-4 border-t border-white/10">
                    <div className="bg-violet-500/10 border border-violet-500/20 rounded-lg p-3 mb-4">
                        <div className="flex items-center gap-2 text-violet-300 text-xs font-bold mb-1">
                            <ShieldAlert size={14} />
                            MODO ADMIN
                        </div>
                        <p className="text-[10px] text-slate-400">Acceso total al sistema.</p>
                    </div>

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
