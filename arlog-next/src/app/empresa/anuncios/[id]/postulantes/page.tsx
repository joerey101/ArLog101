
import { getServerSession } from "next-auth";
import { authOptions } from "../../../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Phone, Mail, MapPin, Linkedin, FileText, ArrowLeft, MessageSquare, Download } from "lucide-react";
import { Rol } from "@prisma/client";

export default async function CompanyApplicantsPage({ params }: { params: { id: string } }) {
    const session = await getServerSession(authOptions);
    if (!session || session.user.rol !== Rol.EMPRESA) redirect('/login');

    const anuncioId = parseInt(params.id);
    const userId = parseInt(session.user.id);

    // Validate ownership
    const anuncio = await prisma.anuncio.findUnique({
        where: { id: anuncioId },
        select: { usuario_id: true, titulo: true }
    });

    if (!anuncio || anuncio.usuario_id !== userId) notFound();

    // Fetch Candidates
    const postulaciones = await prisma.postulacion.findMany({
        where: { anuncio_id: anuncioId },
        include: {
            candidato: {
                include: {
                    perfilCandidato: true
                }
            }
        },
        orderBy: { fecha_postulacion: 'desc' }
    });

    return (
        <div className="max-w-6xl mx-auto">
            <div className="mb-6 flex items-center gap-4">
                <Link href="/empresa/anuncios">
                    <Button variant="ghost" size="icon" className="text-slate-400 hover:text-white">
                        <ArrowLeft />
                    </Button>
                </Link>
                <div>
                    <h1 className="text-2xl font-bold text-white">Postulantes: {anuncio.titulo}</h1>
                    <p className="text-slate-400">{postulaciones.length} candidatos aplicaron a esta vacante.</p>
                </div>
            </div>

            {postulaciones.length === 0 ? (
                <div className="text-center py-20 bg-slate-900 rounded-xl border border-white/5">
                    <div className="text-slate-500 mb-2">Aún no hay postulantes.</div>
                    <p className="text-sm text-slate-600">Considera compartir el enlace de tu anuncio en redes sociales.</p>
                </div>
            ) : (
                <div className="grid gap-4">
                    {postulaciones.map((post) => {
                        const perfil = post.candidato.perfilCandidato;
                        const nombre = perfil?.nombre ? `${perfil.nombre} ${perfil.apellido}` : post.candidato.email;
                        const initials = (nombre[0] || '?').toUpperCase();

                        // Construct direct contact links
                        const whatsappLink = perfil?.telefono
                            ? `https://wa.me/${perfil.telefono.replace(/[^0-9]/g, '')}?text=Hola ${perfil.nombre}, te contactamos por tu postulación a ${anuncio.titulo} en ArLog.`
                            : null;

                        const mailtoLink = `mailto:${post.candidato.email}?subject=Entrevista para ${anuncio.titulo}&body=Hola ${perfil?.nombre || ''}, vimos tu perfil en ArLog...`;

                        return (
                            <Card key={post.id} className="bg-slate-900 border-white/5 hover:border-white/10 transition-all">
                                <CardContent className="p-6 flex flex-col md:flex-row gap-6 items-start md:items-center">
                                    <Avatar className="w-16 h-16 border-2 border-slate-700">
                                        <AvatarImage src={perfil?.cv_url || undefined} /> {/* Fallback if no photo field yet, maybe reuse CV URL if it was an image? No, wait, Profile Photo field missing. Using initials for now */}
                                        <AvatarFallback className="bg-slate-800 text-slate-300 font-bold text-xl">{initials}</AvatarFallback>
                                    </Avatar>

                                    <div className="flex-1 space-y-2">
                                        <div className="flex flex-col md:flex-row md:items-center gap-2">
                                            <h3 className="text-lg font-bold text-white">{nombre}</h3>
                                            <Badge variant="outline" className="w-fit text-slate-400 border-slate-700">
                                                {new Date(post.fecha_postulacion).toLocaleDateString()}
                                            </Badge>
                                        </div>

                                        <div className="flex flex-wrap gap-4 text-sm text-slate-400">
                                            {perfil?.ciudad && (
                                                <span className="flex items-center gap-1"><MapPin size={14} className="text-emerald-500" /> {perfil.ciudad}</span>
                                            )}
                                            {perfil?.titulo_cargo && (
                                                <span className="flex items-center gap-1"><Badge variant="secondary" className="h-5 text-[10px]">{perfil.titulo_cargo}</Badge></span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="flex flex-row md:flex-col gap-2 w-full md:w-auto shrink-0">
                                        {perfil?.cv_url && (
                                            <a href={perfil.cv_url} target="_blank" rel="noopener noreferrer">
                                                <Button variant="outline" size="sm" className="w-full justify-start border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800">
                                                    <Download size={14} className="mr-2" /> Ver CV
                                                </Button>
                                            </a>
                                        )}
                                        {whatsappLink && (
                                            <a href={whatsappLink} target="_blank" rel="noopener noreferrer">
                                                <Button size="sm" className="w-full justify-start bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20">
                                                    <MessageSquare size={14} className="mr-2" /> WhatsApp
                                                </Button>
                                            </a>
                                        )}
                                        <a href={mailtoLink}>
                                            <Button size="sm" className="w-full justify-start bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 border border-cyan-500/20">
                                                <Mail size={14} className="mr-2" /> Email
                                            </Button>
                                        </a>
                                        {perfil?.linkedin_url && (
                                            <a href={perfil.linkedin_url} target="_blank" rel="noopener noreferrer">
                                                <Button variant="ghost" size="sm" className="w-full justify-start text-blue-400 hover:text-blue-300">
                                                    <Linkedin size={14} className="mr-2" /> LinkedIn
                                                </Button>
                                            </a>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
