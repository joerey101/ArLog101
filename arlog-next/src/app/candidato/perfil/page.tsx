
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { revalidatePath } from "next/cache";

// Server Action para guardar datos
async function updateProfile(formData: FormData) {
    'use server'

    const session = await getServerSession(authOptions);
    if (!session) return;

    const nombre = formData.get('nombre') as string;
    const apellido = formData.get('apellido') as string;
    const telefono = formData.get('telefono') as string;
    const ubicacion = formData.get('ubicacion') as string;
    const linkedin = formData.get('linkedin') as string;
    const cv_url = formData.get('cv_url') as string;

    // Actualizar o Crear Perfil
    await prisma.perfilCandidato.upsert({
        where: { usuario_id: parseInt(session.user.id) },
        update: {
            nombre,
            apellido,
            telefono,
            ciudad: ubicacion,
            linkedin_url: linkedin,
            cv_url
        },
        create: {
            usuario_id: parseInt(session.user.id),
            nombre,
            apellido,
            telefono,
            ciudad: ubicacion,
            linkedin_url: linkedin,
            cv_url
        }
    });

    revalidatePath('/candidato/perfil');
}

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
                            <div className="w-20 h-20 bg-slate-700 rounded-full mx-auto flex items-center justify-center text-3xl font-bold text-slate-300 mb-4">
                                {perfil?.nombre ? perfil.nombre[0] : email![0].toUpperCase()}
                            </div>
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
                            <form action={updateProfile} className="space-y-6">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="nombre" className="text-slate-300">Nombre</Label>
                                        <Input id="nombre" name="nombre" defaultValue={perfil?.nombre || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Juan" required />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="apellido" className="text-slate-300">Apellido</Label>
                                        <Input id="apellido" name="apellido" defaultValue={perfil?.apellido || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Pérez" required />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="telefono" className="text-slate-300">Teléfono / WhatsApp</Label>
                                    <Input id="telefono" name="telefono" defaultValue={perfil?.telefono || ''} className="bg-slate-900 border-white/10 text-white" placeholder="+54 11 1234 5678" />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="ubicacion" className="text-slate-300">Ubicación (Zona de residencia)</Label>
                                    <Input id="ubicacion" name="ubicacion" defaultValue={perfil?.ciudad || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Ej: Pilar, Zona Norte" />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="linkedin" className="text-slate-300">Perfil de LinkedIn (Opcional)</Label>
                                    <Input id="linkedin" name="linkedin" defaultValue={perfil?.linkedin_url || ''} className="bg-slate-900 border-white/10 text-white" placeholder="https://linkedin.com/in/usuario" />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="cv_url" className="text-slate-300">Enlace a tu CV (Google Drive / PDF)</Label>
                                    <Input id="cv_url" name="cv_url" defaultValue={perfil?.cv_url || ''} className="bg-slate-900 border-white/10 text-white" placeholder="https://drive.google.com/..." />
                                    <p className="text-[10px] text-slate-500">Recomendamos subir tu CV a Google Drive y pegar aquí el enlace "Público".</p>
                                </div>

                                <div className="pt-4">
                                    <Button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold">
                                        Guardar Cambios
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
