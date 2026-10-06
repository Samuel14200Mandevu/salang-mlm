import { WalletPanel } from "@/components/wallets/WalletPanel";

export default function WalletsPage() {
  return (
    <div className="space-y-6">
      <div>
        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">Finance</p>
        <h1 className="mt-1 font-display text-2xl font-bold text-foreground">Wallet Management</h1>
        <p className="text-sm text-muted">Balances, payouts, and the commission ledger</p>
      </div>
      <WalletPanel />
    </div>
  );
}
