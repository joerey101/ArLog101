import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Sparkles, Building2 } from "lucide-react";

export function CtaSection() {
    return (
        <section className="w-full overflow-hidden bg-white">
            {/* Statistics Block - Full Width container */}
            <div className="w-full bg-slate-50 border-y border-slate-100 py-20 md:py-32">
                <div className="max-w-7xl mx-auto px-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                        <div className="space-y-2">
                            <div className="text-6xl md:text-8xl font-black text-slate-900 tracking-tighter">128</div>
                            <div className="text-xs font-bold tracking-[0.3em] text-blue-600 uppercase">Oportunidades</div>
                        </div>
                        <div className="space-y-2">
                            <div className="text-6xl md:text-8xl font-black text-slate-900 tracking-tighter">840</div>
                            <div className="text-xs font-bold tracking-[0.3em] text-blue-600 uppercase">Talentos</div>
                        </div>
                        <div className="space-y-2">
                            <div className="text-6xl md:text-8xl font-black text-slate-900 tracking-tighter">42</div>
                            <div className="text-xs font-bold tracking-[0.3em] text-blue-600 uppercase">Empresas</div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main CTA - REALLY Full Width Banner */}
            <div className="w-full bg-blue-600 py-24 md:py-40 relative">
                {/* Decorative pattern */}
                <div className="absolute inset-0 opacity-10 pointer-events-none">
                    <svg height="100%" preserveAspectRatio="none" viewBox="0 0 800 400" width="100%" xmlns="http://www.w3.org/2000/svg">
                        <path d="M600 0 L800 0 L800 400 L400 400 Z" fill="white" />
                        <circle cx="700" cy="100" fill="white" r="50" />
                        <circle cx="200" cy="300" fill="none" r="150" stroke="white" strokeWidth="2" />
                    </svg>
                </div>

                <div className="max-w-7xl mx-auto px-6 relative z-10">
                    <div className="flex flex-col lg:flex-row items-center justify-between gap-16 text-center lg:text-left">
                        <div className="space-y-8 lg:w-2/3">
                            <div className="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/10 border border-white/20 text-white text-sm font-bold backdrop-blur-sm">
                                <Sparkles className="w-4 h-4" />
                                <span>LIDERANDO LA INDUSTRIA</span>
                            </div>
                            <h2 className="text-5xl md:text-8xl font-black text-white leading-[1] tracking-tight">
                                IMPULSÁ TU CARRERA EN EL SECTOR <br className="hidden md:block" /> QUE MUEVE AL PAÍS
                            </h2>
                            <p className="text-xl md:text-3xl text-white/90 max-w-3xl font-medium leading-relaxed">
                                Accedé a las mejores vacantes, compará sueldos promedio y postulate en las empresas líderes.
                            </p>
                        </div>

                        <div className="flex flex-col gap-6 items-center lg:items-end">
                            <Link href="/login">
                                <Button size="lg" className="h-24 px-16 rounded-3xl bg-white text-blue-600 hover:bg-zinc-100 font-black text-2xl shadow-[0_20px_60px_-15px_rgba(255,255,255,0.4)] transition-all hover:scale-105 active:scale-95">
                                    REGISTRATE GRATIS
                                </Button>
                            </Link>
                            <div className="flex items-center gap-3 text-white/80 text-base font-bold bg-blue-700/50 px-6 py-3 rounded-2xl border border-white/10 backdrop-blur-sm">
                                <div className="w-3 h-3 rounded-full bg-green-400 animate-pulse" />
                                <span>84 nuevas vacantes hoy</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Secondary Access (Company Section) - Full Width Section, NO CARD */}
            <div className="w-full bg-white py-24 md:py-32 border-t border-slate-100">
                <div className="max-w-7xl mx-auto px-6">
                    <div className="flex flex-col md:flex-row items-center justify-between gap-12">
                        <div className="flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
                            <div className="w-20 h-20 rounded-3xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                                <Building2 className="w-10 h-10" />
                            </div>
                            <div className="space-y-2">
                                <h3 className="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">¿Sos una empresa?</h3>
                                <p className="text-xl text-slate-500 max-w-md font-medium">Gestioná tus publicaciones y encontrá el talento logístico ideal.</p>
                            </div>
                        </div>
                        <div className="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                            <Link href="/login" className="w-full sm:w-auto">
                                <Button className="w-full h-16 px-10 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-black text-lg shadow-xl shadow-slate-900/10">
                                    Publicar Aviso
                                </Button>
                            </Link>
                            <Link href="/dashboard" className="w-full sm:w-auto">
                                <Button variant="outline" className="w-full h-16 px-10 rounded-2xl border-slate-200 text-slate-600 hover:bg-slate-50 font-black text-lg">
                                    Dashboard
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
