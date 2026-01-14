
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { Badge } from "@/components/ui/badge";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { redirect } from "next/navigation";

export default async function AdminUsuariosPage() {
    const session = await getServerSession(authOptions);
    if (!session || session.user.rol !== 'admin') redirect('/login');

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
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-white">Directorio Global de Usuarios</h1>
                <Badge variant="outline" className="text-slate-400">Mostrando últimos 50</Badge>
            </div>

            <div className="rounded-md border border-white/10 bg-slate-900/50">
                <Table>
                    <TableHeader>
                        <TableRow className="border-white/10 hover:bg-white/5">
                            <TableHead className="text-slate-400">ID</TableHead>
                            <TableHead className="text-slate-400">Email</TableHead>
                            <TableHead className="text-slate-400">Rol</TableHead>
                            <TableHead className="text-slate-400">Nombre / Razón Social</TableHead>
                            <TableHead className="text-slate-400">Estado</TableHead>
                            <TableHead className="text-slate-400 text-right">Registro</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {usuarios.map((u) => {
                            const nombreDisplay = u.rol === 'candidato'
                                ? `${u.perfilCandidato?.nombre || ''} ${u.perfilCandidato?.apellido || ''}`
                                : u.perfilEmpresa?.razon_social || '-';

                            return (
                                <TableRow key={u.id} className="border-white/5 hover:bg-white/5 data-[state=selected]:bg-muted">
                                    <TableCell className="font-medium text-slate-500">#{u.id}</TableCell>
                                    <TableCell className="text-white">{u.email}</TableCell>
                                    <TableCell>
                                        <Badge className={
                                            u.rol === 'admin' ? 'bg-red-500/20 text-red-400' :
                                                u.rol === 'empresa' ? 'bg-cyan-500/20 text-cyan-400' :
                                                    'bg-emerald-500/20 text-emerald-400'
                                        }>
                                            {u.rol}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-slate-300">{nombreDisplay.trim() || 'Sin completar perfil'}</TableCell>
                                    <TableCell>
                                        {u.estado === 'activo' ? (
                                            <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-500">Activo</span>
                                        ) : (
                                            <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-500">{u.estado}</span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right text-slate-500 text-xs">
                                        {new Date(u.fecha_registro).toLocaleDateString()}
                                    </TableCell>
                                </TableRow>
                            );
                        })}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
