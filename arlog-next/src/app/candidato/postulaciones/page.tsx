
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Calendar, MapPin, Building, Info } from "lucide-react";
import Link from "next/link";
import { Button } from "@/components/ui/button";

// Helper para colores de estado
const getStatusBadge = (status: string) => {
    switch (status) {
        case 'nuevo': return <Badge variant="secondary">Enviada</Badge>;
        case 'visto': return <Badge className="bg-blue-500/20 text-blue-400 hover:bg-blue-500/30">Visto</Badge>;
        case 'entrevista': return <Badge className="bg-purple-500/20 text-purple-400 hover:bg-purple-500/30">Entrevista</Badge>;
        case 'finalista': return <Badge className="bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30">Finalista</Badge>;
        case 'descartado': return <Badge className="bg-red-500/10 text-red-400 hover:bg-red-500/20">Descartado</Badge>;
        case 'contratado': return <Badge className="bg-emerald-500 text-black font-bold">¡Contratado!</Badge>;
        default: return <Badge variant="outline">{status}</Badge>;
    }
};

export default async function MisPostulacionesPage() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    const postulaciones = await prisma.postulacion.findMany({
        where: { usuario_id: parseInt(session.user.id) },
        include: {
            anuncio: {
                include: {
                    // Si tuviéramos relacion Anuncio -> Empresa la traeríamos, 
                    // pero en v1.9 el link es Anuncio -> Usuario (que es empresa).
                    // Traemos el usuario publicador para sacar el nombre de empresa.
                    usuario: {
                        include: {
                            perfilEmpresa: true
                        }
                    }
                }
            }
        },
        orderBy: { fecha_postulacion: 'desc' }
    });

    return (
        <div className="max-w-5xl mx-auto">
            <div className="flex justify-between items-center mb-8">
                <h1 className="text-3xl font-bold text-white">Mis Postulaciones</h1>
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
                <div className="grid gap-4">
                    {postulaciones.map((p) => (
                        <Card key={p.id} className="bg-slate-900/50 border-white/5 hover:border-white/10 transition-all">
                            <CardContent className="p-6">
                                <div className="flex flex-col md:flex-row justify-between md:items-center gap-4">

                                    {/* Info del Empleo */}
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-2 mb-2">
                                            {getStatusBadge(p.estado)}
                                            <span className="text-xs text-slate-500">{new Date(p.fecha_postulacion).toLocaleDateString()}</span>
                                        </div>
                                        <h3 className="text-xl font-bold text-white">{p.anuncio.titulo}</h3>
                                        <div className="flex flex-wrap gap-4 text-sm text-slate-400">
                                            <span className="flex items-center gap-1">
                                                <Building size={14} className="text-cyan-400" />
                                                {p.anuncio.usuario.perfilEmpresa?.razon_social || 'Empresa Confidencial'}
                                            </span>
                                            {p.anuncio.ubicacion && (
                                                <span className="flex items-center gap-1">
                                                    <MapPin size={14} className="text-emerald-400" /> {p.anuncio.ubicacion}
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Acciones (Futuro: Chat, Ver Detalle) */}
                                    <div>
                                        <Button variant="ghost" size="sm" className="text-slate-400 hover:text-white">Ver aviso original</Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            )}
        </div>
    );
}
