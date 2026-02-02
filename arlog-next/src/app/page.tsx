import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, MapPin, Truck, Package, Monitor, Wrench } from "lucide-react";
import { Rol, EstadoAnuncio } from "@prisma/client";
import Link from "next/link";
import prisma from "@/lib/prisma";
import { Footer } from "@/components/shared/footer";
import { CtaSection } from "@/components/shared/cta-section";
import { Badge } from "@/components/ui/badge";

export default async function Home() {
  // Fetch dynamic stats directly from DB (Server Component power!)
  const stats = {
    anuncios: await prisma.anuncio.count({ where: { estado: EstadoAnuncio.ACTIVO } }).catch(() => 0),
    candidatos: await prisma.usuario.count({ where: { rol: Rol.CANDIDATO } }).catch(() => 0),
    empresas: await prisma.usuario.count({ where: { rol: Rol.EMPRESA } }).catch(() => 0),
  };

  return (
    <main className="min-h-screen bg-white text-slate-900 selection:bg-blue-100">
      {/* Navbar Placeholder */}
      <nav className="fixed top-0 w-full z-50 border-b border-slate-100 bg-white/90 backdrop-blur-md">
        <div className="container mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-white text-sm">A</div>
            <span className="font-bold text-xl tracking-tight text-slate-900">ArLog<span className="text-blue-600">Jobs</span></span>
          </div>
          <div className="flex gap-4">
            <Link href="/login">
              <Button variant="ghost" className="text-slate-600 hover:text-blue-600 font-bold">Soy Empresa</Button>
            </Link>
            <Link href="/login">
              <Button className="bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-full px-6">
                Ingresar
              </Button>
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative pt-32 pb-20 md:pt-56 md:pb-40 px-6 flex flex-col items-center text-center overflow-hidden">
        <Badge variant="secondary" className="mb-8 px-4 py-1.5 bg-blue-50 text-blue-600 border-none uppercase tracking-widest text-[10px] font-black">
          v2.0 Next Gen
        </Badge>

        <h1 className="text-6xl md:text-[100px] font-black mb-8 leading-[0.9] max-w-5xl tracking-tighter text-slate-900 uppercase">
          El Hub del Talento <br />
          <span className="text-blue-600">
            Logístico & Operativo.
          </span>
        </h1>

        <p className="text-slate-500 text-xl md:text-3xl max-w-3xl mb-14 leading-relaxed font-medium">
          Conectamos a los mejores profesionales con las empresas líderes del sector logístico en Argentina.
        </p>

        {/* Search Bar - Elevated Shadow instead of bg */}
        <div className="w-full max-w-4xl p-2 bg-white border border-slate-100 rounded-[28px] md:rounded-full shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] flex flex-col md:flex-row gap-2">
          <div className="flex-1 relative group">
            <Search className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-600 transition-colors h-6 w-6" />
            <Input
              placeholder="¿Qué puesto buscas? (Ej: Clarkista)"
              className="h-16 pl-14 bg-transparent border-transparent focus-visible:ring-0 text-slate-900 placeholder:text-slate-300 text-lg font-medium"
            />
          </div>
          <div className="w-px h-10 bg-slate-100 hidden md:block self-center" />
          <div className="flex-1 relative group">
            <MapPin className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-blue-600 transition-colors h-6 w-6" />
            <Input
              placeholder="Ubicación (Ej: Pilar)"
              className="h-16 pl-14 bg-transparent border-transparent focus-visible:ring-0 text-slate-900 placeholder:text-slate-300 text-lg font-medium"
            />
          </div>
          <Link href="/empleos" className="w-full md:w-auto">
            <Button size="lg" className="w-full h-16 px-12 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-black text-lg">
              BUSCAR AHORA
            </Button>
          </Link>
        </div>
      </section>

      {/* Categories - Cleaner, more modern layout */}
      <section className="container mx-auto px-6 mb-32">
        <div className="flex flex-col md:flex-row items-center justify-between mb-12 gap-4">
          <h2 className="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Categorías Populares</h2>
          <div className="h-px flex-1 bg-slate-100 hidden md:block mx-10" />
          <Link href="/empleos" className="text-blue-600 font-bold hover:underline text-sm uppercase tracking-widest">
            Ver Todas →
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {[
            { id: 'transporte', name: 'Transporte', sub: 'Choferes, Reparto', icon: Truck, color: 'text-blue-600' },
            { id: 'deposito', name: 'Depósito', sub: 'Carga, Clarkistas', icon: Package, color: 'text-indigo-600' },
            { id: 'admin', name: 'Admin', sub: 'Analistas, RRHH', icon: Monitor, color: 'text-purple-600' },
            { id: 'tecnico', name: 'Técnico', sub: 'Mecánicos, Mant.', icon: Wrench, color: 'text-orange-600' },
          ].map((cat) => (
            <Link key={cat.id} href={`/empleos?q=${cat.name}`} className="group p-8 rounded-3xl border border-slate-50 hover:bg-slate-50 transition-all duration-300">
              <div className={`w-14 h-14 rounded-2xl bg-white border border-slate-100 ${cat.color} flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition-transform`}>
                <cat.icon size={28} />
              </div>
              <h3 className="font-black text-xl text-slate-900 mb-1">{cat.name}</h3>
              <p className="text-slate-400 text-sm font-medium">{cat.sub}</p>
            </Link>
          ))}
        </div>
      </section>

      {/* Stats - Pure White */}
      <section className="border-t border-slate-100 py-24 bg-white">
        <div className="container mx-auto px-6 grid grid-cols-2 md:grid-cols-3 gap-12 text-center">
          <div className="space-y-4">
            <div className="text-6xl font-black text-slate-900">{stats.anuncios}</div>
            <div className="text-[10px] text-slate-400 uppercase font-black tracking-[0.3em]">Ofertas Activas</div>
          </div>
          <div className="space-y-4">
            <div className="text-6xl font-black text-blue-600">{stats.candidatos}</div>
            <div className="text-[10px] text-slate-400 uppercase font-black tracking-[0.3em]">Candidatos</div>
          </div>
          <div className="hidden md:block space-y-4">
            <div className="text-6xl font-black text-slate-900">{stats.empresas}</div>
            <div className="text-[10px] text-slate-400 uppercase font-black tracking-[0.3em]">Empresas</div>
          </div>
        </div>
      </section>

      <CtaSection />
      <Footer />
    </main>
  );
}
