'use server'

import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { revalidatePath } from "next/cache";

export async function updateProfile(formData: FormData) {
    const session = await getServerSession(authOptions);
    if (!session) return;

    const nombre = formData.get('nombre') as string;
    const apellido = formData.get('apellido') as string;
    const telefono = formData.get('telefono') as string;
    const ubicacion = formData.get('ubicacion') as string;
    const linkedin = formData.get('linkedin') as string;
    const cv_url = formData.get('cv_url') as string;
    const foto_url = formData.get('foto_url') as string;

    // Actualizar o Crear Perfil
    await prisma.perfilCandidato.upsert({
        where: { usuario_id: parseInt(session.user.id) },
        update: {
            nombre,
            apellido,
            telefono,
            ciudad: ubicacion,
            linkedin_url: linkedin,
            cv_url,
            foto_url
        },
        create: {
            usuario_id: parseInt(session.user.id),
            nombre,
            apellido,
            telefono,
            ciudad: ubicacion,
            linkedin_url: linkedin,
            cv_url,
            foto_url
        }
    });

    revalidatePath('/candidato/perfil');
}
