import Link from "next/link";
import { SalangLogo } from "@/components/marketing/SalangLogo";

const nav = [
  { href: "/#platform", label: "Plateforme" },
  { href: "/#operations", label: "Opérations" },
  { href: "/#securite", label: "Sécurité" },
];

export function SiteHeader() {
  return (
    <header className="sticky top-0 z-50 border-b border-border bg-page-light/95 dark:border-border-dark dark:bg-page-dark/95">
      <div className="mx-auto flex h-14 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <SalangLogo />
        <nav className="hidden items-center gap-8 text-sm font-medium text-ink-muted md:flex" aria-label="Principal">
          {nav.map((item) => (
            <Link key={item.href} href={item.href} className="hover:text-brand-primary dark:hover:text-ink-inverse">
              {item.label}
            </Link>
          ))}
        </nav>
        <div className="flex items-center gap-3">
          <Link
            href="/login"
            className="hidden text-sm font-medium text-ink-muted hover:text-brand-primary sm:inline dark:hover:text-ink-inverse"
          >
            Connexion
          </Link>
          <Link
            href="/login"
            className="inline-flex h-9 items-center justify-center rounded-lg bg-brand-primary px-4 text-sm font-semibold text-white hover:bg-brand-primary-hover"
          >
            Espace membre
          </Link>
        </div>
      </div>
    </header>
  );
}
