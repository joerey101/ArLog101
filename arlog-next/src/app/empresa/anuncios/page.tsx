
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { EstadoAnuncio } from "@prisma/client";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { MoreHorizontal, PlusCircle, Users, Calendar } from "lucide-react";
import { redirect } from "next/navigation";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { JobActions } from "./job-actions";

export default async function MisAnunciosPage() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    const userId = parseInt(session.user.id);

    const anuncios = await prisma.anuncio.findMany({
        where: { usuario_id: userId },
        include: {
            _count: {
                select: { postulaciones: true }
            }
        },
        orderBy: { fecha_publicacion: 'desc' }
    });

    return (
        <div className="max-w-6xl mx-auto">
            <div className="flex justify-between items-center mb-6 pl-4 pr-4 md:pl-0 md:pr-0">
                <h1 className="text-2xl md:text-3xl font-bold text-white flex items-center gap-2">
                    Mis Ofertas
                    <span className="text-xs bg-emerald-500/10 text-emerald-500 px-2 py-1 rounded-full border border-emerald-500/20">v2.1</span>
                </h1>
                <Link href="/empresa/anuncios/nuevo">
                    <Button className="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold shadow-lg shadow-emerald-500/20">
                        <PlusCircle className="mr-2 h-4 w-4" /> Nuevo Anuncio
                    </Button>
                </Link>
            </div>

            <div className="bg-slate-900 rounded-xl border border-white/5 overflow-hidden">
                {/* Table Header */}
                <div className="grid grid-cols-12 gap-4 p-4 border-b border-white/5 bg-white/[0.02] text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:grid">
                    <div className="col-span-6">Título del Puesto</div>
                    <div className="col-span-2 text-center">Estado</div>
                    <div className="col-span-2 text-center">Candidatos</div>
                    <div className="col-span-2 text-right">Publicado</div>
                </div>

                {/* Table Body */}
                {anuncios.length === 0 ? (
                    <div className="p-12 text-center text-slate-500">
                        <p className="mb-4">No has publicado ningún empleo aún.</p>
                        <Button variant="outline" className="text-emerald-400 border-emerald-500/20">Crear el primero</Button>
                    </div>
                ) : (
                    <div className="divide-y divide-white/5">
                        {anuncios.map((anuncio) => (
                            <div key={anuncio.id} className="grid grid-cols-12 gap-4 p-4 items-center hover:bg-white/[0.02] transition">
                                <div className="col-span-12 md:col-span-6">
                                    <h3 className="text-white font-bold truncate text-lg mb-1">{anuncio.titulo}</h3>
                                    <p className="text-xs text-slate-500 truncate mb-3">{anuncio.ubicacion}</p>
                                    <div className="flex gap-2">
                                        <JobActions jobId={anuncio.id} />
                                    </div>
                                </div>
                                <div className="col-span-4 md:col-span-2 text-center flex items-center justify-center">
                                    <Badge variant="outline" className={
                                        anuncio.estado === EstadoAnuncio.ACTIVO ? 'border-emerald-500/50 text-emerald-400 bg-emerald-500/10' :
                                            anuncio.estado === EstadoAnuncio.PAUSADO ? 'border-yellow-500/50 text-yellow-500 bg-yellow-500/10' :
                                                'border-slate-500 text-slate-500'
                                    }>
                                        {anuncio.estado?.toUpperCase() || 'S/D'}
                                    </Badge>
                                </div>
                                <div className="col-span-4 md:col-span-2 text-center flex items-center justify-center gap-1 font-bold">
                                    <Link href={`/empresa/anuncios/${anuncio.id}/postulantes?from=/empresa/anuncios`} className="flex items-center gap-2 px-3 py-1 rounded-md hover:bg-white/10 text-white transition-colors group">
                                        <Users size={16} className="text-purple-400 group-hover:scale-110 transition-transform" />
                                        <span>{anuncio._count.postulaciones}</span>
                                    </Link>
                                </div>
                                <div className="col-span-4 md:col-span-2 text-right text-sm text-slate-500 flex items-center justify-end">
                                    {new Date(anuncio.fecha_publicacion).toLocaleDateString()}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
