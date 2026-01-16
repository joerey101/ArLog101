
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { CompanyForm } from "./company-form";

export default async function EmpresaPerfilPage() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    const perfil = await prisma.perfilEmpresa.findUnique({
        where: { usuario_id: parseInt(session.user.id) }
    });

    // Fix Legacy Images logic
    const displayLogo = perfil?.logo_url?.startsWith('http')
        ? perfil.logo_url
        : perfil?.logo_url
            ? `https://arlogjobs.joserey101.com/${perfil.logo_url}`
            : null;

    return (
        <div className="max-w-4xl mx-auto">
            <h1 className="text-3xl font-bold text-white mb-8">Marca Empleadora</h1>

            <div className="grid md:grid-cols-3 gap-6">
                {/* Preview */}
                <div className="md:col-span-1">
                    <Card className="bg-white/5 border-white/10 sticky top-8">
                        <CardHeader className="text-center">
                            {displayLogo ? (
                                <img src={displayLogo} alt="Logo" className="w-24 h-24 mx-auto rounded-lg object-contain bg-white p-2 mb-4" />
                            ) : (
                                <div className="w-24 h-24 bg-slate-700 rounded-lg mx-auto flex items-center justify-center text-4xl font-bold text-slate-300 mb-4">
                                    <Building2Icon />
                                </div>
                            )}
                            <CardTitle className="text-white">{perfil?.razon_social || 'Nueva Empresa'}</CardTitle>
                            <CardDescription>{perfil?.rubro || 'Industria sin definir'}</CardDescription>
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
                            <CompanyForm initialData={perfil} />
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
