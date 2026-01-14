
import { PrismaClient, Rol } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
    const email = 'admin@arlog.com';
    const password = await bcrypt.hash('Admin123', 10);

    const user = await prisma.usuario.upsert({
        where: { email },
        update: {},
        create: {
            email,
            password_hash: password,
            rol: Rol.ADMIN,
            fecha_registro: new Date(),
            // Admins don't strictily need a profile, but if schema requires it, logic might differ. 
            // In current schema, relations are optional? Let's check.
            // Looking at previous schema knowledge: perfilEmpresa and perfilCandidato are separate tables.
            // Usually One-to-One relations are optional on one side.
        }
    });

    console.log(`✅ Usuario ADMIN creado: ${user.email} (Rol: ${user.rol})`);
}

main()
    .catch(e => {
        console.error(e);
        process.exit(1);
    })
    .finally(async () => {
        await prisma.$disconnect();
    });
