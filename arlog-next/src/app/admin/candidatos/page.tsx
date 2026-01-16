
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, FileText, ExternalLink } from "lucide-react";
import { Rol } from "@prisma/client";
import Link from "next/link";
import { Button } from "@/components/ui/button";

export const dynamic = 'force-dynamic';

export default async function AdminCandidatosPage() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'ADMIN') {
        redirect('/');
    }

    const candidatos = await prisma.usuario.findMany({
        where: { rol: Rol.CANDIDATO },
        take: 50,
        orderBy: { fecha_registro: 'desc' },
        include: {
            perfilCandidato: true,
            _count: { select: { postulaciones: true } }
        }
    });

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Gestión de Candidatos</h1>
                <p className="text-slate-400">Usuarios registrados buscando empleo.</p>
            </div>

            <Card className="bg-slate-900 border-white/10 overflow-hidden">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <Users className="text-emerald-400" /> Candidatos Recientes
                    </CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left text-slate-400">
                            <thead className="text-xs text-slate-300 uppercase bg-slate-950/50 border-b border-white/10">
                                <tr>
                                    <th className="px-6 py-4">Nombre</th>
                                    <th className="px-6 py-4">Título / Cargo</th>
                                    <th className="px-6 py-4">Ubicación</th>
                                    <th className="px-6 py-4">Email Contacto</th>
                                    <th className="px-6 py-4 text-center">Postulaciones</th>
                                    <th className="px-6 py-4 text-right">CV</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {candidatos.map((user) => (
                                    <tr key={user.id} className="hover:bg-white/5 transition-colors">
                                        <td className="px-6 py-4">
                                            <Link href={`/admin/candidatos/${user.id}`} className="group">
                                                <div className="text-white font-medium group-hover:text-emerald-400 transition-colors">
                                                    {user.perfilCandidato?.nombre} {user.perfilCandidato?.apellido}
                                                </div>
                                                <div className="text-xs text-slate-500">ID: #{user.id}</div>
                                            </Link>
                                        </td>
                                        <td className="px-6 py-4 text-slate-300">
                                            {user.perfilCandidato?.titulo_cargo || 'Sin título'}
                                        </td>
                                        <td className="px-6 py-4">
                                            {user.perfilCandidato?.ciudad || '-'}, {user.perfilCandidato?.provincia || '-'}
                                        </td>
                                        <td className="px-6 py-4 font-mono text-xs">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <Badge variant="secondary" className="bg-slate-800 text-slate-300 border-white/5">
                                                {user._count.postulaciones}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            {user.perfilCandidato?.cv_url ? (
                                                <a href={user.perfilCandidato.cv_url} target="_blank" rel="noopener noreferrer">
                                                    <Button size="sm" variant="outline" className="h-8 border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/10">
                                                        <FileText size={14} className="mr-1" /> Ver CV
                                                    </Button>
                                                </a>
                                            ) : (
                                                <span className="text-slate-600 text-xs italic">Sin CV</span>
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
