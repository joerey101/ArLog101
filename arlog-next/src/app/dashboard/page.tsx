import { getServerSession } from "next-auth";
import { authOptions } from "../api/auth/[...nextauth]/route";
import { redirect } from "next/navigation";

export default async function DashboardRedirect() {
    const session = await getServerSession(authOptions);

    if (!session) {
        redirect('/login');
    }

    const rol = session.user.rol;

    // Normalizar a mayúsculas para asegurar coincidencia con Enum de Prisma
    const rolUpper = rol?.toUpperCase();

    if (rolUpper === 'CANDIDATO') {
        redirect('/candidato/dashboard');
    } else if (rolUpper === 'EMPRESA') {
        redirect('/empresa/dashboard');
    } else if (rolUpper === 'ADMIN') {
        redirect('/admin/dashboard');
    } else {
        // Fallback por seguridad
        return (
            <div className="p-8 text-white">
                Error: Rol desconocido ({rol}). Contacte a soporte.
            </div>
        );
    }
}
