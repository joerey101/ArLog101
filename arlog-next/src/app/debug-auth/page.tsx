
import prisma from "@/lib/prisma";
import bcrypt from "bcryptjs";

export const dynamic = 'force-dynamic';

export default async function DebugAuthPage() {
    const email = 'fly@fly.com';
    const password = 'Miami128';

    const logs = [];
    logs.push(`Iniciando diagnóstico para: ${email}`);

    try {
        const user = await prisma.usuario.findUnique({
            where: { email: email }
        });

        if (!user) {
            logs.push("❌ ERROR: Usuario NO encontrado en la base de datos.");
        } else {
            logs.push("✅ Usuario encontrado.");
            logs.push(`ID: ${user.id}`);
            logs.push(`Rol: '${user.rol}'`);

            const hash = user.password_hash;
            logs.push(`Hash almacenado (primeros 10 chars): ${hash.substring(0, 10)}...`);

            if (!hash) {
                logs.push("❌ ERROR: El usuario no tiene contraseña (hash vacío).");
            } else {
                // Prueba 1: Comparación directa
                const directa = await bcrypt.compare(password, hash);
                logs.push(`Prueba 1 (Directa): ${directa ? "✅ ÉXITO" : "❌ FALLÓ"}`);

                // Prueba 2: Comparación con Fix $2y$ -> $2a$
                let fixHash = hash;
                if (hash.startsWith('$2y$')) {
                    fixHash = hash.replace(/^\$2y\$/, '$2a$');
                    logs.push(`Hash modificado para Node ($2y$ -> $2a$): ${fixHash.substring(0, 10)}...`);

                    const conFix = await bcrypt.compare(password, fixHash);
                    logs.push(`Prueba 2 (Con Fix PHP): ${conFix ? "✅ ÉXITO" : "❌ FALLÓ"}`);
                } else {
                    logs.push("El hash no empieza con $2y$, no se aplicó fix.");
                }
            }
        }

    } catch (error: any) {
        logs.push("❌ ERROR CRÍTICO DE CONEXIÓN O EJECUCIÓN:");
        logs.push(error.message);
    }

    return (
        <div className="min-h-screen bg-black text-green-400 p-8 font-mono text-sm">
            <h1 className="text-xl font-bold text-white mb-4">Diagnóstico de Autenticación</h1>
            <div className="border border-green-900 bg-green-900/10 p-4 rounded">
                {logs.map((log, i) => (
                    <div key={i} className="mb-2 border-b border-green-900/30 pb-1">{log}</div>
                ))}
            </div>
            <p className="mt-8 text-gray-500">Eliminar esta página después de usar.</p>
        </div>
    );
}
