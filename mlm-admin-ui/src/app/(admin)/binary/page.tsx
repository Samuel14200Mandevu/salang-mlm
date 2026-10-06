import { BinaryTreeView } from "@/components/binary/BinaryTreeView";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import { binaryTree } from "@/lib/mock-data";

export default function BinaryPage() {
  return (
    <div className="space-y-6">
      <div>
        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">Network</p>
        <h1 className="mt-1 font-display text-2xl font-bold text-foreground">Binary Network Management</h1>
        <p className="text-sm text-muted">Select a node to inspect left and right leg volumes</p>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Organization Tree</CardTitle>
          <p className="text-sm text-muted">SVG links connect each sponsor to the two legs. Scroll sideways on small screens.</p>
        </CardHeader>
        <BinaryTreeView root={binaryTree} />
      </Card>
    </div>
  );
}
