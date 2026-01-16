
import { getServerSession } from "next-auth";
import { authOptions } from "../../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { notFound, redirect } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { ArrowLeft, User, FileText, Download } from "lucide-react";
import Link from "next/link";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";

export default async function AdminCandidatoDetailPage({ params }: { params: { id: string } }) {
    const session = await getServerSession(authOptions);
    if (!session || session.user.rol !== 'ADMIN') redirect('/login');

    const userId = parseInt(params.id);
    if (isNaN(userId)) notFound();

    const candidato = await prisma.usuario.findUnique({
        where: { id: userId },
        include: { perfilCandidato: true }
    });

    if (!candidato) notFound();

    const perfil = candidato.perfilCandidato;
    const nombre = perfil?.nombre ? `${perfil.nombre} ${perfil.apellido}` : 'Candidato Sin Nombre';
    const initials = (nombre[0] || '?').toUpperCase();

    return (
        <div className="max-w-4xl mx-auto">
            <div className="mb-6">
                <Link href="/admin/candidatos">
                    <Button variant="ghost" className="text-slate-400 hover:text-white pl-0">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Volver al listado
                    </Button>
                </Link>
            </div>

            <div className="flex items-center gap-6 mb-8">
                <Avatar className="w-24 h-24 border-2 border-slate-700">
                    <AvatarImage src={perfil?.foto_url || undefined} className="object-cover" />
                    <AvatarFallback className="bg-slate-800 text-slate-300 font-bold text-3xl">{initials}</AvatarFallback>
                </Avatar>
                <div>
                    <h1 className="text-3xl font-bold text-white">{nombre}</h1>
                    <p className="text-slate-400">ID Sistema: {candidato.id} • {candidato.email}</p>
                    {perfil?.titulo_cargo && (
                        <p className="text-emerald-400 font-medium mt-1">{perfil.titulo_cargo}</p>
                    )}
                </div>
            </div>

            <div className="grid gap-6">
                <Card className="bg-slate-900 border-white/10">
                    <CardHeader>
                        <CardTitle className="text-white">Datos Personales</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-slate-400">Teléfono</Label>
                                <Input disabled value={perfil?.telefono || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-slate-400">Ubicación</Label>
                                <Input disabled value={perfil?.ciudad || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-slate-400">LinkedIn</Label>
                            <div className="flex gap-2">
                                <Input disabled value={perfil?.linkedin_url || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-slate-400">CV URL</Label>
                            <div className="flex gap-2">
                                <Input disabled value={perfil?.cv_url || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                                {perfil?.cv_url && (
                                    <a href={perfil.cv_url} target="_blank" rel="noopener noreferrer">
                                        <Button size="icon" variant="outline" className="border-white/10"><Download className="h-4 w-4" /></Button>
                                    </a>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Placeholder for future editing functionality */}
                <div className="flex justify-end">
                    <Button variant="outline" className="border-emerald-500/20 text-emerald-500 hover:bg-emerald-500/10" disabled>
                        Editar Información (Próximamente)
                    </Button>
                </div>
            </div>
        </div>
    );
}
