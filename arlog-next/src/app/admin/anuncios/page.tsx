
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Briefcase, Eye } from "lucide-react";
import Link from "next/link";
import { Button } from "@/components/ui/button";

export const dynamic = 'force-dynamic';

export default async function AdminAnunciosPage() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'ADMIN') {
        redirect('/');
    }

    const anuncios = await prisma.anuncio.findMany({
        take: 50,
        orderBy: { fecha_publicacion: 'desc' },
        include: {
            empresa_perfil: { select: { razon_social: true } },
            _count: { select: { postulaciones: true } }
        }
    });

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Gestión de Anuncios</h1>
                <p className="text-slate-400">Supervisión de ofertas laborales publicadas.</p>
            </div>

            <Card className="bg-slate-900 border-white/10 overflow-hidden">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <Briefcase className="text-yellow-400" /> Listado de Ofertas
                    </CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left text-slate-400">
                            <thead className="text-xs text-slate-300 uppercase bg-slate-950/50 border-b border-white/10">
                                <tr>
                                    <th className="px-6 py-4">Título</th>
                                    <th className="px-6 py-4">Empresa</th>
                                    <th className="px-6 py-4">Ubicación</th>
                                    <th className="px-6 py-4">Estado</th>
                                    <th className="px-6 py-4 text-center">Postulantes</th>
                                    <th className="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {anuncios.map((job) => (
                                    <tr key={job.id} className="hover:bg-white/5 transition-colors">
                                        <td className="px-6 py-4 text-white font-medium">{job.titulo}</td>
                                        <td className="px-6 py-4">{job.empresa_perfil?.razon_social || 'Confidencial'}</td>
                                        <td className="px-6 py-4">{job.ubicacion}</td>
                                        <td className="px-6 py-4">
                                            <Badge variant="outline" className={`
                                                ${job.estado === 'ACTIVO' ? 'border-emerald-500/50 text-emerald-400 bg-emerald-500/10' : ''}
                                                ${job.estado === 'PAUSADO' ? 'border-yellow-500/50 text-yellow-400 bg-yellow-500/10' : ''}
                                                ${job.estado === 'CERRADO' ? 'border-red-500/50 text-red-400 bg-red-500/10' : ''}
                                            `}>
                                                {job.estado}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4 text-center font-bold text-white">
                                            {job._count.postulaciones}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link href={`/empleos/${job.id}`}>
                                                <Button size="icon" variant="ghost" className="hover:text-emerald-400 hover:bg-emerald-500/10">
                                                    <Eye size={16} />
                                                </Button>
                                            </Link>
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
