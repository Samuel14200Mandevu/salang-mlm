import type { Metadata } from "next";

export const metadata: Metadata = {
  title: {
    default: "Salang — Console réseau & commissions",
    template: "%s · Salang",
  },
  description:
    "Plateforme membre Salang Group : suivi des commissions, portefeuilles et structure binaire, dans un cadre institutionnel.",
};

export default function MarketingLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-full bg-page-light text-ink dark:bg-page-dark dark:text-ink-inverse">{children}</div>
  );
}
