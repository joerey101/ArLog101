
import prisma from "@/lib/prisma";
import Link from "next/link";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { MapPin, Briefcase, Clock, Calendar } from "lucide-react";
import { EstadoAnuncio } from "@prisma/client";

export default async function EmpleosPage({
    searchParams,
}: {
    searchParams?: { q?: string; loc?: string };
}) {
    const { q, loc } = searchParams || {};

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
        <div className="container mx-auto px-6 py-12">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">Bolsa de Trabajo</h1>
                <p className="text-slate-400">Encuentra tu próximo desafío en logística.</p>
            </div>

            <div className="grid gap-4">
                {anuncios.length === 0 ? (
                    <div className="text-center py-12 text-slate-500">
                        No se encontraron ofertas con esos criterios.
                    </div>
                ) : (
                    anuncios.map((job) => (
                        <Link key={job.id} href={`/empleos/${job.id}`}>
                            <Card className="bg-slate-900 border-white/5 hover:border-emerald-500/30 transition-all group">
                                <CardContent className="p-6 flex flex-col md:flex-row gap-6 items-start md:items-center">
                                    {/* Logo Placeholder */}
                                    <div className="w-16 h-16 rounded-lg bg-white/5 flex items-center justify-center text-2xl font-bold text-slate-500 uppercase shrink-0">
                                        {job.empresa_perfil?.razon_social?.[0] || <Briefcase size={24} />}
                                    </div>

                                    <div className="flex-1">
                                        <h2 className="text-xl font-bold text-white group-hover:text-emerald-400 transition-colors">
                                            {job.titulo}
                                        </h2>
                                        <p className="text-slate-400 mb-2">{job.empresa_perfil?.razon_social || 'Empresa Confidencial'}</p>

                                        <div className="flex flex-wrap gap-3 text-sm text-slate-500">
                                            <span className="flex items-center gap-1"><MapPin size={14} /> {job.ubicacion || 'Remoto'}</span>
                                            <span className="flex items-center gap-1"><Clock size={14} /> {job.modalidad}</span>
                                            <span className="flex items-center gap-1"><Calendar size={14} /> {new Date(job.fecha_publicacion).toLocaleDateString()}</span>
                                        </div>
                                    </div>

                                    <div className="hidden md:block">
                                        <Button variant="outline" className="border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/10">Ver Detalle</Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    ))
                )}
            </div>
        </div>
    );
}
