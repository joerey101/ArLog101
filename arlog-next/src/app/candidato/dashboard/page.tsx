
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import Link from "next/link";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { ArrowRight, FileText, Search, UserCheck } from "lucide-react";

export default async function CandidatoDashboard() {
    const session = await getServerSession(authOptions);
    // El control de sesión ya lo hace el layout o el middleware, pero por seguridad:
    if (!session) return null;

    const userId = parseInt(session.user.id);

    // Fetch Data en Paralelo
    const [perfil, postulacionesCount] = await Promise.all([
        prisma.perfilCandidato.findUnique({ where: { usuario_id: userId } }),
        prisma.postulacion.count({ where: { candidato_id: userId } })
    ]);

    const tieneCV = !!perfil?.cv_url;
    const nombre = perfil?.nombre || session.user.name || 'Candidato';

    return (
        <div className="space-y-8 max-w-5xl mx-auto">
            {/* Welcome Hero */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-white mb-2">Hola, <span className="text-emerald-400">{nombre.split(' ')[0]}</span> 👋</h1>
                    <p className="text-slate-400">Aquí tienes un resumen de tu actividad en ArLog.</p>
                </div>
                {!tieneCV && (
                    <div className="px-4 py-2 bg-amber-500/10 border border-amber-500/20 rounded-lg text-amber-200 text-sm flex items-center gap-2 animate-pulse">
                        ⚠️ Aún no has cargado tu CV
                        <Link href="/candidato/perfil" className="underline font-bold hover:text-white">Solucionar</Link>
                    </div>
                )}
            </div>

            {/* Quick Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card className="bg-slate-900 border-white/5 hover:border-emerald-500/30 transition-all group">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium text-slate-400">Postulaciones</CardTitle>
                        <FileText className="h-4 w-4 text-emerald-500 group-hover:scale-110 transition-transform" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-white">{postulacionesCount}</div>
                        <p className="text-xs text-slate-500">Candidaturas enviadas</p>
                    </CardContent>
                </Card>

                <Card className="bg-slate-900 border-white/5 hover:border-cyan-500/30 transition-all group">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium text-slate-400">Estado del Perfil</CardTitle>
                        <UserCheck className="h-4 w-4 text-cyan-500 group-hover:scale-110 transition-transform" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-white">{tieneCV ? 'Completo' : 'Incompleto'}</div>
                        <p className="text-xs text-slate-500">{tieneCV ? 'Listo para aplicar' : 'Falta información clave'}</p>
                    </CardContent>
                </Card>

                <Card className="bg-gradient-to-br from-emerald-900/20 to-cyan-900/20 border-white/10 hover:border-emerald-500/50 transition-all cursor-pointer group">
                    <Link href="/empleos" className="h-full block">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-emerald-200">Buscar Empleo</CardTitle>
                            <Search className="h-4 w-4 text-emerald-400 group-hover:scale-110 transition-transform" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-lg font-bold text-white group-hover:translate-x-1 transition-transform flex items-center gap-2">
                                Ver Ofertas <ArrowRight size={16} />
                            </div>
                            <p className="text-xs text-slate-400">Hay vacantes nuevas hoy</p>
                        </CardContent>
                    </Link>
                </Card>
            </div>

            {/* Recent Activity or Upsell */}
            <div className="p-6 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h3 className="text-xl font-bold text-white mb-2">¿Buscas trabajo de Clarkista?</h3>
                    <p className="text-slate-400 text-sm">Las empresas de Zona Norte están buscando perfiles con certificación de manejo vigente.</p>
                </div>
                <Link href="/empleos?q=clarkista">
                    <Button className="bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold">Ver vacantes de Clarkista</Button>
                </Link>
            </div>
        </div>
    );
}
