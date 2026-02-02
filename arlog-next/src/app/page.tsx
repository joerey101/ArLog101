import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Search, MapPin, ArrowRight, Truck, Package, Monitor, Wrench } from "lucide-react";
import { Rol, EstadoAnuncio } from "@prisma/client";
import Link from "next/link";
import prisma from "@/lib/prisma";
import { Footer } from "@/components/shared/footer";
import { CtaSection } from "@/components/shared/cta-section";

export default async function Home() {
  // Fetch dynamic stats directly from DB (Server Component power!)
  const stats = {
    anuncios: await prisma.anuncio.count({ where: { estado: EstadoAnuncio.ACTIVO } }).catch(() => 0),
    candidatos: await prisma.usuario.count({ where: { rol: Rol.CANDIDATO } }).catch(() => 0),
    empresas: await prisma.usuario.count({ where: { rol: Rol.EMPRESA } }).catch(() => 0),
  };

  return (
    <main className="min-h-screen bg-slate-950 text-white selection:bg-blue-500/30">
      {/* Navbar Placeholder (To be componentized) */}
      <nav className="fixed top-0 w-full z-50 border-b border-white/10 bg-slate-950/50 backdrop-blur-md">
        <div className="container mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center font-bold text-slate-950">A</div>
            <span className="font-bold text-xl tracking-tight">ArLog<span className="text-blue-400">Jobs</span></span>
          </div>
          <div className="flex gap-4">
            <Link href="/login">
              <Button variant="ghost" className="text-slate-300 hover:text-white hover:bg-white/5">Soy Empresa</Button>
            </Link>
            <Link href="/login">
              <Button className="bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-full">
                Ingresar
              </Button>
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative pt-32 pb-20 md:pt-48 md:pb-32 px-6 flex flex-col items-center text-center overflow-hidden">
        {/* Background Gradients */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-blue-500/20 rounded-[100%] blur-[120px] -z-10 pointer-events-none" />
        <div className="absolute bottom-0 right-0 w-[800px] h-[600px] bg-sky-500/10 rounded-[100%] blur-[120px] -z-10 pointer-events-none" />

        <Badge variant="outline" className="mb-6 px-4 py-1 border-blue-500/30 bg-blue-500/10 text-blue-400 uppercase tracking-widest text-xs font-bold animate-fade-in">
          v2.0 Next Gen
        </Badge>

        <h1 className="text-5xl md:text-7xl font-bold mb-6 leading-tight max-w-4xl tracking-tight">
          El Hub del Talento <br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-sky-400">
            Logístico & Operativo.
          </span>
        </h1>

        <p className="text-slate-400 text-lg md:text-xl max-w-2xl mb-10 leading-relaxed">
          Conectamos a los mejores profesionales con las empresas líderes del sector logístico en Argentina. Rápido, simple y efectivo.
        </p>

        {/* Search Bar */}
        <div className="w-full max-w-3xl p-2 bg-white/5 border border-white/10 rounded-2xl md:rounded-full backdrop-blur-xl shadow-2xl shadow-blue-500/10 flex flex-col md:flex-row gap-2">
          <div className="flex-1 relative group">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-400 transition-colors h-5 w-5" />
            <Input
              placeholder="¿Qué puesto buscas? (Ej: Clarkista)"
              className="h-14 pl-12 bg-transparent border-transparent focus-visible:ring-0 text-white placeholder:text-slate-500 text-base"
            />
          </div>
          <div className="w-px h-8 bg-white/10 hidden md:block self-center" />
          <div className="flex-1 relative group">
            <MapPin className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-sky-400 transition-colors h-5 w-5" />
            <Input
              placeholder="Ubicación (Ej: Pilar)"
              className="h-14 pl-12 bg-transparent border-transparent focus-visible:ring-0 text-white placeholder:text-slate-500 text-base"
            />
          </div>
          <Link href="/empleos" className="w-full md:w-auto">
            <Button size="lg" className="w-full h-14 px-8 rounded-xl md:rounded-full bg-gradient-to-r from-blue-500 to-sky-500 hover:from-blue-400 hover:to-sky-400 text-white font-bold text-base shadow-lg shadow-blue-500/20">
              Buscar Empleo
            </Button>
          </Link>
        </div>
      </section>

      {/* Categories */}
      <section className="container mx-auto px-6 mb-24">
        <p className="text-slate-500 text-sm font-bold uppercase tracking-widest mb-6 text-center md:text-left">Categorías Populares</p>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          {[
            { id: 'transporte', name: 'Transporte', sub: 'Choferes, Reparto', icon: Truck, color: 'text-blue-400', bg: 'bg-blue-500/10' },
            { id: 'deposito', name: 'Depósito', sub: 'Carga, Clarkistas', icon: Package, color: 'text-sky-400', bg: 'bg-sky-500/10' },
            { id: 'admin', name: 'Admin', sub: 'Analistas, RRHH', icon: Monitor, color: 'text-purple-400', bg: 'bg-purple-500/10' },
            { id: 'tecnico', name: 'Técnico', sub: 'Mecánicos, Mant.', icon: Wrench, color: 'text-orange-400', bg: 'bg-orange-500/10' },
          ].map((cat) => (
            <Link key={cat.id} href={`/empleos?q=${cat.name}`} className="group relative">
              <div className="absolute inset-0 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/0 group-hover:via-blue-500/10 opacity-0 group-hover:opacity-100 transition duration-500 rounded-xl" />
              <Card className="bg-white/5 border-white/10 hover:border-blue-500/30 transition-all duration-300">
                <CardContent className="p-5 flex items-center gap-4">
                  <div className={`p-3 rounded-lg ${cat.bg} ${cat.color} group-hover:scale-110 transition-transform duration-300`}>
                    <cat.icon size={24} />
                  </div>
                  <div>
                    <h3 className="font-bold text-lg text-white group-hover:text-blue-300 transition-colors">{cat.name}</h3>
                    <p className="text-slate-500 text-xs">{cat.sub}</p>
                  </div>
                  <ArrowRight className="ml-auto text-slate-600 group-hover:text-blue-400 group-hover:translate-x-1 transition-all" size={16} />
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </section>

      {/* Stats */}
      <section className="border-t border-white/5 py-12 bg-white/[0.02]">
        <div className="container mx-auto px-6 grid grid-cols-2 md:grid-cols-3 gap-8 text-center">
          <div className="bg-slate-900/50 backdrop-blur border border-white/5 rounded-2xl p-6">
            <div className="text-4xl font-bold text-white mb-2">{stats.anuncios}</div>
            <div className="text-xs text-slate-500 uppercase font-bold tracking-widest">Ofertas Activas</div>
          </div>
          <div className="bg-slate-900/50 backdrop-blur border border-white/5 rounded-2xl p-6">
            <div className="text-4xl font-bold text-blue-400 mb-2">{stats.candidatos}</div>
            <div className="text-xs text-slate-500 uppercase font-bold tracking-widest">Candidatos</div>
          </div>
          <div className="hidden md:block bg-slate-900/50 backdrop-blur border border-white/5 rounded-2xl p-6">
            <div className="text-4xl font-bold text-sky-400 mb-2">{stats.empresas}</div>
            <div className="text-xs text-slate-500 uppercase font-bold tracking-widest">Empresas</div>
          </div>
        </div>
      </section>

      <CtaSection />
      <Footer />
    </main>
  );
}

