'use server'

import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { revalidatePath } from "next/cache";

export async function updateCompanyProfile(formData: FormData) {
    const session = await getServerSession(authOptions);
    if (!session) return;

    const razon_social = formData.get('razon_social') as string;
    const rubro = formData.get('rubro') as string;
    const sitio_web = formData.get('sitio_web') as string;
    const logo_url = formData.get('logo_url') as string;
    const descripcion = formData.get('descripcion') as string;
    const cuit = formData.get('cuit') as string;

    await prisma.perfilEmpresa.upsert({
        where: { usuario_id: parseInt(session.user.id) },
        update: { razon_social, rubro, sitio_web, logo_url, descripcion, cuit },
        create: {
            usuario_id: parseInt(session.user.id),
            razon_social, rubro, sitio_web, logo_url, descripcion, cuit
        }
    });

    revalidatePath('/empresa/perfil');
}
