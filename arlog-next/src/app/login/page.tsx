'use client';

import { useState } from 'react';
import { signIn } from 'next-auth/react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { AlertCircle, Loader2 } from 'lucide-react';

export default function LoginPage() {
    const router = useRouter();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const result = await signIn('credentials', {
                redirect: false,
                email,
                password,
            });

            if (result?.error) {
                setError("Credenciales inválidas. Por favor intente nuevamente.");
                setLoading(false);
            } else {
                // Redirección inteligente basada en rol lo haremos en el dashboard
                // Por ahora al dashboard genérico
                router.push('/dashboard');
                router.refresh();
            }
        } catch (err) {
            setError("Ocurrió un error inesperado.");
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-slate-950 p-4 relative overflow-hidden">
            {/* Background Effects */}
            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none" />

            <Card className="w-full max-w-md bg-white/5 border-white/10 backdrop-blur-xl z-10">
                <CardHeader className="text-center">
                    <div className="mx-auto mb-4 w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center font-bold text-slate-950 text-xl">A</div>
                    <CardTitle className="text-2xl text-white">Bienvenido</CardTitle>
                    <CardDescription className="text-slate-400">Ingresa a tu cuenta de ArLog Jobs</CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-4">
                        {error && (
                            <div className="p-3 rounded bg-red-500/10 border border-red-500/20 flex items-center gap-2 text-red-400 text-sm">
                                <AlertCircle size={16} />
                                {error}
                            </div>
                        )}

                        <div className="space-y-2">
                            <label className="text-sm font-medium text-slate-300">Email</label>
                            <Input
                                type="email"
                                placeholder="usuario@ejemplo.com"
                                className="bg-slate-900/50 border-white/10 text-white placeholder:text-slate-500"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <div className="flex justify-between items-center">
                                <label className="text-sm font-medium text-slate-300">Contraseña</label>
                                <Link href="#" className="text-xs text-emerald-400 hover:text-emerald-300">¿Olvidaste tu contraseña?</Link>
                            </div>
                            <Input
                                type="password"
                                className="bg-slate-900/50 border-white/10 text-white"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                required
                            />
                        </div>

                        <Button type="submit" className="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold" disabled={loading}>
                            {loading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : 'Ingresar'}
                        </Button>
                    </form>
                </CardContent>
                <CardFooter className="justify-center border-t border-white/5 pt-6">
                    <p className="text-sm text-slate-400">
                        ¿No tienes cuenta? <Link href="/registro" className="text-emerald-400 hover:text-emerald-300 font-bold">Regístrate</Link>
                    </p>
                </CardFooter>
            </Card>
        </div>
    );
}
