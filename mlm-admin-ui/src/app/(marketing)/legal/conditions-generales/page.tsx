import type { Metadata } from "next";
import Link from "next/link";
import { SiteFooter } from "@/components/marketing/SiteFooter";
import { SiteHeader } from "@/components/marketing/SiteHeader";

export const metadata: Metadata = {
  title: "Conditions générales",
};

export default function TermsPage() {
  return (
    <>
      <SiteHeader />
      <main className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
        <h1 className="font-display text-2xl font-bold text-ink dark:text-ink-inverse">Conditions générales d&apos;utilisation</h1>
        <p className="mt-4 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
          Document régissant l&apos;accès à la plateforme membre Salang Group. Le texte définitif est
          hébergé sur le domaine principal de l&apos;entreprise ; cette page sert de point d&apos;entrée
          depuis la console Next.js.
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
