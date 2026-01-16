
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import Link from "next/link";
import { redirect } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, Briefcase, Building2, UserCheck } from "lucide-react";
import { Rol } from "@prisma/client";

export const dynamic = 'force-dynamic';

export default async function AdminDashboardPage() {
    const session = await getServerSession(authOptions);

    // Strict Admin Check
    if (!session) {
        redirect('/login?callbackUrl=/admin/dashboard');
    }

    if (session.user.rol !== 'ADMIN') {
        redirect('/');
    }

    // Parallel Data Fetching for Stats
    const [totalUsuarios, totalCandidatos, totalEmpresas, totalAnuncios] = await Promise.all([
        prisma.usuario.count(),
        prisma.usuario.count({ where: { rol: Rol.CANDIDATO } }),
        prisma.usuario.count({ where: { rol: Rol.EMPRESA } }),
        prisma.anuncio.count(),
    ]);

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Panel de Administración</h1>
                <p className="text-slate-400">Resumen general del estado de la plataforma.</p>
            </div>

            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Link href="/admin/usuarios">
                    <Card className="bg-slate-900/50 border-white/10 hover:border-violet-500/50 transition-all cursor-pointer h-full">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Total Usuarios</CardTitle>
                            <Users className="h-4 w-4 text-violet-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-white">{totalUsuarios}</div>
                            <p className="text-xs text-slate-500">Registrados en el sistema</p>
                        </CardContent>
                    </Card>
                </Link>
                <Link href="/admin/candidatos">
                    <Card className="bg-slate-900/50 border-white/10 hover:border-emerald-500/50 transition-all cursor-pointer h-full">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Candidatos</CardTitle>
                            <UserCheck className="h-4 w-4 text-emerald-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-white">{totalCandidatos}</div>
                            <p className="text-xs text-slate-500">Buscan empleo</p>
                        </CardContent>
                    </Card>
                </Link>
                <Link href="/admin/empresas">
                    <Card className="bg-slate-900/50 border-white/10 hover:border-cyan-500/50 transition-all cursor-pointer h-full">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Empresas</CardTitle>
                            <Building2 className="h-4 w-4 text-cyan-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-white">{totalEmpresas}</div>
                            <p className="text-xs text-slate-500">Publican ofertas</p>
                        </CardContent>
                    </Card>
                </Link>
                <Link href="/admin/anuncios">
                    <Card className="bg-slate-900/50 border-white/10 hover:border-yellow-500/50 transition-all cursor-pointer h-full">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Anuncios</CardTitle>
                            <Briefcase className="h-4 w-4 text-yellow-400" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-white">{totalAnuncios}</div>
                            <p className="text-xs text-slate-500">Ofertas totales</p>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            {/* TODO: Add Charts or Recent Activity Table here */}
            <div className="bg-slate-900/30 border border-white/5 rounded-xl p-8 text-center">
                <p className="text-slate-500 italic">Más métricas y gráficos próximamente en v2.1</p>
            </div>
        </div>
    );
}
