
import { getServerSession } from "next-auth";
import { authOptions } from "../../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { notFound, redirect } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { ArrowLeft, Building2, Globe, Mail } from "lucide-react";
import Link from "next/link";
import Image from "next/image";

export default async function AdminEmpresaDetailPage({ params }: { params: { id: string } }) {
    const session = await getServerSession(authOptions);
    if (!session || session.user.rol !== 'ADMIN') redirect('/login');

    const userId = parseInt(params.id);
    if (isNaN(userId)) notFound();

    const empresa = await prisma.usuario.findUnique({
        where: { id: userId },
        include: { perfilEmpresa: true }
    });

    if (!empresa) notFound();

    const perfil = empresa.perfilEmpresa;

    return (
        <div className="max-w-4xl mx-auto">
            <div className="mb-6">
                <Link href="/admin/empresas">
                    <Button variant="ghost" className="text-slate-400 hover:text-white pl-0">
                        <ArrowLeft className="mr-2 h-4 w-4" /> Volver al listado
                    </Button>
                </Link>
            </div>

            <div className="flex items-center gap-6 mb-8">
                <div className="w-24 h-24 bg-slate-800 rounded-xl flex items-center justify-center border border-white/10 overflow-hidden relative">
                    {perfil?.logo_url ? (
                        <Image src={perfil.logo_url} alt="Logo" fill className="object-cover" />
                    ) : (
                        <Building2 className="w-10 h-10 text-slate-500" />
                    )}
                </div>
                <div>
                    <h1 className="text-3xl font-bold text-white">{perfil?.razon_social || 'Empresa Sin Nombre'}</h1>
                    <p className="text-slate-400">ID Sistema: {empresa.id} • {empresa.email}</p>
                </div>
            </div>

            <div className="grid gap-6">
                <Card className="bg-slate-900 border-white/10">
                    <CardHeader>
                        <CardTitle className="text-white">Información General</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-slate-400">Razón Social</Label>
                                <Input disabled value={perfil?.razon_social || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-slate-400">Email de Cuenta</Label>
                                <Input disabled value={empresa.email} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label className="text-slate-400">CUIT</Label>
                                <Input disabled value={perfil?.cuit || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                            <div className="space-y-2">
                                <Label className="text-slate-400">Rubro / Industria</Label>
                                <Input disabled value={perfil?.rubro || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-slate-400">Sitio Web</Label>
                            <div className="flex gap-2">
                                <Input disabled value={perfil?.sitio_web || ''} className="bg-slate-950 border-white/10 text-slate-300" />
                                {perfil?.sitio_web && (
                                    <a href={perfil.sitio_web} target="_blank" rel="noopener noreferrer">
                                        <Button size="icon" variant="outline" className="border-white/10"><Globe className="h-4 w-4" /></Button>
                                    </a>
                                )}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-slate-400">Descripción</Label>
                            <Textarea disabled value={perfil?.descripcion || ''} className="bg-slate-950 border-white/10 text-slate-300 min-h-[120px]" />
                        </div>
                    </CardContent>
                </Card>

                {/* Placeholder for future editing functionality */}
                <div className="flex justify-end">
                    <Button variant="outline" className="border-cyan-500/20 text-cyan-500 hover:bg-cyan-500/10" disabled>
                        Editar Información (Próximamente)
                    </Button>
                </div>
            </div>
        </div>
    );
}
