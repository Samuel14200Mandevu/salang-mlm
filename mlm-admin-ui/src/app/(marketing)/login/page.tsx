import type { Metadata } from "next";
import Link from "next/link";
import { LoginForm } from "@/components/marketing/LoginForm";
import { SalangLogo } from "@/components/marketing/SalangLogo";
import { SiteFooter } from "@/components/marketing/SiteFooter";

export const metadata: Metadata = {
  title: "Connexion",
  description: "Accédez à votre espace membre Salang Group.",
};

export default function LoginPage() {
  return (
    <>
      <main className="mx-auto flex min-h-[calc(100vh-1px)] max-w-6xl flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div className="mx-auto w-full max-w-[400px]">
          <div className="mb-8 text-center">
            <SalangLogo className="justify-center" href="/" />
          </div>

          <div className="rounded-lg border border-border bg-[#FAFBFC] p-6 text-left sm:p-8 dark:border-border-dark dark:bg-[#1A2332]">
            <h1 className="font-display text-xl font-bold tracking-tight text-ink dark:text-ink-inverse">
              Connexion membre
            </h1>
            <p className="mt-1 text-sm text-ink-muted dark:text-ink-subtle">
              Identifiants associés à votre compte Salang Group.
            </p>
            <LoginForm />
            <p className="mt-6 text-center text-sm text-ink-muted dark:text-ink-subtle">
              Pas encore inscrit ?{" "}
              <Link href="/#platform" className="font-semibold text-brand-primary hover:underline">
                Découvrir l&apos;adhésion
              </Link>
            </p>
          </div>

          <p className="mt-6 text-center text-xs text-ink-subtle">
            <Link href="/legal/conditions-generales" className="hover:text-brand-primary">
              CGU
            </Link>
            {" · "}
            <Link href="/legal/confidentialite" className="hover:text-brand-primary">
              Confidentialité
            </Link>
          </p>
        </div>
      </main>
      <SiteFooter />
    </>
  );
}
