import Link from "next/link";
import { LayoutDashboard, Users, ShieldAlert, LogOut, Briefcase, Tag } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function AdminLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    return (
        <div className="min-h-screen bg-slate-950 flex">
            {/* Sidebar Navigation */}
            <aside className="w-64 border-r border-white/10 bg-slate-900/90 hidden md:flex flex-col">
                <div className="p-6 border-b border-white/10">
                    <Link href="/" className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center font-bold text-white shadow-lg shadow-red-500/20">A</div>
                        <span className="font-bold text-xl text-white tracking-tight">ArLog<span className="text-red-500">Admin</span></span>
                    </Link>
                </div>

                <nav className="flex-1 p-4 space-y-2">
                    <Link href="/admin/dashboard">
                        <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5 data-[active=true]:bg-white/5">
                            <LayoutDashboard className="mr-2 h-4 w-4 text-red-500" />
                            Overview
                        </Button>
                    </Link>

                    <div className="py-2">
                        <h4 className="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Plataforma</h4>
                        <Link href="/admin/usuarios">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Users className="mr-2 h-4 w-4 text-slate-400" />
                                Usuarios
                            </Button>
                        </Link>
                        <Link href="/admin/anuncios">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Briefcase className="mr-2 h-4 w-4 text-slate-400" />
                                Anuncios Global
                            </Button>
                        </Link>
                        <Link href="/admin/etiquetas">
                            <Button variant="ghost" className="w-full justify-start text-slate-300 hover:text-white hover:bg-white/5">
                                <Tag className="mr-2 h-4 w-4 text-slate-400" />
                                Etiquetas / Skills
                            </Button>
                        </Link>
                    </div>
                </nav>

                <div className="p-4 border-t border-white/10">
                    <Link href="/api/auth/signout">
                        <Button variant="ghost" className="w-full justify-start text-red-400 hover:text-red-300 hover:bg-red-500/10">
                            <LogOut className="mr-2 h-4 w-4" />
                            Salir
                        </Button>
                    </Link>
                </div>
            </aside>

            {/* Main Content Area */}
            <main className="flex-1 p-4 md:p-8 overflow-y-auto bg-slate-950">
                <div className="max-w-7xl mx-auto">
                    {children}
                </div>
            </main>
        </div>
    );
}
