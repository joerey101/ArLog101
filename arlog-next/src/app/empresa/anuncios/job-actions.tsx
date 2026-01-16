import { Button } from "@/components/ui/button";
import { Eye } from "lucide-react";
import Link from "next/link";

interface JobActionsProps {
    jobId: number;
}

export function JobActions({ jobId }: JobActionsProps) {
    return (
        <div className="flex justify-start">
            <Link href={`/empleos/${jobId}?from=/empresa/anuncios`}>
                <Button
                    variant="ghost"
                    size="sm"
                    className="text-emerald-400 hover:text-emerald-300 hover:bg-emerald-400/10 font-bold border border-emerald-500/20 px-4"
                >
                    <Eye className="mr-2 h-4 w-4" /> VER ANUNCIO
                </Button>
            </Link>
        </div>
    );
}
