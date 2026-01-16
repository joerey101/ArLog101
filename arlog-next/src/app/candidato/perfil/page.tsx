
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { CandidateForm } from "./candidate-form";

export default async function PerfilPage() {
    const session = await getServerSession(authOptions);

    if (!session) redirect('/login');

    // Obtener datos actuales
    const perfil = await prisma.perfilCandidato.findUnique({
        where: { usuario_id: parseInt(session.user.id) }
    });

    const email = session.user.email;

    return (
        <div className="max-w-4xl mx-auto">
            <h1 className="text-3xl font-bold text-white mb-8">Mi Perfil Profesional</h1>

            <div className="grid md:grid-cols-3 gap-6">
                {/* Tarjeta Resumen */}
                <div className="md:col-span-1">
                    <Card className="bg-white/5 border-white/10 sticky top-8">
                        <CardHeader className="text-center">
                            {perfil?.foto_url ? (
                                <img src={perfil.foto_url} alt="Profile" className="w-20 h-20 rounded-full mx-auto object-cover mb-4 border-2 border-emerald-500/50" />
                            ) : (
                                <div className="w-20 h-20 bg-slate-700 rounded-full mx-auto flex items-center justify-center text-3xl font-bold text-slate-300 mb-4">
                                    {perfil?.nombre ? perfil.nombre[0] : email![0].toUpperCase()}
                                </div>
                            )}
                            <CardTitle className="text-white">{perfil?.nombre || 'Usuario'} {perfil?.apellido || ''}</CardTitle>
                            <CardDescription>{email}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-sm text-slate-400 space-y-2">
                                <p className="flex justify-between"><span>Estado:</span> <span className="text-emerald-400 font-bold">Activo</span></p>
                                <p className="flex justify-between"><span>CV Cargado:</span> <span className={perfil?.cv_url ? "text-emerald-400" : "text-red-400"}>{perfil?.cv_url ? 'Sí' : 'No'}</span></p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Formulario Principal */}
                <div className="md:col-span-2">
                    <Card className="bg-white/5 border-white/10">
                        <CardHeader>
                            <CardTitle className="text-white">Información Personal</CardTitle>
                            <CardDescription>Mantén tus datos actualizados para que las empresas te contacten.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <CandidateForm initialData={perfil} />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
