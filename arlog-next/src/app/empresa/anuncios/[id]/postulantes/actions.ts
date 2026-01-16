"use server";

import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { EstadoPostulacion, Rol } from "@prisma/client";
import { revalidatePath } from "next/cache";

export async function updateApplicationStatus(postulacionId: number, newState: EstadoPostulacion, path: string) {
    const session = await getServerSession(authOptions);

    // Authorization check
    if (!session || session.user.rol !== Rol.EMPRESA) {
        throw new Error("Unauthorized");
    }

    // Verify ownership indirectly or trust ID if we are strict. 
    // Best practice: Check if the job belongs to the company user
    // However, for speed, assuming the POST request comes from a valid context.

    // To be safer:
    const postulacion = await prisma.postulacion.findUnique({
        where: { id: postulacionId },
        include: { anuncio: true }
    });

    if (!postulacion) throw new Error("Application not found");
    if (postulacion.anuncio.usuario_id !== parseInt(session.user.id)) {
        throw new Error("Unauthorized access to this application");
    }

    // Update
    await prisma.postulacion.update({
        where: { id: postulacionId },
        data: { estado: newState }
    });

    revalidatePath(path);
}
