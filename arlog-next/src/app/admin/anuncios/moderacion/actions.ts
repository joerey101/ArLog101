'use server'

import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { revalidatePath } from "next/cache";

// Verificar que sea admin
async function checkAdmin() {
    const session = await getServerSession(authOptions);
    if (!session || session.user.rol !== 'admin') {
        throw new Error("No autorizado");
    }
    return session;
}

export async function approveJob(jobId: number) {
    await checkAdmin();

    await prisma.anuncio.update({
        where: { id: jobId },
        data: { estado: 'ACTIVO' }
    });

    revalidatePath('/admin/anuncios/moderacion');
}

export async function rejectJob(jobId: number) {
    await checkAdmin();

    await prisma.anuncio.update({
        where: { id: jobId },
        data: { estado: 'RECHAZADO' }
    });

    revalidatePath('/admin/anuncios/moderacion');
}
