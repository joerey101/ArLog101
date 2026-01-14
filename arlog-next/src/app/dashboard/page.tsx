import { getServerSession } from "next-auth";
import { authOptions } from "../api/auth/[...nextauth]/route";
import { redirect } from "next/navigation";

export default async function DashboardRedirect() {
    const session = await getServerSession(authOptions);

    if (!session) {
        redirect('/login');
    }

    const rol = session.user.rol;

    if (rol === 'candidato') {
        redirect('/candidato/dashboard');
    } else if (rol === 'empresa') {
        redirect('/empresa/dashboard');
    } else if (rol === 'admin') {
        redirect('/admin/dashboard'); // Asumiendo que quisieras crear este también
    } else {
        // Fallback por seguridad
        return (
            <div className="p-8 text-white">
                Error: Rol desconocido ({rol}). Contacte a soporte.
            </div>
        );
    }
}
