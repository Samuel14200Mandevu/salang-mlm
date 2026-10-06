"use client";

import Link from "next/link";
import { motion, useReducedMotion } from "framer-motion";
import { DashboardMockup } from "@/components/marketing/DashboardMockup";
import { SiteFooter } from "@/components/marketing/SiteFooter";
import { SiteHeader } from "@/components/marketing/SiteHeader";

const fade = {
  hidden: { opacity: 0, y: 12 },
  show: { opacity: 1, y: 0 },
};

export default function LandingPage() {
  const reduceMotion = useReducedMotion();

  const motionProps = reduceMotion
    ? {}
    : {
        initial: "hidden" as const,
        whileInView: "show" as const,
        viewport: { once: true, margin: "-8% 0px" },
        transition: { duration: 0.45, ease: [0.22, 1, 0.36, 1] },
      };

  return (
    <>
      <SiteHeader />
      <main>
        <section className="border-b border-border dark:border-border-dark">
          <div className="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16 lg:py-24 lg:px-8">
            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 14 }}
              animate={reduceMotion ? undefined : { opacity: 1, y: 0 }}
              transition={{ duration: 0.5, ease: [0.22, 1, 0.36, 1] }}
            >
              <p className="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-brand-primary">
                Salang Group · Health Care International
              </p>
              <h1 className="mt-3 font-display text-3xl font-bold leading-tight tracking-tight text-ink sm:text-4xl dark:text-ink-inverse">
                Pilotez commissions, réseau et portefeuilles depuis une console unique.
              </h1>
              <p className="mt-4 max-w-xl text-base leading-relaxed text-ink-muted dark:text-ink-subtle">
                Salang centralise le calcul des bonus, la structure binaire et les mouvements financiers
                de votre activité membre. Interface dense, lisible, pensée pour un usage quotidien en
                équipe distribuée.
              </p>
              <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <Link
                  href="/login"
                  className="inline-flex h-11 items-center justify-center rounded-lg bg-brand-primary px-6 text-sm font-semibold text-white hover:bg-brand-primary-hover"
                >
                  Accéder à la console
                </Link>
                <Link
                  href="#operations"
                  className="inline-flex h-11 items-center justify-center rounded-lg border border-border px-6 text-sm font-semibold text-ink dark:border-border-dark dark:text-ink-inverse"
                >
                  Voir les capacités
                </Link>
              </div>
              <dl className="mt-10 grid max-w-md grid-cols-2 gap-6 border-t border-border pt-8 dark:border-border-dark">
                <div>
                  <dt className="text-xs font-medium uppercase tracking-wide text-ink-subtle">Membres actifs</dt>
                  <dd className="mt-1 font-display text-2xl font-bold tabular-nums text-ink dark:text-ink-inverse">500+</dd>
                </div>
                <div>
                  <dt className="text-xs font-medium uppercase tracking-wide text-ink-subtle">Pays couverts</dt>
                  <dd className="mt-1 font-display text-2xl font-bold tabular-nums text-ink dark:text-ink-inverse">50+</dd>
                </div>
              </dl>
            </motion.div>

            <motion.div
              initial={reduceMotion ? false : { opacity: 0, y: 18 }}
              animate={reduceMotion ? undefined : { opacity: 1, y: 0 }}
              transition={{ duration: 0.55, delay: 0.08, ease: [0.22, 1, 0.36, 1] }}
            >
              <DashboardMockup />
              <p className="mt-3 text-xs text-ink-subtle">
                Aperçu interactif · données de démonstration
              </p>
            </motion.div>
          </div>
        </section>

        <section id="platform" className="scroll-mt-14 bg-[#ECEEF1] py-16 dark:bg-[#0F1419] sm:py-20">
          <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <motion.div variants={fade} {...motionProps} className="max-w-2xl">
              <p className="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-brand-primary">Plateforme</p>
              <h2 className="mt-2 font-display text-2xl font-bold tracking-tight text-ink sm:text-[1.75rem] dark:text-ink-inverse">
                Une vue consolidée pour les décisions financières du réseau
              </h2>
              <p className="mt-3 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
                Chaque écran est calibré pour la lecture rapide : soldes, cycles de commission, alertes
                de conformité et historique des retraits sans navigation superflue.
              </p>
            </motion.div>

            <motion.div
              variants={fade}
              {...motionProps}
              className="mt-12 grid gap-10 lg:grid-cols-[3fr_2fr] lg:items-start"
            >
              <article className="rounded-lg border border-border bg-[#FAFBFC] p-6 dark:border-border-dark dark:bg-[#1A2332]">
                <h3 className="font-display text-lg font-semibold text-ink dark:text-ink-inverse">
                  Reporting commissions
                </h3>
                <p className="mt-2 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
                  Ventilation direct / indirect / leadership, export CSV et filtres par période de clôture.
                  Les montants affichés reprennent les règles publiées du plan Salang.
                </p>
                <ul className="mt-5 space-y-2 border-t border-border pt-5 text-sm text-ink dark:border-border-dark dark:text-ink-inverse">
                  <li className="flex justify-between gap-4">
                    <span className="text-ink-muted dark:text-ink-subtle">Cycle en cours</span>
                    <span className="font-medium tabular-nums">Juin 2026</span>
                  </li>
                  <li className="flex justify-between gap-4">
                    <span className="text-ink-muted dark:text-ink-subtle">Seuil de retrait</span>
                    <span className="font-medium tabular-nums">50 USD min.</span>
                  </li>
                </ul>
              </article>

              <aside className="space-y-4">
                <div className="rounded-lg border border-border bg-page-light p-5 dark:border-border-dark dark:bg-page-dark">
                  <p className="text-xs font-semibold uppercase tracking-wide text-brand-accent">Notification</p>
                  <p className="mt-2 text-sm font-medium text-ink dark:text-ink-inverse">
                    Deux retraits en file d&apos;approbation
                  </p>
                  <p className="mt-1 text-xs text-ink-muted dark:text-ink-subtle">
                    L&apos;accent orange signale uniquement les états en attente, jamais les actions principales.
                  </p>
                </div>
                <div className="rounded-lg border border-border bg-page-light p-5 dark:border-border-dark dark:bg-page-dark">
                  <p className="text-sm font-semibold text-ink dark:text-ink-inverse">Arbre binaire</p>
                  <p className="mt-2 text-sm text-ink-muted dark:text-ink-subtle">
                    Navigation par branche, repères de volume gauche/droite et accès fiche membre en un clic.
                  </p>
                </div>
              </aside>
            </motion.div>
          </div>
        </section>

        <section id="operations" className="scroll-mt-14 py-16 sm:py-20">
          <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div className="grid gap-12 lg:grid-cols-[2fr_3fr] lg:items-center">
              <motion.div variants={fade} {...motionProps}>
                <p className="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-brand-primary">Opérations</p>
                <h2 className="mt-2 font-display text-2xl font-bold tracking-tight text-ink dark:text-ink-inverse">
                  De l&apos;adhésion au versement, un fil conducteur traçable
                </h2>
                <p className="mt-3 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
                  Inscription parrainée, activation email, achats PV/BV et calcul automatique des grades.
                  Les administrateurs disposent des mêmes vues agrégées que les membres qualifiés.
                </p>
                <Link
                  href="/login"
                  className="mt-6 inline-flex text-sm font-semibold text-brand-primary hover:underline"
                >
                  Ouvrir la console membre
                </Link>
              </motion.div>

              <motion.ol
                variants={fade}
                {...motionProps}
                className="space-y-0 border border-border dark:border-border-dark"
              >
                {[
                  ["Adhésion 30 USD", "Création du compte et rattachement au parrain."],
                  ["Activation & KYC email", "Validation de l'adresse et accès boutique."],
                  ["Volumes & grades", "PBV/BV consolidés à chaque clôture mensuelle."],
                  ["Retraits", "Demande, contrôle conformité, virement validé."],
                ].map(([title, desc], i) => (
                  <li
                    key={title}
                    className="grid grid-cols-[auto_1fr] gap-4 border-b border-border px-5 py-4 last:border-b-0 dark:border-border-dark"
                  >
                    <span className="font-display text-sm font-bold tabular-nums text-brand-primary">
                      {String(i + 1).padStart(2, "0")}
                    </span>
                    <div>
                      <p className="text-sm font-semibold text-ink dark:text-ink-inverse">{title}</p>
                      <p className="mt-0.5 text-sm text-ink-muted dark:text-ink-subtle">{desc}</p>
                    </div>
                  </li>
                ))}
              </motion.ol>
            </div>
          </div>
        </section>

        <section id="securite" className="scroll-mt-14 border-y border-border bg-[#ECEEF1] py-16 dark:border-border-dark dark:bg-[#0F1419] sm:py-20">
          <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <motion.div variants={fade} {...motionProps} className="max-w-3xl">
              <p className="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-brand-primary">Sécurité</p>
              <h2 className="mt-2 font-display text-2xl font-bold tracking-tight text-ink dark:text-ink-inverse">
                Contrôles d&apos;accès, journalisation et documents légaux accessibles
              </h2>
              <p className="mt-3 text-sm leading-relaxed text-ink-muted dark:text-ink-subtle">
                Authentification renforcée, sessions expirables et séparation des rôles admin / membre.
                Les CGU et la politique de confidentialité sont publiées et liées depuis chaque parcours
                d&apos;inscription et de connexion.
              </p>
            </motion.div>
          </div>
        </section>

        <section className="py-16 sm:py-20">
          <div className="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <div>
              <h2 className="font-display text-xl font-bold text-ink dark:text-ink-inverse">
                Prêt à vous connecter ?
              </h2>
              <p className="mt-1 text-sm text-ink-muted dark:text-ink-subtle">
                Utilisez vos identifiants membre ou contactez le support parrain.
              </p>
            </div>
            <Link
              href="/login"
              className="inline-flex h-11 shrink-0 items-center justify-center rounded-lg bg-brand-primary px-6 text-sm font-semibold text-white hover:bg-brand-primary-hover"
            >
              Connexion
            </Link>
          </div>
        </section>
      </main>
      <SiteFooter />
    </>
  );
}
