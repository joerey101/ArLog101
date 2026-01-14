
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import Link from "next/link";
import { notFound } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { MapPin, Briefcase, Clock, DollarSign, Calendar, CheckCircle2, FileText } from "lucide-react";
import { Rol, EstadoAnuncio } from "@prisma/client";
import { revalidatePath } from "next/cache";

async function applyAction(formData: FormData) {
    "use server";
    const session = await getServerSession(authOptions);
    if (!session || session.user.rol !== Rol.CANDIDATO) return;

    const anuncioId = parseInt(formData.get('anuncio_id') as string);
    const candidatoId = parseInt(session.user.id);

    try {
        await prisma.postulacion.create({
            data: {
                anuncio_id: anuncioId,
                candidato_id: candidatoId
            }
        });
        revalidatePath(`/empleos/${anuncioId}`);
    } catch (e) {
        // Ignorar duplicados
        console.error("Ya postulado o error", e);
    }
}

export default async function JobDetailPage({ params }: { params: { id: string } }) {
    const session = await getServerSession(authOptions);
    const anuncioId = parseInt(params.id);

    if (isNaN(anuncioId)) notFound();

    const anuncio = await prisma.anuncio.findUnique({
        where: { id: anuncioId },
        include: {
            empresa_perfil: true,
            postulaciones: {
                where: { candidato_id: session ? parseInt(session.user.id) : -1 },
                select: { id: true } // Check if applied
            }
        }
    });

    if (!anuncio || anuncio.estado !== EstadoAnuncio.ACTIVO) notFound();

    const yaPostulado = anuncio.postulaciones.length > 0;
    const isCandidato = session?.user.rol === Rol.CANDIDATO;

    return (
        <div className="container mx-auto px-6 py-12 max-w-4xl">
            <div className="mb-6">
                <Link href="/empleos" className="text-slate-400 hover:text-white text-sm mb-4 block">← Volver al listado</Link>

                <div className="flex flex-col md:flex-row gap-6 items-start justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-white mb-2">{anuncio.titulo}</h1>
                        <div className="text-xl text-emerald-400 mb-4">{anuncio.empresa_perfil?.razon_social || 'Empresa Confidencial'}</div>

                        <div className="flex flex-wrap gap-4 text-sm text-slate-300">
                            <Badge variant="secondary" className="bg-slate-800 text-slate-300 flex items-center gap-1">
                                <MapPin size={14} /> {anuncio.ubicacion}
                            </Badge>
                            <Badge variant="secondary" className="bg-slate-800 text-slate-300 flex items-center gap-1">
                                <Briefcase size={14} /> {anuncio.modalidad}
                            </Badge>
                            <Badge variant="secondary" className="bg-slate-800 text-slate-300 flex items-center gap-1">
                                <Clock size={14} /> {anuncio.tipo_contrato || 'Full-time'}
                            </Badge>
                            {anuncio.rango_salarial && (
                                <Badge variant="secondary" className="bg-slate-800 text-emerald-300 border-emerald-500/20 flex items-center gap-1">
                                    <DollarSign size={14} /> {anuncio.rango_salarial}
                                </Badge>
                            )}
                        </div>
                    </div>

                    {/* Action Box */}
                    <Card className="bg-slate-900 border-white/10 w-full md:w-72 shrink-0">
                        <CardContent className="p-6">
                            {!session ? (
                                <div className="text-center">
                                    <p className="text-slate-400 text-sm mb-4">Inicia sesión para postularte.</p>
                                    <Link href={`/login?callbackUrl=/empleos/${anuncio.id}`}>
                                        <Button className="w-full bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400">Ingresar</Button>
                                    </Link>
                                </div>
                            ) : !isCandidato ? (
                                <div className="text-center text-slate-500 text-sm bg-white/5 p-3 rounded">
                                    Solo candidatos pueden postular.
                                </div>
                            ) : yaPostulado ? (
                                <div className="text-center">
                                    <div className="bg-emerald-500/10 text-emerald-400 p-3 rounded mb-2 flex items-center justify-center gap-2 font-bold">
                                        <CheckCircle2 /> Postulado
                                    </div>
                                    <p className="text-xs text-slate-500">Tu CV ya fue enviado.</p>
                                </div>
                            ) : (
                                <form action={applyAction}>
                                    <input type="hidden" name="anuncio_id" value={anuncio.id} />
                                    <Button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold shadow-lg shadow-emerald-500/20">
                                        Postularme Ahora
                                    </Button>
                                    <p className="text-xs text-slate-500 mt-2 text-center">Se enviará tu perfil actual.</p>
                                </form>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>

            <div className="grid md:grid-cols-3 gap-8">
                <div className="md:col-span-2 space-y-8">
                    <section>
                        <h3 className="text-xl font-bold text-white mb-4 border-b border-white/5 pb-2">Descripción del Puesto</h3>
                        <div className="prose prose-invert max-w-none text-slate-300 whitespace-pre-line">
                            {anuncio.descripcion}
                        </div>
                    </section>
                </div>

                <div className="space-y-6">
                    <Card className="bg-white/5 border-white/10">
                        <CardHeader>
                            <CardTitle className="text-white text-base">Sobre la Empresa</CardTitle>
                        </CardHeader>
                        <CardContent className="text-sm text-slate-400">
                            {anuncio.empresa_perfil?.descripcion || "Esta empresa no ha agregado una descripción pública."}
                            {anuncio.empresa_perfil?.sitio_web && (
                                <a href={anuncio.empresa_perfil.sitio_web} target="_blank" rel="noopener noreferrer" className="block mt-4 text-emerald-400 hover:underline truncate">
                                    Visitar Sitio Web
                                </a>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
