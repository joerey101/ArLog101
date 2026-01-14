
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import Link from "next/link";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Users, Briefcase, FileText, Activity } from "lucide-react";
import { redirect } from "next/navigation";

export default async function AdminDashboard() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'admin') {
        redirect('/login'); // Protección extra, aunque el middleware o page redirect ya lo haría
    }

    // Métricas reales
    const [
        totalCandidatos,
        totalEmpresas,
        totalAnuncios,
        totalPostulaciones
    ] = await Promise.all([
        prisma.usuario.count({ where: { rol: 'candidato' } }),
        prisma.usuario.count({ where: { rol: 'empresa' } }),
        prisma.anuncio.count(),
        prisma.postulacion.count()
    ]);

    return (
        <div className="space-y-8">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Panel de Supervisión</h1>
                <p className="text-slate-400">Estado del sistema en tiempo real.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <MetricCard title="Candidatos" value={totalCandidatos} icon={<Users className="text-emerald-500" />} />
                <MetricCard title="Empresas" value={totalEmpresas} icon={<Briefcase className="text-cyan-500" />} />
                <MetricCard title="Anuncios Totales" value={totalAnuncios} icon={<Activity className="text-yellow-500" />} />
                <MetricCard title="Postulaciones" value={totalPostulaciones} icon={<FileText className="text-purple-500" />} />
            </div>

            <div className="grid md:grid-cols-2 gap-6">
                <Card className="bg-slate-900 border-white/5">
                    <CardHeader>
                        <CardTitle className="text-white">Control de Calidad</CardTitle>
                        <CardDescription>Accesos directos a moderación</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-2">
                        <Link href="/admin/usuarios" className="p-3 bg-white/5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 flex justify-between">
                            <span>Ver todos los usuarios</span>
                            <Users size={16} />
                        </Link>
                        <Link href="/admin/anuncios" className="p-3 bg-white/5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 flex justify-between">
                            <span>Moderación de Anuncios</span>
                            <ShieldAlertIcon />
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}

function MetricCard({ title, value, icon }: { title: string, value: number, icon: React.ReactNode }) {
    return (
        <Card className="bg-slate-900 border-white/5">
            <CardHeader className="flex flex-row items-center justify-between pb-2">
                <CardTitle className="text-sm font-medium text-slate-400 uppercase tracking-wider">{title}</CardTitle>
                {icon}
            </CardHeader>
            <CardContent>
                <div className="text-3xl font-bold text-white">{value}</div>
            </CardContent>
        </Card>
    )
}

function ShieldAlertIcon() { return <Activity size={16} /> } // Placeholder
