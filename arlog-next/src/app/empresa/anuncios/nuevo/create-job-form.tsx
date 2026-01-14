'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { createJobAction } from './actions';
import { Loader2 } from 'lucide-react';

export function CreateJobForm({ userId }: { userId: string }) {
    const router = useRouter();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    // Estados para selectores (ya que Shadcn Select no expone 'name' directamente al form data a veces sin input hidden)
    const [modalidad, setModalidad] = useState('presencial');
    const [tipo, setTipo] = useState('full_time');

    async function onSubmit(formData: FormData) {
        setLoading(true);
        setError('');

        // Añadir valores de select manually si hiciera falta, pero usamos input hidden
        formData.set('modalidad', modalidad);
        formData.set('tipo_contrato', tipo);

        try {
            const result = await createJobAction(formData);
            if (result.success) {
                router.push('/empresa/anuncios');
                router.refresh();
            } else {
                setError(result.message || 'Error al crear anuncio');
            }
        } catch (e) {
            setError('Error inesperado');
        } finally {
            setLoading(false);
        }
    }

    return (
        <form action={onSubmit} className="space-y-6">
            {error && <div className="p-3 bg-red-500/10 text-red-400 border border-red-500/20 rounded-lg">{error}</div>}

            <div className="space-y-2">
                <Label className="text-slate-300">Título del Puesto</Label>
                <Input name="titulo" placeholder="Ej: Operario de Depósito - Turno Noche" className="bg-slate-900 border-white/10 text-white" required />
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label className="text-slate-300">Ubicación</Label>
                    <Input name="ubicacion" placeholder="Ej: Parque Industrial Pilar" className="bg-slate-900 border-white/10 text-white" required />
                </div>
                <div className="space-y-2">
                    <Label className="text-slate-300">Departamento / Área</Label>
                    <Input name="departamento" placeholder="Ej: Logística" className="bg-slate-900 border-white/10 text-white" />
                </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                    <Label className="text-slate-300">Modalidad</Label>
                    <Select value={modalidad} onValueChange={setModalidad}>
                        <SelectTrigger className="bg-slate-900 border-white/10 text-white">
                            <SelectValue placeholder="Seleccionar" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="presencial">Presencial</SelectItem>
                            <SelectItem value="hibrido">Híbrido</SelectItem>
                            <SelectItem value="remoto">Remoto</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <Label className="text-slate-300">Tipo de Jornada</Label>
                    <Select value={tipo} onValueChange={setTipo}>
                        <SelectTrigger className="bg-slate-900 border-white/10 text-white">
                            <SelectValue placeholder="Seleccionar" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="full_time">Full Time</SelectItem>
                            <SelectItem value="part_time">Part Time</SelectItem>
                            <SelectItem value="eventual">Eventual / Temporada</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="space-y-2">
                <Label className="text-slate-300">Rango Salarial (Opcional)</Label>
                <Input name="rango_salarial" placeholder="Ej: $800.000 - $950.000 Bruto" className="bg-slate-900 border-white/10 text-white" />
                <p className="text-[10px] text-slate-500">Visible para candidatos. Aumenta la conversión.</p>
            </div>

            <div className="space-y-2">
                <Label className="text-slate-300">Descripción del Puesto</Label>
                <Textarea name="descripcion" placeholder="Responsabilidades, requisitos, beneficios..." className="bg-slate-900 border-white/10 text-white h-48" required />
            </div>

            <Button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold" disabled={loading}>
                {loading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : 'Publicar Aviso'}
            </Button>
        </form>
    )
}
