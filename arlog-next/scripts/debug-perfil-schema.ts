
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
    console.log('🔍 Inspeccionando tabla PERFILES_EMPRESAS...');
    try {
        const data = await prisma.$queryRaw`SELECT * FROM perfiles_empresas LIMIT 1`;
        if (data.length > 0) {
            console.log(Object.keys(data[0]));
        } else {
            const cols = await prisma.$queryRaw`DESCRIBE perfiles_empresas`;
            console.table(cols);
        }
    } catch (e) {
        console.error(e.message);
    }
}

main().finally(() => prisma.$disconnect());
