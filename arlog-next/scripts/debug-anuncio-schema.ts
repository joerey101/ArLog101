
const { PrismaClient } = require('@prisma/client');
const prisma = new PrismaClient();

async function main() {
    console.log('🔍 Inspeccionando tabla ANUNCIOS (Plural)...');

    try {
        // Usamos el nombre correcto de la tabla ahora
        const anuncios = await prisma.$queryRaw`SELECT * FROM anuncios LIMIT 1`;

        if (anuncios.length === 0) {
            console.log('⚠️ La tabla "anuncios" existe pero está vacía.');
            // Probamos describir la tabla para ver las columnas aunque esté vacía
            const columnas = await prisma.$queryRaw`DESCRIBE anuncios`;
            console.table(columnas);
        } else {
            console.log('✅ Anuncio encontrado. Estructura:');
            console.log(Object.keys(anuncios[0])); // Solo listamos las keys para ver nombres de columnas
            console.log('Ejemplo completo:', anuncios[0]);
        }

    } catch (error) {
        console.error('❌ Error consultando:', error.message);
    }
}

main()
    .catch((e) => console.error(e))
    .finally(async () => await prisma.$disconnect());
