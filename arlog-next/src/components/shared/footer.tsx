import Link from "next/link";
import { Separator } from "@/components/ui/separator";
import { Github, Twitter, Linkedin, Instagram } from "lucide-react";

export function Footer() {
  return (
    <footer className="w-full bg-black/40 backdrop-blur-xl border-t border-white/10 relative overflow-hidden theme-glass">
      {/* Decorative gradient blob */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[200px] bg-blue-500/10 blur-[100px] rounded-full pointer-events-none" />

      <div className="container mx-auto px-4 py-12 md:py-16 relative z-10">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12">
          
          {/* Brand Column */}
          <div className="space-y-4">
            <h3 className="text-2xl font-bold bg-gradient-to-r from-white to-white/60 bg-clip-text text-transparent">
              ArLog
            </h3>
            <p className="text-zinc-400 text-sm leading-relaxed max-w-xs">
              Transformando la gestión de talento con inteligencia artificial y diseño de vanguardia.
            </p>
            <div className="flex space-x-4 pt-2">
              <SocialLink href="#" icon={<Twitter className="w-4 h-4" />} label="Twitter" />
              <SocialLink href="#" icon={<Github className="w-4 h-4" />} label="GitHub" />
              <SocialLink href="#" icon={<Linkedin className="w-4 h-4" />} label="LinkedIn" />
              <SocialLink href="#" icon={<Instagram className="w-4 h-4" />} label="Instagram" />
            </div>
          </div>

          {/* Links Columns */}
          <div className="space-y-4">
            <h4 className="text-sm font-semibold text-white tracking-wider uppercase">Plataforma</h4>
            <ul className="space-y-2">
              <FooterLink href="/empleos">Empleos</FooterLink>
              <FooterLink href="/empresas">Empresas</FooterLink>
              <FooterLink href="/candidatos">Candidatos</FooterLink>
              <FooterLink href="/precios">Precios</FooterLink>
            </ul>
          </div>

          <div className="space-y-4">
            <h4 className="text-sm font-semibold text-white tracking-wider uppercase">Compañía</h4>
            <ul className="space-y-2">
              <FooterLink href="/nosotros">Sobre Nosotros</FooterLink>
              <FooterLink href="/blog">Blog</FooterLink>
              <FooterLink href="/carreras">Carreras</FooterLink>
              <FooterLink href="/contacto">Contacto</FooterLink>
            </ul>
          </div>

          <div className="space-y-4">
            <h4 className="text-sm font-semibold text-white tracking-wider uppercase">Legal</h4>
            <ul className="space-y-2">
              <FooterLink href="/privacidad">Privacidad</FooterLink>
              <FooterLink href="/terminos">Términos</FooterLink>
              <FooterLink href="/cookies">Cookies</FooterLink>
              <FooterLink href="/seguridad">Seguridad</FooterLink>
            </ul>
          </div>
        </div>

        <Separator className="my-8 bg-white/10" />

        <div className="flex flex-col md:flex-row justify-between items-center text-xs text-zinc-500 space-y-4 md:space-y-0">
          <p>© {new Date().getFullYear()} ArLog. Todos los derechos reservados.</p>
          <div className="flex items-center space-x-6">
            <span className="flex items-center gap-2">
              <span className="w-2 h-2 rounded-full bg-green-500 animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.5)]"></span>
              Sistemas Operativos
            </span>
            <p>Hecho con <span className="text-red-500/80">❤</span> en Argentina</p>
          </div>
        </div>
      </div>
    </footer>
  );
}

function SocialLink({ href, icon, label }: { href: string; icon: React.ReactNode; label: string }) {
  return (
    <Link 
      href={href} 
      className="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 hover:text-white text-zinc-400 transition-all duration-300 backdrop-blur-sm group"
      aria-label={label}
    >
      <span className="group-hover:scale-110 transition-transform duration-300">
        {icon}
      </span>
    </Link>
  );
}

function FooterLink({ href, children }: { href: string; children: React.ReactNode }) {
  return (
    <li>
      <Link 
        href={href} 
        className="text-zinc-400 hover:text-white transition-colors duration-200 text-sm flex items-center gap-1 hover:translate-x-1 transistion-transform"
      >
        {children}
      </Link>
    </li>
  );
}
