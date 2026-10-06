import { SettingsPanel } from "@/components/settings/SettingsPanel";

export default function SettingsPage() {
  return (
    <div className="space-y-6">
      <div>
        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">System</p>
        <h1 className="mt-1 font-display text-2xl font-bold text-foreground">Settings</h1>
        <p className="text-sm text-muted">Compensation defaults and console controls</p>
      </div>
      <SettingsPanel />
    </div>
  );
}
