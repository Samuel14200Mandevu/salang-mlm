import Link from "next/link";
import { SalangLogo } from "@/components/marketing/SalangLogo";

const legal = {
  terms: "/legal/conditions-generales",
  privacy: "/legal/confidentialite",
};

export function SiteFooter() {
  const year = new Date().getFullYear();

  return (
    <footer className="border-t border-border bg-page-light dark:border-border-dark dark:bg-page-dark">
      <div className="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-12 sm:px-6 lg:px-8">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
          <div className="max-w-sm">
            <SalangLogo showWordmark />
            <p className="mt-3 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
              Console membre et réseau Salang Group. Commissions, portefeuilles et suivi binaire
              dans un environnement conçu pour la conformité et la traçabilité.
            </p>
          </div>
          <nav
            className="flex flex-col gap-2 text-sm font-medium text-ink dark:text-ink-inverse"
            aria-label="Liens légaux"
          >
            <span className="text-xs font-semibold uppercase tracking-wide text-ink-subtle">
              Conformité
            </span>
            <Link href={legal.terms} className="text-ink-muted hover:text-brand-primary dark:hover:text-ink-inverse">
              Conditions générales (CGU)
            </Link>
            <Link href={legal.privacy} className="text-ink-muted hover:text-brand-primary dark:hover:text-ink-inverse">
              Politique de confidentialité
            </Link>
            <Link href="/login" className="text-ink-muted hover:text-brand-primary dark:hover:text-ink-inverse">
              Connexion membre
            </Link>
          </nav>
        </div>
        <div className="flex flex-col gap-2 border-t border-border pt-6 text-xs text-ink-subtle dark:border-border-dark sm:flex-row sm:items-center sm:justify-between">
          <p>&copy; {year} Salang Group. Tous droits réservés.</p>
          <p>Siège · Hong Kong · Health Care International</p>
        </div>
      </div>
    </footer>
  );
}
