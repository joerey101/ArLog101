
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { User } from "lucide-react";

export const dynamic = 'force-dynamic';

export default async function AdminUsuariosPage() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'ADMIN') {
        redirect('/');
    }

    const usuarios = await prisma.usuario.findMany({
        take: 50,
        orderBy: { fecha_registro: 'desc' },
        include: {
            perfilCandidato: { select: { nombre: true, apellido: true } },
            perfilEmpresa: { select: { razon_social: true } }
        }
    });

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Gestión de Usuarios</h1>
                <p className="text-slate-400">Últimos 50 usuarios registrados.</p>
            </div>

            <Card className="bg-slate-900 border-white/10 overflow-hidden">
                <CardHeader>
                    <CardTitle className="text-white flex items-center gap-2">
                        <User className="text-violet-400" /> Listado General
                    </CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left text-slate-400">
                            <thead className="text-xs text-slate-300 uppercase bg-slate-950/50 border-b border-white/10">
                                <tr>
                                    <th className="px-6 py-4">ID</th>
                                    <th className="px-6 py-4">Email / Usuario</th>
                                    <th className="px-6 py-4">Rol</th>
                                    <th className="px-6 py-4">Nombre / Razón Social</th>
                                    <th className="px-6 py-4">Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {usuarios.map((user) => (
                                    <tr key={user.id} className="hover:bg-white/5 transition-colors">
                                        <td className="px-6 py-4 font-mono text-slate-500">#{user.id}</td>
                                        <td className="px-6 py-4 text-white font-medium">{user.email}</td>
                                        <td className="px-6 py-4">
                                            <Badge variant="outline" className={`
                                                ${user.rol === 'ADMIN' ? 'border-violet-500/50 text-violet-400 bg-violet-500/10' : ''}
                                                ${user.rol === 'EMPRESA' ? 'border-cyan-500/50 text-cyan-400 bg-cyan-500/10' : ''}
                                                ${user.rol === 'CANDIDATO' ? 'border-emerald-500/50 text-emerald-400 bg-emerald-500/10' : ''}
                                            `}>
                                                {user.rol}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4">
                                            {user.perfilCandidato
                                                ? `${user.perfilCandidato.nombre} ${user.perfilCandidato.apellido}`
                                                : user.perfilEmpresa?.razon_social || '-'
                                            }
                                        </td>
                                        <td className="px-6 py-4">
                                            {new Date(user.fecha_registro).toLocaleDateString()}
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
