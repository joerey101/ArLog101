
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Card, CardContent } from "@/components/ui/card";
import { MapPin, Building, Info, FileText } from "lucide-react";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { ApplicationTimeline } from "./timeline";

export default async function MisPostulacionesPage() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    const postulaciones = await prisma.postulacion.findMany({
        where: { candidato_id: parseInt(session.user.id) },
        include: {
            anuncio: {
                include: {
                    empresa_perfil: true,
                    usuario: true
                }
            }
        },
        orderBy: { fecha_postulacion: 'desc' }
    });

    return (
        <div className="max-w-4xl mx-auto">
            <div className="flex justify-between items-center mb-8">
                <div>
                    <h1 className="text-3xl font-bold text-white">Mis Postulaciones</h1>
                    <p className="text-slate-400">Sigue el estado de tus candidaturas en tiempo real.</p>
                </div>
                <Link href="/empleos">
                    <Button variant="outline" className="border-emerald-500/50 text-emerald-400 hover:bg-emerald-500/10">Buscar más empleos</Button>
                </Link>
            </div>

            {postulaciones.length === 0 ? (
                <Card className="bg-white/5 border-white/10 text-center py-12">
                    <div className="flex flex-col items-center">
                        <Info className="w-12 h-12 text-slate-500 mb-4" />
                        <h3 className="text-xl text-white font-bold mb-2">Aún no te has postulado</h3>
                        <p className="text-slate-400 mb-6">Explora las ofertas disponibles y da el siguiente paso en tu carrera.</p>
                        <Link href="/empleos">
                            <Button className="bg-emerald-500 hover:bg-emerald-400 text-slate-950">Ver Ofertas</Button>
                        </Link>
                    </div>
                </Card>
            ) : (
                <div className="grid gap-6">
                    {postulaciones.map((p) => (
                        <Card key={p.id} className="bg-slate-900 border-white/5 overflow-hidden">
                            <CardContent className="p-0">
                                {/* Header del Empleo */}
                                <div className="p-6 border-b border-white/5 flex flex-col md:flex-row justify-between gap-4">
                                    <div className="space-y-1">
                                        <Link href={`/empleos/${p.anuncio_id}`} className="hover:underline">
                                            <h3 className="text-xl font-bold text-white hover:text-emerald-400 transition-colors">{p.anuncio.titulo}</h3>
                                        </Link>
                                        <div className="flex flex-wrap gap-4 text-sm text-slate-400">
                                            <span className="flex items-center gap-1">
                                                <Building size={14} className="text-cyan-400" />
                                                {p.anuncio.empresa_perfil?.razon_social || 'Empresa Confidencial'}
                                            </span>
                                            {p.anuncio.ubicacion && (
                                                <span className="flex items-center gap-1">
                                                    <MapPin size={14} className="text-emerald-400" /> {p.anuncio.ubicacion}
                                                </span>
                                            )}
                                            <span className="text-slate-600">
                                                • Enviada el {new Date(p.fecha_postulacion).toLocaleDateString()}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <Link href={`/empleos/${p.anuncio_id}`}>
                                            <Button variant="ghost" size="sm" className="text-slate-400 hover:text-white">
                                                <FileText className="w-4 h-4 mr-2" />
                                                Ver Aviso
                                            </Button>
                                        </Link>
                                    </div>
                                </div>

                                {/* Area de Status Timeline */}
                                <div className="px-6 py-6 bg-slate-950/30">
                                    <ApplicationTimeline status={p.estado} />
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            )}
        </div>
    );
}
