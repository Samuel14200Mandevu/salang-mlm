"use client";

import { useEffect, useState, type ReactNode } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

type SettingsForm = {
  platformName: string;
  currency: string;
  pairValue: string;
  directBonus: string;
  matchingBonus: string;
  emailAlerts: boolean;
  autoPayout: boolean;
  kycRequired: boolean;
};

const defaults: SettingsForm = {
  platformName: "MLM Pro",
  currency: "USD",
  pairValue: "100",
  directBonus: "15",
  matchingBonus: "10",
  emailAlerts: true,
  autoPayout: false,
  kycRequired: true,
};

const storageKey = "mlm-admin-settings";

export function SettingsPanel() {
  const [form, setForm] = useState<SettingsForm>(defaults);
  const [status, setStatus] = useState("");

  useEffect(() => {
    const raw = window.localStorage.getItem(storageKey);
    if (!raw) return;
    try {
      setForm({ ...defaults, ...JSON.parse(raw) });
    } catch {
      setForm(defaults);
    }
  }, []);

  function update<K extends keyof SettingsForm>(key: K, value: SettingsForm[K]) {
    setForm((current) => ({ ...current, [key]: value }));
    setStatus("");
  }

  function save() {
    window.localStorage.setItem(storageKey, JSON.stringify(form));
    setStatus("Settings saved on this browser");
  }

  return (
    <div className="grid gap-4 xl:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Compensation plan</CardTitle>
          <p className="text-sm text-muted">Preview values only — not connected to the ledger yet</p>
        </CardHeader>
        <div className="space-y-3">
          <Field label="Platform name">
            <Input value={form.platformName} onChange={(event) => update("platformName", event.target.value)} />
          </Field>
          <Field label="Currency">
            <Input value={form.currency} onChange={(event) => update("currency", event.target.value)} />
          </Field>
          <Field label="Binary pair value">
            <Input value={form.pairValue} onChange={(event) => update("pairValue", event.target.value)} />
          </Field>
          <div className="grid gap-3 sm:grid-cols-2">
            <Field label="Direct bonus %">
              <Input value={form.directBonus} onChange={(event) => update("directBonus", event.target.value)} />
            </Field>
            <Field label="Matching bonus %">
              <Input value={form.matchingBonus} onChange={(event) => update("matchingBonus", event.target.value)} />
            </Field>
          </div>
        </div>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Controls</CardTitle>
          <p className="text-sm text-muted">Operational switches for the admin console</p>
        </CardHeader>
        <div className="space-y-2">
          <Toggle
            checked={form.emailAlerts}
            onChange={(value) => update("emailAlerts", value)}
            label="Email alerts"
            description="Notify admins when a payout waits more than 24 hours"
          />
          <Toggle
            checked={form.autoPayout}
            onChange={(value) => update("autoPayout", value)}
            label="Auto payout"
            description="Release verified withdrawals under the daily cap"
          />
          <Toggle
            checked={form.kycRequired}
            onChange={(value) => update("kycRequired", value)}
            label="KYC required"
            description="Block withdrawals until identity is verified"
          />
        </div>
        <div className="mt-5 flex items-center gap-3">
          <Button type="button" onClick={save}>
            Save settings
          </Button>
          {status && <p className="text-sm text-success">{status}</p>}
        </div>
      </Card>
    </div>
  );
}

function Field({ label, children }: { label: string; children: ReactNode }) {
  return (
    <label className="block text-sm">
      <span className="mb-1.5 block text-xs font-medium uppercase tracking-wide text-muted">{label}</span>
      {children}
    </label>
  );
}

function Toggle({
  checked,
  onChange,
  label,
  description,
}: {
  checked: boolean;
  onChange: (value: boolean) => void;
  label: string;
  description: string;
}) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      onClick={() => onChange(!checked)}
      className="flex w-full items-center justify-between gap-4 rounded-xl border border-border bg-canvas px-4 py-3 text-left"
    >
      <span>
        <span className="block text-sm font-medium text-foreground">{label}</span>
        <span className="block text-xs text-muted">{description}</span>
      </span>
      <span className={cn("relative h-6 w-11 shrink-0 rounded-full p-0.5 transition", checked ? "bg-primary shadow-glow-sm" : "bg-border")}>
        <span className={cn("block h-5 w-5 rounded-full bg-white transition", checked && "translate-x-5")} />
      </span>
    </button>
  );
}
