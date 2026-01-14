
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Building2, Globe, MapPin } from "lucide-react";
import { Rol } from "@prisma/client";
import Link from "next/link";
import { Button } from "@/components/ui/button";

export const dynamic = 'force-dynamic';

export default async function AdminEmpresasPage() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'ADMIN') {
        redirect('/');
    }

    const empresas = await prisma.usuario.findMany({
        where: { rol: Rol.EMPRESA },
        take: 50,
        orderBy: { fecha_registro: 'desc' },
        include: {
            perfilEmpresa: true,
            _count: { select: { anuncios: true } }
        }
    });

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Gestión de Empresas</h1>
                <p className="text-slate-400">Organizaciones registradas en la plataforma.</p>
            </div>

            <Card className="bg-slate-900 border-white/10 overflow-hidden">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <Building2 className="text-cyan-400" /> Empresas Registradas
                    </CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left text-slate-400">
                            <thead className="text-xs text-slate-300 uppercase bg-slate-950/50 border-b border-white/10">
                                <tr>
                                    <th className="px-6 py-4">Razón Social</th>
                                    <th className="px-6 py-4">Rubro</th>
                                    <th className="px-6 py-4">Ubicación</th>
                                    <th className="px-6 py-4">Email Contacto</th>
                                    <th className="px-6 py-4 text-center">Anuncios</th>
                                    <th className="px-6 py-4 text-right">Web</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {empresas.map((user) => (
                                    <tr key={user.id} className="hover:bg-white/5 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="text-white font-medium text-base">
                                                {user.perfilEmpresa?.razon_social || 'Pendiente'}
                                            </div>
                                            <div className="text-xs text-slate-500">ID: #{user.id}</div>
                                        </td>
                                        <td className="px-6 py-4 text-slate-300">
                                            {user.perfilEmpresa?.rubro || '-'}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-1.5">
                                                <MapPin size={14} className="text-slate-500" />
                                                {user.perfilEmpresa?.ubicacion || '-'}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 font-mono text-xs">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <Badge variant="outline" className="border-cyan-500/20 text-cyan-400 bg-cyan-500/5">
                                                {user._count.anuncios}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            {user.perfilEmpresa?.sitio_web ? (
                                                <a href={user.perfilEmpresa.sitio_web} target="_blank" rel="noopener noreferrer">
                                                    <Button size="sm" variant="ghost" className="h-8 text-cyan-400 hover:text-cyan-300 hover:bg-cyan-500/10">
                                                        <Globe size={14} />
                                                    </Button>
                                                </a>
                                            ) : (
                                                <span className="text-slate-600 text-xs">-</span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
