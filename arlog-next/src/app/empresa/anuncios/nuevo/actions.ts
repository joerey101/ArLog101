'use server';

import { getServerSession } from "next-auth";
import { authOptions } from "../../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { revalidatePath } from "next/cache";

export async function createJobAction(formData: FormData) {
    const session = await getServerSession(authOptions);
    if (!session) return { success: false, message: 'No autorizado' };

    try {
        const titulo = formData.get('titulo') as string;
        const descripcion = formData.get('descripcion') as string;
        const ubicacion = formData.get('ubicacion') as string;
        const departamento = formData.get('departamento') as string;
        const modalidad = formData.get('modalidad') as any; // Enum validation simplified
        const tipo_contrato = formData.get('tipo_contrato') as string;
        const rango_salarial = formData.get('rango_salarial') as string;

        await prisma.anuncio.create({
            data: {
                usuario_id: parseInt(session.user.id),
                titulo,
                descripcion,
                ubicacion,
                departamento,
                modalidad, // 'presencial' | 'remoto' | 'hibrido' matches Enum
                tipo_contrato,
                rango_salarial,
                estado: 'activo'
            }
        });

        revalidatePath('/empresa/anuncios');
        revalidatePath('/empleos'); // Update public listing
        return { success: true };

    } catch (error) {
        console.error(error);
        return { success: false, message: 'Error al guardar en base de datos' };
    }
}
