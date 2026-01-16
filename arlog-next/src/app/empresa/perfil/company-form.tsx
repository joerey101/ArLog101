"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import ImageUpload from "@/components/ui/image-upload";
import { updateCompanyProfile } from "./actions"; // We will move the action here
import { useRouter } from "next/navigation";

interface CompanyFormProps {
    initialData: {
        razon_social: string | null;
        rubro: string | null;
        sitio_web: string | null;
        logo_url: string | null;
        descripcion: string | null;
        cuit: string | null;
    } | null;
}

export const CompanyForm: React.FC<CompanyFormProps> = ({ initialData }) => {
    const [logoUrl, setLogoUrl] = useState(initialData?.logo_url || "");
    const router = useRouter();

    const onLogoChange = (url: string) => {
        setLogoUrl(url);
    };

    const onLogoRemove = () => {
        setLogoUrl("");
    };

    return (
        <form action={updateCompanyProfile} className="space-y-5">
            <div className="space-y-2">
                <Label htmlFor="razon_social" className="text-slate-300">Razón Social / Nombre Fantasía</Label>
                <Input
                    id="razon_social"
                    name="razon_social"
                    defaultValue={initialData?.razon_social || ''}
                    className="bg-slate-900 border-white/10 text-white"
                    required
                />
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label htmlFor="cuit" className="text-slate-300">CUIT (Opcional)</Label>
                    <Input
                        id="cuit"
                        name="cuit"
                        defaultValue={initialData?.cuit || ''}
                        className="bg-slate-900 border-white/10 text-white"
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="rubro" className="text-slate-300">Industria</Label>
                    <Input
                        id="rubro"
                        name="rubro"
                        defaultValue={initialData?.rubro || ''}
                        className="bg-slate-900 border-white/10 text-white"
                        placeholder="Ej: Logística, Retail..."
                    />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="sitio_web" className="text-slate-300">Sitio Web</Label>
                <Input
                    id="sitio_web"
                    name="sitio_web"
                    defaultValue={initialData?.sitio_web || ''}
                    className="bg-slate-900 border-white/10 text-white"
                    placeholder="https://miempresa.com"
                />
            </div>

            <div className="space-y-2">
                <Label className="text-slate-300">Logo de la Empresa</Label>
                <div className="bg-slate-900 p-4 rounded-lg border border-white/10">
                    <ImageUpload
                        value={logoUrl ? [logoUrl] : []}
                        onChange={onLogoChange}
                        onRemove={onLogoRemove}
                    />
                    {/* Hidden input to submit the value with FormData */}
                    <input type="hidden" name="logo_url" value={logoUrl} />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="descripcion" className="text-slate-300">Sobre la Empresa</Label>
                <Textarea
                    id="descripcion"
                    name="descripcion"
                    defaultValue={initialData?.descripcion || ''}
                    className="bg-slate-900 border-white/10 text-white h-32"
                    placeholder="Describe brevemente a qué se dedican..."
                />
            </div>

            <Button type="submit" className="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold">
                Actualizar Perfil
            </Button>
        </form>
    );
};
