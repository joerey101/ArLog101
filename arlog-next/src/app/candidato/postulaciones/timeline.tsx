import { Check, Clock, X } from "lucide-react";
import { cn } from "@/lib/utils";

interface TimelineProps {
    status: string; // 'nuevo' | 'visto' | 'entrevista' | 'finalista' | 'contratado' | 'descartado'
}

const STEPS = [
    { id: 'nuevo', label: 'Enviada' },
    { id: 'visto', label: 'Visto' },
    { id: 'entrevista', label: 'Entrevista' },
    { id: 'finalista', label: 'Finalista' },
    { id: 'contratado', label: 'Oferta' },
];

export function ApplicationTimeline({ status }: TimelineProps) {
    const isRejected = status === 'descartado';

    // Si está descartado, mostramos una UI especial o nos quedamos en el último paso conocido?
    // Por simplicidad, si es rechazado, mostramos una barra roja al final.

    // Encontrar índice del paso actual
    let currentStepIndex = STEPS.findIndex(s => s.id === status);

    // Si no matchea exacto (ej. 'descartado' o estados legacy), manejamos defaults
    if (status === 'descartado') currentStepIndex = -1; // Special Handling
    if (currentStepIndex === -1 && !isRejected) currentStepIndex = 0; // Default to first

    return (
        <div className="w-full mt-4">
            {isRejected ? (
                <div className="flex items-center gap-3 text-red-400 bg-red-950/20 p-3 rounded-lg border border-red-900/50">
                    <X className="w-5 h-5" />
                    <span className="font-medium">Tu postulación ha sido descartada para este puesto.</span>
                </div>
            ) : (
                <div className="relative flex justify-between">
                    {/* Linea de fondo */}
                    <div className="absolute top-[14px] left-0 w-full h-[2px] bg-slate-800 -z-10" />

                    {/* Pasos */}
                    {STEPS.map((step, idx) => {
                        const isCompleted = idx <= currentStepIndex;
                        const isCurrent = idx === currentStepIndex;

                        return (
                            <div key={step.id} className="flex flex-col items-center gap-2">
                                <div
                                    className={cn(
                                        "w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors duration-300",
                                        isCompleted
                                            ? "bg-emerald-950 border-emerald-500 text-emerald-500"
                                            : "bg-slate-950 border-slate-700 text-slate-700",
                                        isCurrent && "ring-2 ring-emerald-500/30 scale-110 bg-emerald-500 text-black border-emerald-500"
                                    )}
                                >
                                    {isCompleted ? <Check size={14} /> : <div className="w-2 h-2 rounded-full bg-slate-700" />}
                                </div>
                                <span className={cn(
                                    "text-xs font-medium transition-colors duration-300",
                                    isCompleted ? "text-emerald-400" : "text-slate-600",
                                    isCurrent && "text-white"
                                )}>
                                    {step.label}
                                </span>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
