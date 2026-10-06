"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

export function LoginForm() {
  const router = useRouter();
  const [pending, setPending] = useState(false);

  function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setPending(true);
    router.push("/dashboard");
  }

  return (
    <form className="mt-6 space-y-4 text-left" onSubmit={onSubmit} noValidate>
      <div>
        <label htmlFor="email" className="mb-1.5 block text-sm font-medium text-ink dark:text-ink-inverse">
          Adresse email
        </label>
        <input
          id="email"
          name="email"
          type="email"
          autoComplete="email"
          required
          placeholder="vous@exemple.com"
          className="h-10 w-full rounded-lg border border-border bg-page-light px-3 text-left text-sm text-ink outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40 dark:border-border-dark dark:bg-page-dark dark:text-ink-inverse"
        />
      </div>
      <div>
        <label htmlFor="password" className="mb-1.5 block text-sm font-medium text-ink dark:text-ink-inverse">
          Mot de passe
        </label>
        <input
          id="password"
          name="password"
          type="password"
          autoComplete="current-password"
          required
          className="h-10 w-full rounded-lg border border-border bg-page-light px-3 text-left text-sm text-ink outline-none focus-visible:ring-2 focus-visible:ring-brand-primary/40 dark:border-border-dark dark:bg-page-dark dark:text-ink-inverse"
        />
        <p className="mt-2">
          <Link href="#" className="text-xs font-medium text-brand-primary hover:underline">
            Mot de passe oublié
          </Link>
        </p>
      </div>
      <label className="flex cursor-pointer items-center gap-2 text-sm text-ink-muted dark:text-ink-subtle">
        <input
          type="checkbox"
          name="remember"
          className="mt-0.5 h-4 w-4 rounded border-border text-brand-primary focus-visible:ring-brand-primary/40"
        />
        Rester connecté sur cet appareil
      </label>
      <button
        type="submit"
        disabled={pending}
        className="flex h-11 w-full items-center justify-center rounded-lg bg-brand-primary text-sm font-semibold text-white hover:bg-brand-primary-hover disabled:opacity-60"
      >
        {pending ? "Redirection…" : "Se connecter"}
      </button>
    </form>
  );
}
