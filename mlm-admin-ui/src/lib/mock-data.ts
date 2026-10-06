export type KpiIcon = "revenue" | "profit" | "members" | "wallet" | "binary" | "payout";

export const kpiStats: {
  title: string;
  value: number;
  change: number;
  sparkline: number[];
  isCount?: boolean;
  accent: string;
  icon: KpiIcon;
}[] = [
  {
    title: "Total Revenue",
    value: 2458975,
    change: 12.5,
    sparkline: [40, 55, 48, 62, 58, 72, 68, 80, 76, 88],
    accent: "#7C3AED",
    icon: "revenue",
  },
  {
    title: "Net Profit",
    value: 892340.5,
    change: 8.2,
    sparkline: [30, 42, 38, 50, 47, 55, 52, 60, 58, 65],
    accent: "#06B6D4",
    icon: "profit",
  },
  {
    title: "Active Members",
    value: 18452,
    change: 5.4,
    sparkline: [20, 22, 24, 23, 26, 28, 30, 29, 32, 34],
    isCount: true,
    accent: "#10B981",
    icon: "members",
  },
  {
    title: "Wallet Volume",
    value: 1203450.75,
    change: -2.1,
    sparkline: [70, 68, 65, 66, 64, 62, 63, 61, 60, 59],
    accent: "#F59E0B",
    icon: "wallet",
  },
  {
    title: "Binary Pairs",
    value: 9421,
    change: 3.8,
    sparkline: [10, 12, 11, 14, 15, 16, 17, 18, 19, 21],
    isCount: true,
    accent: "#7C3AED",
    icon: "binary",
  },
  {
    title: "Pending Payouts",
    value: 145890,
    change: -4.5,
    sparkline: [50, 48, 52, 49, 47, 45, 46, 44, 43, 42],
    accent: "#EF4444",
    icon: "payout",
  },
];

export const revenueOverview = [
  { date: "Jan", revenue: 180000, profit: 72000 },
  { date: "Feb", revenue: 210000, profit: 84000 },
  { date: "Mar", revenue: 195000, profit: 78000 },
  { date: "Apr", revenue: 240000, profit: 96000 },
  { date: "May", revenue: 280000, profit: 112000 },
  { date: "Jun", revenue: 265000, profit: 106000 },
  { date: "Jul", revenue: 310000, profit: 124000 },
  { date: "Aug", revenue: 295000, profit: 118000 },
  { date: "Sep", revenue: 340000, profit: 136000 },
  { date: "Oct", revenue: 360000, profit: 144000 },
  { date: "Nov", revenue: 385000, profit: 154000 },
  { date: "Dec", revenue: 420000, profit: 168000 },
];

/** Salang commission plan (mock shares) */
export const incomeBreakdown = [
  { name: "Direct Bonus", value: 32, color: "#7C3AED" },
  { name: "Indirect Bonus", value: 26, color: "#06B6D4" },
  { name: "Binary / Matching", value: 20, color: "#10B981" },
  { name: "Leadership", value: 14, color: "#F59E0B" },
  { name: "Retail & Other", value: 8, color: "#9CA3AF" },
];

export const incomeTotal = 2458975;

export const topRanks = [
  { rank: 1, name: "Diamond Pearl", users: 42, income: 458900, growth: 14.2, status: "Top" },
  { rank: 2, name: "Diamant Bleu", users: 118, income: 392100, growth: 11.8, status: "Growing" },
  { rank: 3, name: "Saphire Manager", users: 286, income: 318450, growth: 9.4, status: "Stable" },
  { rank: 4, name: "Manager Senior", users: 890, income: 245600, growth: 6.1, status: "Stable" },
  { rank: 5, name: "Directeur", users: 2105, income: 128300, growth: -1.2, status: "Watch" },
];

export const recentActivity = [
  { id: 1, user: "Amina Mukendi", action: "Withdrawal approved — $1,250", time: "2 min ago", tone: "success" as const },
  { id: 2, user: "Jean Dupont", action: "KYC verified", time: "14 min ago", tone: "info" as const },
  { id: 3, user: "System", action: "Binary cycle closed — leg #8842", time: "32 min ago", tone: "gold" as const },
  { id: 4, user: "Marie Ngalula", action: "Package upgrade — Gold", time: "1 h ago", tone: "info" as const },
  { id: 5, user: "David Martin", action: "Payout rejected — mismatch", time: "2 h ago", tone: "danger" as const },
];

const FIRST = ["Amina", "Jean", "Marie", "David", "Chantal", "Luc", "Sarah", "Patrick", "Grace", "Eric", "Nadia", "Olivier"];
const LAST = ["Mukendi", "Dupont", "Ngalula", "Martin", "Kabila", "Bernard", "Ilunga", "Moreau"];
const RANKS = [
  "Distributeur",
  "Qualification",
  "Directeur",
  "Manager Senior",
  "Saphire Manager",
  "Diamant Bleu",
] as const;
const PACKAGES = ["Adhésion 30$", "Starter BV", "Business BV", "Elite BV"] as const;
const STATUSES = ["Active", "Active", "Active", "Inactive", "Suspended"] as const;
const KYC = ["Verified", "Verified", "Pending", "Rejected"] as const;

