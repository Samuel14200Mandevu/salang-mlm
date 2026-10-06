import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { topRanks } from "@/lib/mock-data";
import { formatCurrency, formatNumber } from "@/lib/utils";

function statusVariant(status: string) {
  if (status === "Top") return "success" as const;
  if (status === "Growing") return "default" as const;
  if (status === "Watch") return "danger" as const;
  return "muted" as const;
}

export function TopRankTable() {
  return (
    <Card className="p-0">
      <CardHeader className="px-5 pt-5">
        <CardTitle>Top Rank Performance</CardTitle>
        <p className="text-sm text-muted">Leaders by network volume and growth</p>
      </CardHeader>
      <Table>
        <TableHeader>
          <TableRow className="hover:bg-transparent">
            <TableHead>Rank</TableHead>
            <TableHead>Leader</TableHead>
            <TableHead>Total Users</TableHead>
            <TableHead>Total Income</TableHead>
            <TableHead>Avg. Growth</TableHead>
            <TableHead>Status</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {topRanks.map((row) => (
            <TableRow key={row.rank}>
              <TableCell>
                <span className="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-primary/15 text-xs font-bold text-primary">
                  {row.rank}
                </span>
              </TableCell>
              <TableCell>
                <div className="flex items-center gap-2">
                  <Avatar name={row.name} />
                  <span className="font-medium">{row.name}</span>
                </div>
              </TableCell>
              <TableCell className="tabular-nums">{formatNumber(row.users)}</TableCell>
              <TableCell className="tabular-nums">{formatCurrency(row.income)}</TableCell>
              <TableCell className={row.growth >= 0 ? "text-success" : "text-danger"}>
                {row.growth >= 0 ? "+" : ""}
                {row.growth}%
              </TableCell>
              <TableCell>
                <Badge variant={statusVariant(row.status)}>{row.status}</Badge>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </Card>
  );
}
