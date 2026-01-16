"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { approveJob, rejectJob } from "./actions";
import { CheckCircle, XCircle, MapPin, Building2, Calendar, DollarSign } from "lucide-react";
import { formatDistanceToNow } from "date-fns";
import { es } from "date-fns/locale";

interface Job {
    id: number;
    titulo: string;
    descripcion: string;
    ubicacion: string | null;
    modalidad: string;
    rango_salarial: string | null;
    fecha_publicacion: Date;
    empresa_perfil: {
        razon_social: string | null;
        logo_url: string | null;
    } | null;
}

export function PendingJobsList({ initialJobs }: { initialJobs: Job[] }) {
    // We can use optimistic updates here for better UX
    // For simplicity v1, we will just rely on server action revalidation redirection

    return (
        <div className="space-y-4">
            {initialJobs.length === 0 ? (
                <div className="text-center py-10 text-slate-400">
                    <p>No hay avisos pendientes de revisión.</p>
                </div>
            ) : (
                initialJobs.map((job) => (
                    <JobCard key={job.id} job={job} />
                ))
            )}
        </div>
    );
}

function JobCard({ job }: { job: Job }) {
    const [loading, setLoading] = useState(false);

    const handleApprove = async () => {
        if (confirm(`¿Aprobar aviso "${job.titulo}"?`)) {
            setLoading(true);
            await approveJob(job.id);
            setLoading(false);
        }
    };

    const handleReject = async () => {
        if (confirm(`¿Rechazar aviso "${job.titulo}"?`)) {
            setLoading(true);
            await rejectJob(job.id);
            setLoading(false);
        }
    };

    return (
        <Card className="bg-slate-900 border-slate-800">
            <CardHeader>
                <div className="flex justify-between items-start">
                    <div>
                        <CardTitle className="text-white text-xl">{job.titulo}</CardTitle>
                        <CardDescription className="text-slate-400 flex items-center gap-2 mt-1">
                            <Building2 className="w-4 h-4" />
                            {job.empresa_perfil?.razon_social || "Empresa Anónima"}
                        </CardDescription>
                    </div>
                    <Badge variant="outline" className="border-yellow-500/50 text-yellow-500 bg-yellow-500/10">
                        Pendiente
                    </Badge>
                </div>
            </CardHeader>
            <CardContent className="text-slate-300 space-y-4">
                <div className="flex flex-wrap gap-4 text-sm">
                    <div className="flex items-center gap-1"><MapPin className="w-4 h-4 text-slate-500" /> {job.ubicacion || "Remoto"}</div>
                    <div className="flex items-center gap-1"><Calendar className="w-4 h-4 text-slate-500" /> {formatDistanceToNow(new Date(job.fecha_publicacion), { addSuffix: true, locale: es })}</div>
                    {job.rango_salarial && <div className="flex items-center gap-1"><DollarSign className="w-4 h-4 text-slate-500" /> {job.rango_salarial}</div>}
                </div>

                <div className="bg-slate-950 p-4 rounded-md text-sm text-slate-400 max-h-32 overflow-y-auto">
                    {job.descripcion}
                </div>
            </CardContent>
            <CardFooter className="justify-end gap-3 border-t border-slate-800 pt-4">
                <Button
                    variant="ghost"
                    className="text-red-400 hover:text-red-300 hover:bg-red-950/30"
                    onClick={handleReject}
                    disabled={loading}
                >
                    <XCircle className="w-4 h-4 mr-2" />
                    Rechazar
                </Button>
                <Button
                    className="bg-emerald-600 hover:bg-emerald-500 text-white"
                    onClick={handleApprove}
                    disabled={loading}
                >
                    <CheckCircle className="w-4 h-4 mr-2" />
                    Aprobar y Publicar
                </Button>
            </CardFooter>
        </Card>
    );
}
