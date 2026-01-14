
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { revalidatePath } from "next/cache";

async function updateCompanyProfile(formData: FormData) {
    'use server'

    const session = await getServerSession(authOptions);
    if (!session) return;

    const razon_social = formData.get('razon_social') as string;
    const industria = formData.get('industria') as string;
    const sitio_web = formData.get('sitio_web') as string;
    const logo_url = formData.get('logo_url') as string;
    const descripcion = formData.get('descripcion') as string;
    const cuit = formData.get('cuit') as string;

    await prisma.perfilEmpresa.upsert({
        where: { usuario_id: parseInt(session.user.id) },
        update: { razon_social, industria, sitio_web, logo_url, descripcion, cuit },
        create: {
            usuario_id: parseInt(session.user.id),
            razon_social, industria, sitio_web, logo_url, descripcion, cuit
        }
    });

    revalidatePath('/empresa/perfil');
}

export default async function EmpresaPerfilPage() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    const perfil = await prisma.perfilEmpresa.findUnique({
        where: { usuario_id: parseInt(session.user.id) }
    });

    return (
        <div className="max-w-4xl mx-auto">
            <h1 className="text-3xl font-bold text-white mb-8">Marca Empleadora</h1>

            <div className="grid md:grid-cols-3 gap-6">
                {/* Preview */}
                <div className="md:col-span-1">
                    <Card className="bg-white/5 border-white/10 sticky top-8">
                        <CardHeader className="text-center">
                            {perfil?.logo_url ? (
                                <img src={perfil.logo_url} alt="Logo" className="w-24 h-24 mx-auto rounded-lg object-contain bg-white p-2 mb-4" />
                            ) : (
                                <div className="w-24 h-24 bg-slate-700 rounded-lg mx-auto flex items-center justify-center text-4xl font-bold text-slate-300 mb-4">
                                    <Building2Icon />
                                </div>
                            )}
                            <CardTitle className="text-white">{perfil?.razon_social || 'Nueva Empresa'}</CardTitle>
                            <CardDescription>{perfil?.industria || 'Industria sin definir'}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-xs text-slate-400">
                                Esta información será visible para todos los candidatos en tus avisos.
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Formulario */}
                <div className="md:col-span-2">
                    <Card className="bg-white/5 border-white/10">
                        <CardHeader>
                            <CardTitle className="text-white">Datos Institucionales</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form action={updateCompanyProfile} className="space-y-5">
                                <div className="space-y-2">
                                    <Label htmlFor="razon_social" className="text-slate-300">Razón Social / Nombre Fantasía</Label>
                                    <Input id="razon_social" name="razon_social" defaultValue={perfil?.razon_social || ''} className="bg-slate-900 border-white/10 text-white" required />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="cuit" className="text-slate-300">CUIT (Opcional)</Label>
                                        <Input id="cuit" name="cuit" defaultValue={perfil?.cuit || ''} className="bg-slate-900 border-white/10 text-white" />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="industria" className="text-slate-300">Industria</Label>
                                        <Input id="industria" name="industria" defaultValue={perfil?.industria || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Ej: Logística, Retail..." />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sitio_web" className="text-slate-300">Sitio Web</Label>
                                    <Input id="sitio_web" name="sitio_web" defaultValue={perfil?.sitio_web || ''} className="bg-slate-900 border-white/10 text-white" placeholder="https://miempresa.com" />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="logo_url" className="text-slate-300">URL del Logo (Imagen)</Label>
                                    <Input id="logo_url" name="logo_url" defaultValue={perfil?.logo_url || ''} className="bg-slate-900 border-white/10 text-white" placeholder="https://..." />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="descripcion" className="text-slate-300">Sobre la Empresa</Label>
                                    <Textarea id="descripcion" name="descripcion" defaultValue={perfil?.descripcion || ''} className="bg-slate-900 border-white/10 text-white h-32" placeholder="Describe brevemente a qué se dedican..." />
                                </div>

                                <Button type="submit" className="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold">
                                    Actualizar Perfil
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}

function Building2Icon() { // Simple Icon Fallback 
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="lucide lucide-building-2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" /><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" /><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" /><path d="M10 6h4" /><path d="M10 10h4" /><path d="M10 14h4" /><path d="M10 18h4" /></svg>
    )
}
