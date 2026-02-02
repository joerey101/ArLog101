import Link from "next/link";
import { Button } from "@/components/ui/button";
import { ArrowRight, Sparkles, Building2 } from "lucide-react";

export function CtaSection() {
    return (
        <section className="relative py-24 overflow-hidden bg-slate-50 border-y border-slate-100">
            {/* Background elements */}
            <div className="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-100/30 rounded-full blur-[120px] -z-10 pointer-events-none" />
            <div className="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-100/30 rounded-full blur-[100px] -z-10 pointer-events-none" />

            <div className="w-full">
                {/* Statistics Block - Full Width container but centered content */}
                <div className="max-w-7xl mx-auto px-6">
                    <div className="grid grid-cols-3 gap-8 text-center border-b border-slate-200 pb-20 mb-20">
                        <div>
                            <div className="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter">128</div>
                            <div className="text-[10px] font-bold tracking-[0.2em] text-blue-600 mt-2 uppercase">Oportunidades</div>
                        </div>
                        <div>
                            <div className="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter">840</div>
                            <div className="text-[10px] font-bold tracking-[0.2em] text-blue-600 mt-2 uppercase">Talentos</div>
                        </div>
                        <div>
                            <div className="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter">42</div>
                            <div className="text-[10px] font-bold tracking-[0.2em] text-blue-600 mt-2 uppercase">Empresas</div>
                        </div>
                    </div>
                </div>

                {/* Main CTA - REALLY Full Width */}
                <div className="w-full bg-blue-600 py-20 md:py-32 relative overflow-hidden">
                    {/* Decorative pattern */}
                    <div className="absolute inset-0 opacity-10 pointer-events-none">
                        <svg height="100%" preserveAspectRatio="none" viewBox="0 0 800 400" width="100%" xmlns="http://www.w3.org/2000/svg">
                            <path d="M600 0 L800 0 L800 400 L400 400 Z" fill="white" />
                            <circle cx="700" cy="100" fill="white" r="50" />
                            <circle cx="200" cy="300" fill="none" r="150" stroke="white" strokeWidth="2" />
                        </svg>
                    </div>

                    <div className="max-w-7xl mx-auto px-6 relative z-10">
                        <div className="flex flex-col lg:flex-row items-center justify-between gap-12 text-center lg:text-left">
                            <div className="space-y-6 lg:w-3/5">
                                <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-white text-sm font-medium">
                                    <Sparkles className="w-4 h-4" />
                                    <span>Liderando la industria</span>
                                </div>
                                <h2 className="text-4xl md:text-7xl font-black text-white leading-[1.1] tracking-tight">
                                    IMPULSÁ TU CARRERA EN EL SECTOR QUE MUEVE AL PAÍS
                                </h2>
                                <p className="text-xl md:text-2xl text-white/90 max-w-2xl font-medium leading-relaxed">
                                    Accedé a las mejores vacantes, compará sueldos promedio y postulate en las empresas líderes.
                                </p>
                            </div>

                            <div className="flex flex-col gap-6 items-center lg:items-end">
                                <Link href="/login">
                                    <Button size="lg" className="h-20 px-12 rounded-2xl bg-white text-blue-600 hover:bg-zinc-100 font-black text-xl shadow-2xl transition-all hover:scale-105 active:scale-95">
                                        REGISTRATE GRATIS
                                    </Button>
                                </Link>
                                <div className="flex items-center gap-2 text-white/70 text-sm font-bold bg-blue-700/50 px-4 py-2 rounded-xl border border-white/10 backdrop-blur-sm">
                                    <div className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                                    <span>84 nuevas vacantes hoy</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Secondary Access (Company Section) - Full Width Row */}
                <div className="max-w-7xl mx-auto px-6 mt-20 pb-10">
                    <div className="flex flex-col md:flex-row items-center justify-between gap-8 p-12 bg-white border border-slate-100 rounded-[32px] shadow-sm">
                        <div className="flex items-center gap-6">
                            <div className="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100">
                                <Building2 className="w-8 h-8" />
                            </div>
                            <div className="text-left">
                                <h3 className="text-2xl font-bold text-slate-900">¿Sos una empresa?</h3>
                                <p className="text-slate-500">Gestioná tus publicaciones y encontrá el talento logístico ideal.</p>
                            </div>
                        </div>
                        <div className="flex flex-wrap gap-4">
                            <Link href="/login">
                                <Button className="h-12 px-8 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                                    Publicar Aviso
                                </Button>
                            </Link>
                            <Link href="/dashboard">
                                <Button variant="outline" className="h-12 px-8 rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 font-bold">
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
