"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import ImageUpload from "@/components/ui/image-upload";
import { updateProfile } from "./actions";
import { ExternalLink } from "lucide-react";

interface CandidateFormProps {
    initialData: {
        nombre: string | null;
        apellido: string | null;
        telefono: string | null;
        ciudad: string | null;
        linkedin_url: string | null;
        cv_url: string | null;
        foto_url: string | null;
    } | null;
}

export const CandidateForm: React.FC<CandidateFormProps> = ({ initialData }) => {
    const [fotoUrl, setFotoUrl] = useState(initialData?.foto_url || "");

    const onFotoChange = (url: string) => {
        setFotoUrl(url);
    };

    const onFotoRemove = () => {
        setFotoUrl("");
    };

    return (
        <form action={updateProfile} className="space-y-6">

            <div className="space-y-2">
                <Label className="text-slate-300">Foto de Perfil</Label>
                <div className="bg-slate-900 p-4 rounded-lg border border-white/10">
                    <div className="flex items-center gap-4">
                        <div className="flex-1">
                            <ImageUpload
                                value={fotoUrl ? [fotoUrl] : []}
                                onChange={onFotoChange}
                                onRemove={onFotoRemove}
                            />
                            <input type="hidden" name="foto_url" value={fotoUrl} />
                        </div>
                        <div className="text-xs text-slate-500 max-w-[200px]">
                            Sube una foto profesional. Esto aumenta tus chances de ser contactado.
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label htmlFor="nombre" className="text-slate-300">Nombre</Label>
                    <Input id="nombre" name="nombre" defaultValue={initialData?.nombre || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Juan" required />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="apellido" className="text-slate-300">Apellido</Label>
                    <Input id="apellido" name="apellido" defaultValue={initialData?.apellido || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Pérez" required />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="telefono" className="text-slate-300">Teléfono / WhatsApp</Label>
                <Input id="telefono" name="telefono" defaultValue={initialData?.telefono || ''} className="bg-slate-900 border-white/10 text-white" placeholder="+54 11 1234 5678" />
            </div>

            <div className="space-y-2">
                <Label htmlFor="ubicacion" className="text-slate-300">Ubicación (Zona de residencia)</Label>
                <Input id="ubicacion" name="ubicacion" defaultValue={initialData?.ciudad || ''} className="bg-slate-900 border-white/10 text-white" placeholder="Ej: Pilar, Zona Norte" />
            </div>

            <div className="space-y-2">
                <Label htmlFor="linkedin" className="text-slate-300">Perfil de LinkedIn (Opcional)</Label>
                <Input id="linkedin" name="linkedin" defaultValue={initialData?.linkedin_url || ''} className="bg-slate-900 border-white/10 text-white" placeholder="https://linkedin.com/in/usuario" />
            </div>

            <div className="space-y-2">
                <Label htmlFor="cv_url" className="text-slate-300">Enlace a tu CV (Google Drive / PDF)</Label>
                <div className="flex gap-2">
                    <Input id="cv_url" name="cv_url" defaultValue={initialData?.cv_url || ''} className="bg-slate-900 border-white/10 text-white" placeholder="https://drive.google.com/..." />
                    {initialData?.cv_url && (
                        <Button type="button" variant="outline" size="icon" onClick={() => window.open(initialData.cv_url!, '_blank')}>
                            <ExternalLink className="h-4 w-4" />
                        </Button>
                    )}
                </div>
                <p className="text-[10px] text-slate-500">Recomendamos subir tu CV a Google Drive y pegar aquí el enlace "Público".</p>
            </div>

            <div className="pt-4">
                <Button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold">
                    Guardar Cambios
                </Button>
            </div>
        </form>
    );
};
