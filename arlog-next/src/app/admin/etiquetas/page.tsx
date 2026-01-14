
import { getServerSession } from "next-auth";
import { authOptions } from "../../api/auth/[...nextauth]/route";
import prisma from "@/lib/prisma";
import { redirect } from "next/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Tag, Plus, Trash2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { TipoEtiqueta } from "@prisma/client";
import { revalidatePath } from "next/cache";

export const dynamic = 'force-dynamic';

async function createTag(formData: FormData) {
    "use server";
    const nombre = formData.get("nombre") as string;
    const categoria = formData.get("categoria") as TipoEtiqueta;

    if (!nombre || !categoria) return;

    try {
        await prisma.etiqueta.create({
            data: { nombre, categoria }
        });
        revalidatePath("/admin/etiquetas");
    } catch (e) {
        console.error("Error creating tag:", e);
    }
}

async function deleteTag(formData: FormData) {
    "use server";
    const id = parseInt(formData.get("id") as string);
    if (!id) return;

    try {
        await prisma.etiqueta.delete({ where: { id } });
        revalidatePath("/admin/etiquetas");
    } catch (e) {
        console.error("Error deleting tag:", e);
    }
}

export default async function AdminEtiquetasPage() {
    const session = await getServerSession(authOptions);

    if (!session || session.user.rol !== 'ADMIN') {
        redirect('/');
    }

    const etiquetas = await prisma.etiqueta.findMany({
        orderBy: { nombre: 'asc' }
    });

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-3xl font-bold text-white mb-2">Gestión de Etiquetas (Skills)</h1>
                <p className="text-slate-400">Administra las habilidades y competencias disponibles para los perfiles y anuncios.</p>
            </div>

            <div className="grid md:grid-cols-3 gap-6">
                {/* Create Form */}
                <Card className="bg-slate-900 border-white/10 h-fit">
                    <CardHeader>
                        <CardTitle className="text-white flex items-center gap-2 text-lg">
                            <Plus className="text-emerald-400" /> Nueva Etiqueta
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form action={createTag} className="space-y-4">
                            <div>
                                <label className="text-xs font-bold text-slate-500 uppercase mb-1 block">Nombre</label>
                                <Input
                                    name="nombre"
                                    placeholder="Ej: React, Inglés, SAP"
                                    className="bg-slate-950 border-white/10 text-white"
                                    required
                                />
                            </div>
                            <div>
                                <label className="text-xs font-bold text-slate-500 uppercase mb-1 block">Categoría</label>
                                <select
                                    name="categoria"
                                    className="w-full h-10 px-3 rounded-md border border-white/10 bg-slate-950 text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                                    required
                                >
                                    <option value="" disabled selected>Seleccionar...</option>
                                    {Object.values(TipoEtiqueta).map((tipo) => (
                                        <option key={tipo} value={tipo}>{tipo.replace('_', ' ')}</option>
                                    ))}
                                </select>
                            </div>
                            <Button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold">
                                Crear Etiqueta
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* List */}
                <Card className="md:col-span-2 bg-slate-900 border-white/10">
                    <CardHeader>
                        <CardTitle className="text-white flex items-center gap-2 text-lg">
                            <Tag className="text-violet-400" /> Etiquetas Existentes ({etiquetas.length})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[600px] overflow-y-auto pr-2">
                            {etiquetas.map((tag) => (
                                <div key={tag.id} className="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5 group hover:border-white/10 transition-colors">
                                    <div>
                                        <div className="font-bold text-white text-sm">{tag.nombre}</div>
                                        <Badge variant="outline" className="mt-1 text-[10px] py-0 h-5 border-white/10 text-slate-400 capitalize">
                                            {tag.categoria?.toLowerCase().replace('_', ' ')}
                                        </Badge>
                                    </div>
                                    <form action={deleteTag}>
                                        <input type="hidden" name="id" value={tag.id} />
                                        <Button size="icon" variant="ghost" className="h-8 w-8 text-slate-600 hover:text-red-400 hover:bg-red-500/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Trash2 size={14} />
                                        </Button>
                                    </form>
                                </div>
                            ))}
                            {etiquetas.length === 0 && (
                                <div className="col-span-full text-center py-8 text-slate-500 italic">
                                    No hay etiquetas creadas aún.
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
