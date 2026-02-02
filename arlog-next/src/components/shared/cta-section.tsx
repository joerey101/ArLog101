import Link from "next/link";
import { Button } from "@/components/ui/button";
import { ArrowRight, Sparkles, Building2 } from "lucide-react";

export function CtaSection() {
    return (
        <section className="relative py-24 px-6 overflow-hidden">
            {/* Background with glass effect */}
            <div className="absolute inset-0 bg-gradient-to-br from-blue-900/20 to-slate-950/50 backdrop-blur-3xl -z-20" />

            {/* Decorative blobs */}
            <div className="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-500/10 rounded-full blur-[120px] -z-10 pointer-events-none mix-blend-screen animate-pulse" />
            <div className="absolute bottom-0 left-0 w-[400px] h-[400px] bg-cyan-500/10 rounded-full blur-[100px] -z-10 pointer-events-none" />

            <div className="container mx-auto relative z-10">
                {/* Statistics Block */}
                <div className="grid grid-cols-3 gap-8 max-w-2xl mx-auto text-center border-t border-b border-white/10 py-10 mb-20 backdrop-blur-md">
                    <div>
                        <div className="text-4xl md:text-5xl font-black text-white tracking-tighter">128</div>
                        <div className="text-[10px] font-bold tracking-[0.2em] text-blue-400 mt-2 uppercase">Oportunidades</div>
                    </div>
                    <div>
                        <div className="text-4xl md:text-5xl font-black text-white tracking-tighter">840</div>
                        <div className="text-[10px] font-bold tracking-[0.2em] text-blue-400 mt-2 uppercase">Talentos</div>
                    </div>
                    <div>
                        <div className="text-4xl md:text-5xl font-black text-white tracking-tighter">42</div>
                        <div className="text-[10px] font-bold tracking-[0.2em] text-blue-400 mt-2 uppercase">Empresas</div>
                    </div>
                </div>

                {/* Main CTA Card */}
                <div className="max-w-5xl mx-auto">
                    <div className="relative overflow-hidden p-8 md:p-16 rounded-[40px] border border-white/10 bg-gradient-to-br from-blue-600 to-blue-800 shadow-2xl">
                        {/* Abstract background SVG for the card */}
                        <div className="absolute inset-0 opacity-10 pointer-events-none">
                            <svg height="100%" preserveAspectRatio="none" viewBox="0 0 800 400" width="100%" xmlns="http://www.w3.org/2000/svg">
                                <path d="M600 0 L800 0 L800 400 L400 400 Z" fill="white" />
                                <circle cx="700" cy="100" fill="white" r="50" />
                                <circle cx="200" cy="300" fill="none" r="150" stroke="white" strokeWidth="2" />
                            </svg>
                        </div>

                        <div className="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12 text-center lg:text-left">
                            <div className="space-y-6 lg:w-3/5">
                                <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-white text-sm font-medium backdrop-blur-sm">
                                    <Sparkles className="w-4 h-4" />
                                    <span>Liderando la industria</span>
                                </div>
                                <h2 className="text-4xl md:text-6xl font-black text-white leading-[1.1] tracking-tight">
                                    IMPULSÁ TU CARRERA EN EL SECTOR QUE MUEVE AL PAÍS
                                </h2>
                                <p className="text-lg md:text-xl text-white/80 max-w-2xl font-medium leading-relaxed">
                                    Accedé a las mejores vacantes, compará sueldos promedio y postulate en las empresas líderes. Todo en un solo lugar.
                                </p>
                            </div>

                            <div className="flex flex-col gap-4 items-center">
                                <Link href="/login">
                                    <Button size="lg" className="h-16 px-10 rounded-2xl bg-white text-blue-700 hover:bg-zinc-100 font-black text-lg shadow-xl transition-all hover:scale-105 active:scale-95">
                                        REGISTRATE GRATIS
                                    </Button>
                                </Link>
                                <p className="text-white/60 text-sm font-medium">No requiere tarjeta de crédito</p>
                            </div>
                        </div>
                    </div>

                    {/* Secondary Access (Company Panel) */}
                    <div className="mt-12 flex flex-col items-center bg-white/5 border border-white/10 p-10 rounded-[32px] backdrop-blur-xl">
                        <div className="w-16 h-16 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-400 mb-6 border border-blue-500/20">
                            <Building2 className="w-8 h-8" />
                        </div>
                        <h3 className="text-2xl font-bold text-white mb-2">¿Sos una empresa?</h3>
                        <p className="text-slate-400 mb-8 max-w-md text-center">Gestioná tus publicaciones y encontrá el talento logístico ideal hoy mismo.</p>
                        <div className="flex flex-wrap justify-center gap-4">
                            <Link href="/login">
                                <Button variant="secondary" className="h-12 px-8 rounded-xl bg-blue-600 hover:bg-blue-500 text-white border-none font-bold">
                                    Publicar Aviso
                                </Button>
                            </Link>
                            <Link href="/dashboard">
                                <Button variant="outline" className="h-12 px-8 rounded-xl border-white/10 text-white hover:bg-white/5 font-bold">
                                    Ir al Dashboard
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
