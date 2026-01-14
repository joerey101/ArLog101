
import { getServerSession } from "next-auth";
import { authOptions } from "../../../api/auth/[...nextauth]/route";
import { redirect } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { CreateJobForm } from "./create-job-form";

export default async function NewJobPage() {
    const session = await getServerSession(authOptions);
    if (!session) redirect('/login');

    return (
        <div className="max-w-3xl mx-auto">
            <h1 className="text-3xl font-bold text-white mb-6">Nueva Búsqueda Laboral</h1>

            <Card className="bg-slate-900 border-white/10">
                <CardHeader>
                    <CardTitle className="text-white">Detalles del Empleo</CardTitle>
                    <CardDescription>Completa la información para atraer a los mejores talentos.</CardDescription>
                </CardHeader>
                <CardContent>
                    <CreateJobForm userId={session.user.id} />
                </CardContent>
            </Card>
        </div>
    );
}
