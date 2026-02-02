
import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
    const email = 'joerey@gmail.com';
    const passwordToCheck = 'Miami128';

    console.log(`Checking user: ${email}`);

    try {
        const user = await prisma.usuario.findUnique({
            where: { email },
        });

        if (!user) {
            console.log('❌ User NOT FOUND in database.');
        } else {
            console.log(`✅ User FOUND: ${user.email} (ID: ${user.id}, Rol: ${user.rol})`);

            if (user.password_hash) {
                const isMatch = await bcrypt.compare(passwordToCheck, user.password_hash);
                if (isMatch) {
                    console.log('✅ Password MATCHES.');
                } else {
                    console.log('❌ Password DOES NOT MATCH.');
                    // Optional: Update password to match if needed, or just report failure
                }
            } else {
                console.log('❌ User has NO password hash.');
            }
        }
    } catch (e) {
        console.error('Error querying database:', e);
    } finally {
        await prisma.$disconnect();
    }
}

main();