export type MockUser = {
  id: string;
  name: string;
  email: string;
  rank: (typeof RANKS)[number];
  joined: string;
  package: (typeof PACKAGES)[number];
  wallet: number;
  status: (typeof STATUSES)[number];
  kyc: (typeof KYC)[number];
};

export const mockUsers: MockUser[] = Array.from({ length: 48 }, (_, i) => {
  const name = `${FIRST[i % FIRST.length]} ${LAST[i % LAST.length]}`;
  const slug = name.toLowerCase().replace(" ", ".");
  const month = String((i % 12) + 1).padStart(2, "0");
  const year = 2023 + (i % 3);
  return {
    id: `SAL-${1028 + i}`,
    name,
    email: `${slug}${i}@salang.test`,
    rank: RANKS[i % RANKS.length],
    joined: `${year}-${month}-15`,
    package: PACKAGES[i % PACKAGES.length],
    wallet: Math.round((((i * 173 + 89) % 9800) + 40 + ((i * 17) % 100) / 100) * 100) / 100,
    status: STATUSES[i % STATUSES.length],
    kyc: KYC[i % KYC.length],
  };
});

export type BinaryNode = {
  id: string;
  name: string;
  leftVolume: number;
  rightVolume: number;
  children: BinaryNode[];
};

export const binaryTree: BinaryNode = {
  id: "root",
  name: "Salang HQ",
  leftVolume: 12450,
  rightVolume: 11820,
  children: [
    {
      id: "l1",
      name: "Amina Mukendi",
      leftVolume: 6200,
      rightVolume: 5800,
      children: [
        { id: "l1a", name: "Luc Bernard", leftVolume: 3100, rightVolume: 2900, children: [] },
        { id: "l1b", name: "Sarah Ilunga", leftVolume: 2800, rightVolume: 2600, children: [] },
      ],
    },
    {
      id: "r1",
      name: "Jean Dupont",
      leftVolume: 5900,
      rightVolume: 6100,
      children: [
        { id: "r1a", name: "David Martin", leftVolume: 3000, rightVolume: 3200, children: [] },
        { id: "r1b", name: "Chantal Moreau", leftVolume: 2700, rightVolume: 2850, children: [] },
      ],
    },
  ],
};

export const geoStats = [
  { country: "Dem. Rep. Congo", label: "DR Congo", value: 4200 },
  { country: "United States of America", label: "United States", value: 2400 },
  { country: "France", label: "France", value: 1800 },
  { country: "South Africa", label: "South Africa", value: 1100 },
  { country: "Belgium", label: "Belgium", value: 950 },
];

export const walletKpis: {
  title: string;
  value: number;
  change: number;
  sparkline: number[];
  accent: string;
  icon: KpiIcon;
}[] = [
  {
    title: "Available Balance",
    value: 486220.4,
    change: 6.4,
    sparkline: [22, 24, 23, 28, 30, 29, 34, 36, 35, 40],
    accent: "#10B981",
    icon: "wallet",
  },
  {
    title: "Pending Withdrawals",
    value: 145890,
    change: -4.5,
    sparkline: [18, 20, 19, 17, 16, 18, 15, 14, 13, 12],
    accent: "#F59E0B",
    icon: "payout",
  },
  {
    title: "Paid This Month",
    value: 98240,
    change: 9.1,
    sparkline: [8, 10, 12, 11, 14, 16, 15, 18, 20, 22],
    accent: "#7C3AED",
    icon: "revenue",
  },
  {
    title: "Network Fees",
    value: 12480.25,
    change: 1.8,
    sparkline: [4, 4, 5, 5, 5, 6, 6, 6, 7, 7],
    accent: "#06B6D4",
    icon: "profit",
  },
];

export type WalletTx = {
  id: string;
  member: string;
  type: "Commission" | "Withdrawal" | "Bonus" | "Fee";
  amount: number;
  status: "Completed" | "Pending" | "Failed";
  date: string;
};

export const walletTransactions: WalletTx[] = [
  { id: "TX-9041", member: "Amina Mukendi", type: "Commission", amount: 240.5, status: "Completed", date: "2026-10-02" },
  { id: "TX-9040", member: "Jean Dupont", type: "Withdrawal", amount: -1250, status: "Pending", date: "2026-10-02" },
  { id: "TX-9039", member: "Marie Ngalula", type: "Bonus", amount: 80, status: "Completed", date: "2026-10-01" },
  { id: "TX-9038", member: "David Martin", type: "Withdrawal", amount: -640, status: "Failed", date: "2026-10-01" },
  { id: "TX-9037", member: "Luc Bernard", type: "Commission", amount: 410.75, status: "Completed", date: "2026-09-30" },
  { id: "TX-9036", member: "Sarah Ilunga", type: "Fee", amount: -18.4, status: "Completed", date: "2026-09-30" },
  { id: "TX-9035", member: "Chantal Moreau", type: "Bonus", amount: 150, status: "Completed", date: "2026-09-29" },
  { id: "TX-9034", member: "Patrick Kabila", type: "Commission", amount: 96.2, status: "Pending", date: "2026-09-29" },
];
