
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
    console.log('🔍 Listando tablas de la base de datos...');
    try {
        const tablas = await prisma.$queryRaw`SHOW TABLES`;
        console.log(tablas);
    } catch (error) {
        console.error('❌ Error:', error.message);
    }
}

main()
    .catch((e) => console.error(e))
    .finally(async () => await prisma.$disconnect());
