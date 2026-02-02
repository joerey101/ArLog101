import Link from "next/link";
import { Separator } from "@/components/ui/separator";
import { Github, Twitter, Linkedin, Instagram } from "lucide-react";

export function Footer() {
  return (
    <footer className="w-full bg-white border-t border-slate-100 relative overflow-hidden">
      {/* Subtle decorative gradient blob */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[200px] bg-blue-50/50 blur-[100px] rounded-full pointer-events-none" />

      <div className="container mx-auto px-6 py-12 md:py-20 relative z-10">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12">

          {/* Brand Column */}
          <div className="space-y-6">
            <div className="flex items-center gap-2">
              <img src="/logo.png" alt="ArLog Logo" className="h-12 w-auto object-contain" />
            </div>
            <p className="text-slate-500 text-sm leading-relaxed max-w-xs">
              Transformando la gestión de talento logístico con tecnología de vanguardia y diseño centrado en las personas.
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
            <h4 className="text-sm font-bold text-slate-900 tracking-wider uppercase">Plataforma</h4>
            <ul className="space-y-3">
              <FooterLink href="/empleos">Empleos</FooterLink>
              <FooterLink href="/empresas">Empresas</FooterLink>
              <FooterLink href="/candidatos">Candidatos</FooterLink>
              <FooterLink href="/precios">Precios</FooterLink>
            </ul>
          </div>

          <div className="space-y-4">
            <h4 className="text-sm font-bold text-slate-900 tracking-wider uppercase">Compañía</h4>
            <ul className="space-y-3">
              <FooterLink href="/nosotros">Sobre Nosotros</FooterLink>
              <FooterLink href="/blog">Blog</FooterLink>
              <FooterLink href="/carreras">Carreras</FooterLink>
              <FooterLink href="/contacto">Contacto</FooterLink>
            </ul>
          </div>

          <div className="space-y-4">
            <h4 className="text-sm font-bold text-slate-900 tracking-wider uppercase">Legal</h4>
            <ul className="space-y-3">
              <FooterLink href="/privacidad">Privacidad</FooterLink>
              <FooterLink href="/terminos">Términos</FooterLink>
              <FooterLink href="/cookies">Cookies</FooterLink>
              <FooterLink href="/seguridad">Seguridad</FooterLink>
            </ul>
          </div>
        </div>

        <Separator className="my-10 bg-slate-100" />

        <div className="flex flex-col md:flex-row justify-between items-center text-xs text-slate-400 space-y-4 md:space-y-0">
          <p>© {new Date().getFullYear()} ArLog. Todos los derechos reservados.</p>
          <div className="flex items-center space-x-6">
            <span className="flex items-center gap-2">
              <span className="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.3)]"></span>
              Sistemas Operativos
            </span>
            <p>Hecho con <span className="text-red-500/80 text-base">❤</span> en Argentina</p>
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
      className="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-100 hover:bg-blue-600 hover:border-blue-600 hover:text-white text-slate-400 transition-all duration-300 group"
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
        className="text-slate-500 hover:text-blue-600 transition-colors duration-200 text-sm flex items-center gap-1 hover:translate-x-1 transition-transform"
      >
        {children}
      </Link>
    </li>
  );
}
