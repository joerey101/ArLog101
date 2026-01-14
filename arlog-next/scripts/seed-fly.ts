
import { PrismaClient, Rol } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
    const email = 'fly@fly.com';
    // Hash compatible $2a$
    const password = await bcrypt.hash('123456', 10);

    const user = await prisma.usuario.upsert({
        where: { email },
        update: {},
        create: {
            email,
            password_hash: password,
            rol: Rol.EMPRESA,
            fecha_registro: new Date(),
            perfilEmpresa: {
                create: {
                    razon_social: 'Fly Logística',
                    rubro: 'Logística y Transporte',
                    descripcion: 'Empresa líder en logística.',
                    ubicacion: 'Buenos Aires'
                }
            }
        }
    });

    console.log(`✅ Usuario creado: ${user.email} (Rol: ${user.rol})`);
}

main()
    .catch(e => {
        console.error(e);
        process.exit(1);
    })
    .finally(async () => {
        await prisma.$disconnect();
    });
