
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { EstadoAnuncio } from "@prisma/client";
import Link from "next/link";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { PlusCircle, Users, Briefcase, Eye } from "lucide-react";
import { redirect } from "next/navigation";

export default async function EmpresaDashboard() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    const userId = parseInt(session.user.id);

    // Data Fetching:
    // 1. Perfil (para el nombre)
    // 2. Conteo de Anuncios Activos
    // 3. Conteo total de postulaciones recibidas (join complejo)

    const perfil = await prisma.perfilEmpresa.findUnique({ where: { usuario_id: userId } });

    const anunciosActivos = await prisma.anuncio.count({
        where: { usuario_id: userId, estado: EstadoAnuncio.activo }
    });

    // Obtenemos los IDs de mis anuncios para contar postulaciones
    const misAnuncios = await prisma.anuncio.findMany({
        where: { usuario_id: userId },
        select: { id: true }
    });
    const misAnunciosIds = misAnuncios.map(a => a.id);

    const totalPostulaciones = await prisma.postulacion.count({
        where: { anuncio_id: { in: misAnunciosIds } }
    });

    const nombreEmpresa = perfil?.razon_social || 'Empresa';

    return (
        <div className="space-y-8 max-w-6xl mx-auto">
            {/* Header */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-white mb-2">Panel de Control</h1>
                    <p className="text-slate-400">Bienvenido, <span className="text-cyan-400 font-semibold">{nombreEmpresa}</span>.</p>
                </div>
                <Link href="/empresa/anuncios/nuevo">
                    <Button className="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold shadow-lg shadow-emerald-500/20">
                        <PlusCircle className="mr-2 h-4 w-4" /> Publicar Empleo
                    </Button>
                </Link>
            </div>

            {/* KPI Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card className="bg-slate-900 border-white/5 hover:border-cyan-500/30 transition-all">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium text-slate-400">Ofertas Activas</CardTitle>
                        <Briefcase className="h-4 w-4 text-cyan-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-white">{anunciosActivos}</div>
                        <p className="text-xs text-slate-500">Visibles ahora mismo</p>
                    </CardContent>
                </Card>

                <Card className="bg-slate-900 border-white/5 hover:border-purple-500/30 transition-all">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium text-slate-400">Total Candidatos</CardTitle>
                        <Users className="h-4 w-4 text-purple-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-white">{totalPostulaciones}</div>
                        <p className="text-xs text-slate-500">Postulaciones recibidas</p>
                    </CardContent>
                </Card>

                <Card className="bg-slate-900 border-white/5 hover:border-yellow-500/30 transition-all">
                    <CardHeader className="flex flex-row items-center justify-between pb-2">
                        <CardTitle className="text-sm font-medium text-slate-400">Vistas de Perfil</CardTitle>
                        <Eye className="h-4 w-4 text-yellow-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-white">--</div>
                        <p className="text-xs text-slate-500">Próximamente Analytics</p>
                    </CardContent>
                </Card>
            </div>

            {/* Recent Activity / Zero State */}
            <div className="grid md:grid-cols-2 gap-6">
                <Card className="bg-white/5 border-white/10 h-full">
                    <CardHeader>
                        <CardTitle className="text-white text-lg">Últimas Publicaciones</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {anunciosActivos === 0 ? (
                            <div className="text-center py-8 text-slate-500">
                                <p>No tienes ofertas activas.</p>
                                <Link href="/empresa/anuncios/nuevo" className="text-emerald-400 hover:underline text-sm mt-2 block">Crear primera oferta</Link>
                            </div>
                        ) : (
                            <div className="bg-slate-950/50 rounded-lg p-4 text-center text-slate-500 hover:text-white transition cursor-pointer">
                                <Link href="/empresa/anuncios">Ver listado completo</Link>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card className="bg-gradient-to-br from-indigo-900/10 to-purple-900/10 border-white/10 h-full">
                    <CardHeader>
                        <CardTitle className="text-white text-lg">Consejo ArLog</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-slate-400 text-sm leading-relaxed mb-4">
                            Los avisos con salario visible reciben hasta un <strong>40% más de postulaciones</strong> calificadas. Considera agregar un rango salarial competitivo.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
