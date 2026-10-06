import type { Metadata } from "next";
import Link from "next/link";
import { SiteFooter } from "@/components/marketing/SiteFooter";
import { SiteHeader } from "@/components/marketing/SiteHeader";

export const metadata: Metadata = {
  title: "Politique de confidentialité",
};

export default function PrivacyPage() {
  return (
    <>
      <SiteHeader />
      <main className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 className="font-display text-2xl font-bold text-ink dark:text-ink-inverse">
          Politique de confidentialité
        </h1>
        <p className="mt-4 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
          Salang Group décrit ici le traitement des données personnelles liées au compte membre, aux
          commissions et aux communications transactionnelles. Consultez la version complète sur le site
          institutionnel pour les droits RGPD et les contacts DPO.
        </p>
        <p className="mt-6 text-sm text-ink-muted dark:text-ink-subtle">
          <Link href="/" className="font-semibold text-brand-primary hover:underline">
            Retour à l&apos;accueil
          </Link>
        </p>
      </main>
      <SiteFooter />
    </>
  );
}
