
import { PrismaClient, Rol } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
    const email = 'joerey@gmail.com';
    // Hash compatible $2a$
    const password = await bcrypt.hash('Miami128', 10);

    const user = await prisma.usuario.upsert({
        where: { email },
        update: {},
        create: {
            email,
            password_hash: password,
            rol: Rol.CANDIDATO,
            fecha_registro: new Date(),
            perfilCandidato: {
                create: {
                    nombre: 'Joe',
                    apellido: 'Rey',
                    telefono: '+54 9 11 1234 5678',
                    titulo_cargo: 'Gerente de Logística',
                    ciudad: 'Miami',
                    sobre_mi: 'Experto en operaciones logísticas internacionales.',
                    cv_url: 'https://linkedin.com/in/joerey' // Placeholder
                }
            }
        }
    });

    console.log(`✅ Usuario Candidato creado: ${user.email} (Rol: ${user.rol})`);
}

main()
    .catch(e => {
        console.error(e);
        process.exit(1);
    })
    .finally(async () => {
        await prisma.$disconnect();
    });
