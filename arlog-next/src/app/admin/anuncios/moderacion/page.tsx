import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { PendingJobsList } from "./pending-jobs-list";

export default async function ModeracionPage() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'admin') {
        redirect('/login');
    }

    const pendingJobs = await prisma.anuncio.findMany({
        where: {
            estado: 'PENDIENTE'
        },
        include: {
            empresa_perfil: {
                select: {
                    razon_social: true,
                    logo_url: true
                }
            }
        },
        orderBy: {
            fecha_publicacion: 'desc'
        }
    });

    return (
        <div className="p-6 max-w-5xl mx-auto">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">Moderación de Avisos</h1>
                <p className="text-slate-400">Revisa y aprueba los avisos antes de que sean visibles para los candidatos.</p>
            </div>

            <PendingJobsList initialJobs={pendingJobs} />
        </div>
    );
}
