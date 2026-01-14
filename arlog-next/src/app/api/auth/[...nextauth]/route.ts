import NextAuth, { NextAuthOptions } from "next-auth";
import CredentialsProvider from "next-auth/providers/credentials";
import prisma from "@/lib/prisma";
import bcrypt from "bcryptjs";

export const authOptions: NextAuthOptions = {
    providers: [
        CredentialsProvider({
            name: "Credentials",
            credentials: {
                email: { label: "Email", type: "email" },
                password: { label: "Contraseña", type: "password" }
            },
            async authorize(credentials) {
                if (!credentials?.email || !credentials?.password) {
                    throw new Error("Credenciales incompletas");
                }

                // 1. Buscar usuario
                const user = await prisma.usuario.findUnique({
                    where: { email: credentials.email }
                });

                if (!user || !user.password_hash) {
                    throw new Error("Usuario no encontrado");
                }

                // 2. Validar contraseña
                const isValid = await bcrypt.compare(credentials.password, user.password_hash);

                if (!isValid) {
                    throw new Error("Contraseña incorrecta");
                }

                // 3. Retornar objeto usuario (lo que se guardará en el token JWT)
                return {
                    id: user.id.toString(),
                    email: user.email,
                    rol: user.rol,
                    name: user.email.split("@")[0] // Fallback name
                };
            }
        })
    ],
    session: {
        strategy: "jwt"
    },
    callbacks: {
        async jwt({ token, user }) {
            if (user) {
                token.id = user.id;
                token.rol = user.rol;
            }
            return token;
        },
        async session({ session, token }) {
            if (session.user) {
                session.user.id = token.id;
                session.user.rol = token.rol;
            }
            return session;
        }
    },
    pages: {
        signIn: '/login', // Página de login personalizada
    },
    secret: process.env.NEXTAUTH_SECRET,
};

const handler = NextAuth(authOptions);

export { handler as GET, handler as POST };
