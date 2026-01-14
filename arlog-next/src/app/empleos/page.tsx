
import prisma from "@/lib/prisma";
import Link from "next/link";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { MapPin, Briefcase, Clock, Calendar } from "lucide-react";
import { EstadoAnuncio } from "@prisma/client";

import { getServerSession } from "next-auth";
import { authOptions } from "../api/auth/[...nextauth]/route";

export const dynamic = 'force-dynamic';

export default async function EmpleosPage({
    searchParams,
}: {
    searchParams?: { q?: string; loc?: string };
}) {
    const session = await getServerSession(authOptions);
    const { q, loc } = searchParams || {};

    // Determine back link destination
    const backLink = session ? '/dashboard' : '/';

    const anuncios = await prisma.anuncio.findMany({
        where: {
            estado: EstadoAnuncio.ACTIVO,
            AND: [
                q ? { titulo: { contains: q, mode: 'insensitive' } } : {},
                loc ? { ubicacion: { contains: loc, mode: 'insensitive' } } : {},
            ],
        },
        include: {
            empresa_perfil: {
                select: {
                    razon_social: true,
                    logo_url: true,
                },
            },
        },
        orderBy: { fecha_publicacion: 'desc' },
    });

    return (
        <div className="min-h-screen bg-slate-950">
            {/* Header */}
            <header className="bg-slate-900 border-b border-white/5 py-8">
                <div className="container mx-auto px-6">
                    <Link href={backLink} className="mb-4 inline-block text-sm text-emerald-400 hover:text-emerald-300 font-medium">
                        ← Volver al Inicio
                    </Link>
                    <h1 className="text-3xl md:text-4xl font-bold text-white mb-2">Bolsa de Trabajo</h1>
                    <p className="text-slate-400 max-w-2xl">
                        Explora las mejores oportunidades en el sector logístico y operativo.
                        {q && <span className="block mt-2 text-emerald-400">Resultados para: "{q}"</span>}
                    </p>
                </div>
            </header>

            <div className="container mx-auto px-6 py-12 max-w-5xl">
                <div className="grid gap-4">
                    {anuncios.length === 0 ? (
                        <div className="text-center py-20 bg-white/[0.02] border border-white/5 rounded-xl">
                            <Briefcase className="mx-auto h-12 w-12 text-slate-600 mb-4" />
                            <h3 className="text-lg font-medium text-white mb-2">No se encontraron ofertas</h3>
                            <p className="text-slate-500 mb-6">Intenta con otros términos de búsqueda.</p>
                            <Link href="/empleos">
                                <Button variant="outline" className="border-emerald-500/20 text-emerald-400">Ver todas las ofertas</Button>
                            </Link>
                        </div>
                    ) : (
                        anuncios.map((job) => (
                            <Link key={job.id} href={`/empleos/${job.id}`} className="block group">
                                <Card className="bg-slate-900/50 border-white/5 hover:border-emerald-500/50 hover:bg-slate-900 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300">
                                    <CardContent className="p-6">
                                        <div className="flex flex-col md:flex-row gap-6 items-start">
                                            {/* Logo */}
                                            <div className="w-14 h-14 rounded-lg bg-white/5 flex items-center justify-center text-xl font-bold text-slate-500 uppercase shrink-0 border border-white/5 group-hover:border-emerald-500/20 group-hover:text-emerald-500 transition-colors">
                                                {job.empresa_perfil?.razon_social?.[0] || <Briefcase size={20} />}
                                            </div>

                                            <div className="flex-1 w-full">
                                                <div className="flex flex-col md:flex-row md:justify-between md:items-start gap-2 mb-2">
                                                    <div>
                                                        <h2 className="text-xl font-bold text-white group-hover:text-emerald-400 transition-colors">
                                                            {job.titulo}
                                                        </h2>
                                                        <p className="text-slate-400 text-sm font-medium">{job.empresa_perfil?.razon_social || 'Empresa Confidencial'}</p>
                                                    </div>
                                                    <Badge variant="outline" className="w-fit border-emerald-500/20 text-emerald-400 bg-emerald-500/5 hidden md:flex">
                                                        Nueva
                                                    </Badge>
                                                </div>

                                                <div className="flex flex-wrap gap-4 text-sm text-slate-500 mt-4 md:mt-2">
                                                    <span className="flex items-center gap-1.5"><MapPin size={14} className="text-slate-400" /> {job.ubicacion || 'Remoto'}</span>
                                                    <span className="flex items-center gap-1.5"><Clock size={14} className="text-slate-400" /> {job.modalidad}</span>
                                                    <span className="flex items-center gap-1.5"><Calendar size={14} className="text-slate-400" /> {new Date(job.fecha_publicacion).toLocaleDateString()}</span>
                                                </div>
                                            </div>

                                            <div className="w-full md:w-auto mt-4 md:mt-0 pt-4 md:pt-0 border-t border-white/5 md:border-t-0 flex flex-row md:flex-col justify-between items-center gap-3">
                                                <div className="md:hidden">
                                                    <Badge variant="outline" className="border-emerald-500/20 text-emerald-400 bg-emerald-500/5">
                                                        Nueva
                                                    </Badge>
                                                </div>
                                                <Button className="bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 border border-emerald-500/20 w-fit whitespace-nowrap font-bold transition-all">
                                                    Ver Detalle
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
}
