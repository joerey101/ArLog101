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
    <main className="min-h-screen bg-white text-slate-900 selection:bg-blue-100">
      {/* Navbar Placeholder */}
      <nav className="fixed top-0 w-full z-50 border-b border-slate-100 bg-white/80 backdrop-blur-md">
        <div className="container mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-white">A</div>
            <span className="font-bold text-xl tracking-tight text-slate-900">ArLog<span className="text-blue-600">Jobs</span></span>
          </div>
          <div className="flex gap-4">
            <Link href="/login">
              <Button variant="ghost" className="text-slate-600 hover:text-blue-600 hover:bg-blue-50">Soy Empresa</Button>
            </Link>
            <Link href="/login">
              <Button className="bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-full">
                Ingresar
              </Button>
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative pt-32 pb-20 md:pt-48 md:pb-32 px-6 flex flex-col items-center text-center overflow-hidden">
        {/* Subtle Background Gradients */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-blue-50/50 rounded-[100%] blur-[120px] -z-10 pointer-events-none" />

        <Badge variant="secondary" className="mb-6 px-4 py-1 bg-blue-50 text-blue-600 border-none uppercase tracking-widest text-xs font-bold">
          v2.0 Next Gen
        </Badge>

        <h1 className="text-5xl md:text-7xl font-extrabold mb-6 leading-tight max-w-4xl tracking-tight text-slate-900">
          El Hub del Talento <br />
          <span className="text-blue-600">
            Logístico & Operativo.
          </span>
        </h1>

        <p className="text-slate-500 text-lg md:text-xl max-w-2xl mb-10 leading-relaxed">
          Conectamos a los mejores profesionales con las empresas líderes del sector logístico en Argentina. Rápido, simple y efectivo.
        </p>

        {/* Search Bar */}
        <div className="w-full max-w-3xl p-2 bg-white border border-slate-200 rounded-2xl md:rounded-full shadow-xl shadow-blue-900/5 flex flex-col md:flex-row gap-2">
          <div className="flex-1 relative group">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors h-5 w-5" />
            <Input
              placeholder="¿Qué puesto buscas? (Ej: Clarkista)"
              className="h-14 pl-12 bg-transparent border-transparent focus-visible:ring-0 text-slate-900 placeholder:text-slate-400 text-base"
            />
          </div>
          <div className="w-px h-8 bg-slate-100 hidden md:block self-center" />
          <div className="flex-1 relative group">
            <MapPin className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors h-5 w-5" />
            <Input
              placeholder="Ubicación (Ej: Pilar)"
              className="h-14 pl-12 bg-transparent border-transparent focus-visible:ring-0 text-slate-900 placeholder:text-slate-400 text-base"
            />
          </div>
          <Link href="/empleos" className="w-full md:w-auto">
            <Button size="lg" className="w-full h-14 px-8 rounded-xl md:rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-base shadow-lg shadow-blue-500/20">
              Buscar Empleo
            </Button>
          </Link>
        </div>
      </section>

      {/* Categories */}
      <section className="container mx-auto px-6 mb-24">
        <p className="text-slate-400 text-sm font-bold uppercase tracking-widest mb-6 text-center md:text-left">Categorías Populares</p>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          {[
            { id: 'transporte', name: 'Transporte', sub: 'Choferes, Reparto', icon: Truck, color: 'text-blue-600', bg: 'bg-blue-50' },
            { id: 'deposito', name: 'Depósito', sub: 'Carga, Clarkistas', icon: Package, color: 'text-indigo-600', bg: 'bg-indigo-50' },
            { id: 'admin', name: 'Admin', sub: 'Analistas, RRHH', icon: Monitor, color: 'text-purple-600', bg: 'bg-purple-50' },
            { id: 'tecnico', name: 'Técnico', sub: 'Mecánicos, Mant.', icon: Wrench, color: 'text-orange-600', bg: 'bg-orange-50' },
          ].map((cat) => (
            <Link key={cat.id} href={`/empleos?q=${cat.name}`} className="group relative">
              <Card className="bg-white border-slate-100 hover:border-blue-200 hover:shadow-lg transition-all duration-300">
                <CardContent className="p-5 flex items-center gap-4">
                  <div className={`p-3 rounded-lg ${cat.bg} ${cat.color} group-hover:scale-110 transition-transform duration-300`}>
                    <cat.icon size={24} />
                  </div>
                  <div>
                    <h3 className="font-bold text-lg text-slate-900 group-hover:text-blue-600 transition-colors">{cat.name}</h3>
                    <p className="text-slate-500 text-xs">{cat.sub}</p>
                  </div>
                  <ArrowRight className="ml-auto text-slate-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-all" size={16} />
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      </section>

      {/* Stats */}
      <section className="border-t border-slate-50 py-16 bg-slate-50/50">
        <div className="container mx-auto px-6 grid grid-cols-2 md:grid-cols-3 gap-8 text-center">
          <div className="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
            <div className="text-4xl font-extrabold text-slate-900 mb-2">{stats.anuncios}</div>
            <div className="text-xs text-slate-400 uppercase font-bold tracking-widest">Ofertas Activas</div>
          </div>
          <div className="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
            <div className="text-4xl font-extrabold text-blue-600 mb-2">{stats.candidatos}</div>
            <div className="text-xs text-slate-400 uppercase font-bold tracking-widest">Candidatos</div>
          </div>
          <div className="hidden md:block bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
            <div className="text-4xl font-extrabold text-slate-900 mb-2">{stats.empresas}</div>
            <div className="text-xs text-slate-400 uppercase font-bold tracking-widest">Empresas</div>
          </div>
        </div>
      </section>

      <CtaSection />
      <Footer />
    </main>
  );
}


