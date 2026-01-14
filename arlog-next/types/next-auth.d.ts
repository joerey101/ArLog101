import NextAuth, { DefaultSession } from "next-auth"

declare module "next-auth" {
    /**
     * Returned by `useSession`, `getSession` and received as a prop on the `SessionProvider` React Context
     */
    interface Session {
        user: {
            id: string
            rol: string
        } & DefaultSession["user"]
    }

    interface User {
        id: string
        rol: string
    }
}

declare module "next-auth/jwt" {
    interface JWT {
        id: string
        rol: string
    }
}
