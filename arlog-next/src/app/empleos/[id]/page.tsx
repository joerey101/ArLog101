
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import Link from "next/link";
import { notFound } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { MapPin, Briefcase, Clock, DollarSign, CheckCircle2, FileText } from "lucide-react";
import { Rol, EstadoAnuncio } from "@prisma/client";
import { revalidatePath } from "next/cache";

// Force dynamic rendering to ensure fresh data and avoid 404 caching issues
export const dynamic = 'force-dynamic';
export const revalidate = 0;

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

export default async function JobDetailPage({ params }: { params: Promise<{ id: string }> }) {
    const session = await getServerSession(authOptions);
    // Next.js 15+ requires awaiting params
    const { id } = await params;
    const anuncioId = parseInt(id);

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
        <div className="min-h-screen bg-slate-950 py-12">
            <div className="container mx-auto px-6 max-w-4xl">
                <div className="mb-6">
                    <Link href="/empleos" className="text-emerald-400 hover:text-emerald-300 text-sm mb-4 inline-flex items-center gap-1 font-medium bg-slate-900/50 px-3 py-1.5 rounded-lg border border-white/5 hover:border-emerald-500/30 transition-all">
                        ← Volver al listado
                    </Link>

                    <div className="flex flex-col md:flex-row gap-6 items-start justify-between mt-6">
                        <div className="flex-1">
                            <h1 className="text-3xl md:text-4xl font-bold text-white mb-2">{anuncio.titulo}</h1>
                            <div className="text-xl text-emerald-400 mb-6 font-medium">{anuncio.empresa_perfil?.razon_social || 'Empresa Confidencial'}</div>

                            <div className="flex flex-wrap gap-3 text-sm text-slate-300">
                                <Badge variant="secondary" className="bg-slate-900 border border-white/10 text-slate-300 px-3 py-1.5 flex items-center gap-2 text-base font-normal">
                                    <MapPin size={16} className="text-slate-500" /> {anuncio.ubicacion}
                                </Badge>
                                <Badge variant="secondary" className="bg-slate-900 border border-white/10 text-slate-300 px-3 py-1.5 flex items-center gap-2 text-base font-normal">
                                    <Briefcase size={16} className="text-slate-500" /> {anuncio.modalidad}
                                </Badge>
                                <Badge variant="secondary" className="bg-slate-900 border border-white/10 text-slate-300 px-3 py-1.5 flex items-center gap-2 text-base font-normal">
                                    <Clock size={16} className="text-slate-500" /> {anuncio.tipo_contrato || 'Full-time'}
                                </Badge>
                                {anuncio.rango_salarial && (
                                    <Badge variant="secondary" className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 px-3 py-1.5 flex items-center gap-2 text-base font-medium">
                                        <DollarSign size={16} /> {anuncio.rango_salarial}
                                    </Badge>
                                )}
                            </div>
                        </div>

                        {/* Action Box */}
                        <Card className="bg-slate-900 border-white/10 w-full md:w-80 shrink-0 shadow-2xl shadow-black/50">
                            <CardContent className="p-6">
                                {!session ? (
                                    <div className="text-center">
                                        <p className="text-white text-sm font-medium mb-4">Inicia sesión para postularte.</p>
                                        <Link href={`/login?callbackUrl=/empleos/${anuncio.id}`}>
                                            <Button className="w-full h-12 text-base bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400">
                                                Ingresar
                                            </Button>
                                        </Link>
                                    </div>
                                ) : !isCandidato ? (
                                    <div className="text-center text-slate-400 text-sm bg-white/5 p-4 rounded-lg border border-white/5">
                                        Solo cuentas de tipo <span className="text-white font-bold">Candidato</span> pueden postular.
                                    </div>
                                ) : yaPostulado ? (
                                    <div className="text-center">
                                        <div className="bg-emerald-500/20 border border-emerald-500/20 text-emerald-400 p-4 rounded-lg mb-3 flex flex-col items-center justify-center gap-2 font-bold text-lg animate-in fade-in zoom-in duration-300">
                                            <CheckCircle2 size={32} />
                                            ¡CV Enviado!
                                        </div>
                                        <p className="text-sm text-slate-400">Ya te postulaste a esta vacante.</p>
                                    </div>
                                ) : (
                                    <form action={applyAction}>
                                        <input type="hidden" name="anuncio_id" value={anuncio.id} />
                                        <Button type="submit" className="w-full h-12 text-base bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_30px_rgba(16,185,129,0.5)] transition-all transform hover:-translate-y-1">
                                            Postularme Ahora
                                        </Button>
                                        <p className="text-xs text-slate-500 mt-3 text-center">Se enviará tu perfil profesional actual.</p>
                                    </form>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div className="grid md:grid-cols-3 gap-8 mt-12">
                    <div className="md:col-span-2 space-y-8">
                        <section className="bg-slate-900/50 p-8 rounded-2xl border border-white/5">
                            <h3 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                                <FileText size={20} className="text-emerald-400" />
                                Descripción del Puesto
                            </h3>
                            <div className="prose prose-invert max-w-none text-slate-300 whitespace-pre-line leading-relaxed text-base">
                                {anuncio.descripcion}
                            </div>
                        </section>
                    </div>

                    <div className="space-y-6">
                        <Card className="bg-slate-900/50 border-white/5 p-6">
                            <CardHeader className="p-0 mb-4">
                                <CardTitle className="text-white text-lg font-bold flex items-center gap-2">
                                    <Briefcase size={18} className="text-cyan-400" />
                                    Sobre la Empresa
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="p-0 text-slate-400 leading-relaxed text-sm">
                                {anuncio.empresa_perfil?.descripcion || "Esta empresa no ha agregado una descripción pública."}
                                {anuncio.empresa_perfil?.sitio_web && (
                                    <a href={anuncio.empresa_perfil.sitio_web} target="_blank" rel="noopener noreferrer" className="block mt-6 text-center w-full py-2 rounded-lg bg-white/5 hover:bg-white/10 text-emerald-400 hover:text-emerald-300 text-sm font-medium transition-colors border border-white/5">
                                        Visitar Sitio Web
                                    </a>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    );
}
