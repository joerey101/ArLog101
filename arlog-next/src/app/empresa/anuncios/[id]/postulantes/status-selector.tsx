"use client";

import { useState } from "react";
import { EstadoPostulacion } from "@prisma/client";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { updateApplicationStatus } from "./actions";
import { Loader2 } from "lucide-react";

interface StatusSelectorProps {
    postulacionId: number;
    currentStatus: EstadoPostulacion;
    path: string;
}

const statusMap: Record<string, string> = {
    [EstadoPostulacion.NUEVO]: "Nuevo",
    [EstadoPostulacion.VISTO]: "Visto",
    [EstadoPostulacion.ENTREVISTA]: "En Entrevista",
    [EstadoPostulacion.FINALISTA]: "Finalista",
    [EstadoPostulacion.DESCARTADO]: "Descartado",
    [EstadoPostulacion.CONTRATADO]: "Contratado",
};

export function StatusSelector({ postulacionId, currentStatus, path }: StatusSelectorProps) {
    const [status, setStatus] = useState(currentStatus);
    const [loading, setLoading] = useState(false);

    const handleChange = async (value: EstadoPostulacion) => {
        setLoading(true);
        try {
            // Optimistic update
            setStatus(value);
            await updateApplicationStatus(postulacionId, value, path);
        } catch (error) {
            console.error("Failed to update status", error);
            // Revert on error
            setStatus(currentStatus);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex items-center gap-2">
            <Select disabled={loading} value={status} onValueChange={handleChange}>
                <SelectTrigger className="w-[140px] h-8 text-xs bg-slate-900 border-slate-700 text-slate-300">
                    <SelectValue>
                        <div className="flex items-center gap-2">
                            {loading && <Loader2 className="h-3 w-3 animate-spin" />}
                            {!loading && statusMap[status]}
                        </div>
                    </SelectValue>
                </SelectTrigger>
                <SelectContent className="bg-slate-900 border-slate-700 text-slate-300">
                    {Object.keys(EstadoPostulacion).map((key) => (
                        <SelectItem key={key} value={key} className="text-xs hover:bg-slate-800 cursor-pointer">
                            {statusMap[key]}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
