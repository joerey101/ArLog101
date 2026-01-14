
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
    console.log('🔍 Inspeccionando tabla POSTULACIONES...');
    try {
        const data = await prisma.$queryRaw`SELECT * FROM postulaciones LIMIT 1`;
        if (data.length > 0) {
            console.log('✅ Postulación de ejemplo:', Object.keys(data[0]));
        } else {
            const cols = await prisma.$queryRaw`DESCRIBE postulaciones`;
            console.table(cols);
        }
    } catch (e) {
        console.error(e.message);
    }

    console.log('\n🔍 Re-Inspeccionando ANUNCIOS (Constraints)...');
    try {
        const cols = await prisma.$queryRaw`DESCRIBE anuncios`;
        console.table(cols);
    } catch (e) {
        console.error(e.message);
    }
}

main().finally(() => prisma.$disconnect());
