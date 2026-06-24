@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ============================================================ -->
        <!-- EN-TÊTE AVEC ANIMATION -->
        <!-- ============================================================ -->
        <div class="mb-6 animate-fadeInUp">
            <h1 class="text-3xl font-bold text-[var(--text-primary)]">Dashboard</h1>
            <p class="text-[var(--text-secondary)] mt-1">Bonjour, <span class="font-semibold text-primary-600">{{ Auth::user()->name }}</span></p>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 1 : PROFIL + STATS -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            
            <!-- Carte Profil -->
            <div class="card-modern lg:col-span-1 animate-slideInLeft">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-2xl text-white font-bold shadow-lg shadow-primary-500/30">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-[var(--text-primary)]">{{ Auth::user()->name }}</h3>
                        <p class="text-xs text-[var(--text-secondary)]">EXECUTIVE RANKS</p>
                        <span class="badge-modern badge-success text-xs">Rank {{ Auth::user()->rank_id ?? 1 }}</span>
                    </div>
                </div>
                
                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-[var(--text-secondary)]">MEMBER ID</p>
                        <p class="font-semibold text-[var(--text-primary)]">{{ Auth::user()->id }}</p>
                    </div>
                    <div>
                        <p class="text-[var(--text-secondary)]">JOINED</p>
                        <p class="font-semibold text-[var(--text-primary)]">{{ Auth::user()->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-[var(--bg-secondary)] rounded-lg border border-[var(--border-color)]">
                    <p class="text-xs text-[var(--text-secondary)]">MY SPONSOR</p>
                    <p class="font-semibold text-[var(--text-primary)]">{{ Auth::user()->sponsor?->name ?? 'N/A' }}</p>
                    <p class="text-xs text-[var(--text-secondary)]">{{ Auth::user()->sponsor?->email ?? 'Aucun parrain' }}</p>
                </div>
                
                <div class="mt-3">
                    <p class="text-xs text-[var(--text-secondary)]">PACKAGE NAME</p>
                    <p class="font-semibold text-[var(--text-primary)]">{{ Auth::user()->package?->name ?? 'Starter Package' }}</p>
                    <button class="mt-1 text-xs text-primary-600 hover:text-primary-700 font-semibold transition-colors">
                        Update Package →
                    </button>
                </div>
            </div>

            <!-- 3 Stats Cards -->
            <div class="lg:col-span-3 grid grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="stat-card animate-fadeInUp delay-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Wallet Amount</p>
                            <p class="text-2xl font-bold text-primary-600 mt-1">₮ {{ number_format($walletBalance ?? 0, 2) }}</p>
                        </div>
                        <div class="stat-icon bg-primary-500/10 text-primary-500">💰</div>
                    </div>
                    <div class="mt-3 flex justify-between text-sm border-t border-[var(--border-color)] pt-2">
                        <span class="text-[var(--text-secondary)]">Balance</span>
                        <span class="font-semibold">₮ {{ number_format($walletBalance ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">Payouts</span>
                        <span class="font-semibold">₮ {{ number_format($totalCommission ?? 0, 2) }}</span>
                    </div>
                </div>

                <div class="stat-card animate-fadeInUp delay-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Active Status</p>
                            <p class="text-2xl font-bold text-green-500 mt-1">Active</p>
                        </div>
                        <div class="stat-icon bg-green-500/10 text-green-500">✅</div>
                    </div>
                    <div class="mt-3 border-t border-[var(--border-color)] pt-2">
                        <p class="text-sm text-[var(--text-secondary)]">Personal Sales</p>
                        <p class="font-semibold">₮ {{ number_format($totalCommission ?? 0, 2) }}</p>
                    </div>
                </div>

                <div class="stat-card animate-fadeInUp delay-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">My Rank</p>
                            <p class="text-2xl font-bold text-primary-600 mt-1">{{ Auth::user()->rank ?? 'Distributor' }}</p>
                        </div>
                        <div class="stat-icon bg-purple-500/10 text-purple-500">🏆</div>
                    </div>
                    <div class="mt-3 flex justify-between text-sm border-t border-[var(--border-color)] pt-2">
                        <span class="text-[var(--text-secondary)]">Current</span>
                        <span class="font-semibold">{{ Auth::user()->rank ?? 'Distributor' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[var(--text-secondary)]">Last Rank</span>
                        <span class="font-semibold">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 2 : NETWORK ANALYTICS -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <div class="card-modern lg:col-span-2 animate-fadeInUp delay-2">
                <h4 class="font-bold text-[var(--text-primary)] mb-4">📊 NETWORK ANALYTICS</h4>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-3 bg-[var(--bg-secondary)] rounded-xl">
                        <p class="text-2xl font-bold text-primary-500">{{ $totalDownlines ?? 0 }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">Total Downlines</p>
                    </div>
                    <div class="text-center p-3 bg-[var(--bg-secondary)] rounded-xl">
                        <p class="text-2xl font-bold text-blue-500">₮ {{ number_format($totalCommission ?? 0, 2) }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">Purchase Volume</p>
                    </div>
                    <div class="text-center p-3 bg-[var(--bg-secondary)] rounded-xl">
                        <p class="text-2xl font-bold text-green-500">{{ $level1 ?? 0 }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">Active Members</p>
                    </div>
                    <div class="text-center p-3 bg-[var(--bg-secondary)] rounded-xl">
                        <p class="text-2xl font-bold text-purple-500">{{ ($level2 ?? 0) + ($level3 ?? 0) }}</p>
                        <p class="text-xs text-[var(--text-secondary)]">Paid Accounts</p>
                    </div>
                </div>

                <!-- Graphique -->
                <div>
                    <div class="flex justify-between text-xs text-[var(--text-secondary)] mb-2">
                        @foreach($monthlyData ?? [] as $data)
                            <span>{{ substr($data['month'], 0, 3) }}</span>
                        @endforeach
                    </div>
                    <div class="h-32 flex items-end gap-1">
                        @if(isset($monthlyData) && count($monthlyData) > 0)
                            @php $max = max(array_column($monthlyData, 'amount')) ?: 1; @endphp
                            @foreach($monthlyData as $index => $data)
                                <div class="flex-1 flex flex-col items-center group">
                                    <div class="graph-bar w-full bg-primary-500/30 hover:bg-primary-500 transition-all"
                                         style="height: {{ $data['amount'] > 0 ? max(5, ($data['amount'] / $max) * 100) : 5 }}%">
                                        <span class="tooltip">₮ {{ number_format($data['amount'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sales Category -->
            <div class="card-modern animate-fadeInUp delay-3">
                <h4 class="font-bold text-[var(--text-primary)] mb-4">📈 Sales Category</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[var(--text-secondary)]">Macbook, Inc</span>
                            <span class="font-semibold text-primary-500">4.26%</span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="progress-bar-fill" style="width: 58%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[var(--text-secondary)]">Short Products</span>
                            <span class="font-semibold text-blue-500">0.31%</span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="progress-bar-fill" style="width: 42%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[var(--text-secondary)]">User Growth</span>
                            <span class="font-semibold text-green-500">+3.85%</span>
                        </div>
                        <div class="progress-bar mt-1">
                            <div class="progress-bar-fill" style="width: 76%"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center p-4 bg-[var(--bg-secondary)] rounded-xl">
                    <p class="text-3xl font-bold text-primary-500">3,768</p>
                    <p class="text-xs text-[var(--text-secondary)]">New signups website + mobile</p>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 3 : RECENT MEMBERS + ACTIVITIES -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <div class="card-modern animate-slideInLeft delay-4">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-bold text-[var(--text-primary)]">👥 Recent Downline Members</h4>
                    <span class="text-xs text-[var(--text-secondary)]">{{ $totalDownlines ?? 0 }} total</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Package</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentMembers) && $recentMembers->count() > 0)
                                @foreach($recentMembers as $member)
                                    <tr>
                                        <td>
                                            <p class="font-medium text-[var(--text-primary)]">{{ $member->name }}</p>
                                            <p class="text-xs text-[var(--text-secondary)]">{{ $member->country ?? 'US' }}</p>
                                        </td>
                                        <td>{{ $member->package->name ?? 'Starter' }}</td>
                                        <td class="text-right text-green-500 font-semibold">₮ {{ number_format($member->total_earnings ?? 0, 2) }}</td>
                                        <td class="text-right">
                                            <span class="badge-modern {{ ($member->is_active ?? true) ? 'badge-success' : 'badge-danger' }}">
                                                {{ ($member->is_active ?? true) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-right">
                    <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-semibold transition-colors">
                        SEE ALL ({{ $totalDownlines ?? 0 }}) →
                    </a>
                </div>
            </div>

            <div class="card-modern animate-slideInRight delay-5">
                <h4 class="font-bold text-[var(--text-primary)] mb-4">🔄 Activities</h4>
                <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                    @if(isset($recentActivities) && $recentActivities->count() > 0)
                        @foreach($recentActivities as $activity)
                            <div class="activity-item">
                                <div class="activity-avatar">
                                    {{ substr($activity->fromUser->name ?? 'S', 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-[var(--text-primary)]">
                                        <span class="font-semibold">{{ $activity->fromUser->name ?? 'System' }}</span>
                                        {{ $activity->type_label ?? 'joined membership' }}
                                    </p>
                                    <p class="text-xs text-[var(--text-secondary)]">
                                        Paid Membership (₮ {{ number_format($activity->amount ?? 0, 2) }})
                                    </p>
                                    <p class="text-xs text-[var(--text-secondary)] mt-0.5">{{ $activity->created_at->diffForHumans() ?? 'Recently' }}</p>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- SECTION 4 : BOTTOM STATS -->
        <!-- ============================================================ -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-fadeInUp delay-6">
            <div class="stat-card text-center">
                <p class="text-2xl font-bold text-primary-500">{{ $totalDownlines ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Downlines</p>
            </div>
            <div class="stat-card text-center">
                <p class="text-2xl font-bold text-blue-500">₮ {{ number_format($totalCommission ?? 0, 2) }}</p>
                <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Total Commissions</p>
            </div>
            <div class="stat-card text-center">
                <p class="text-2xl font-bold text-green-500">{{ $level1 ?? 0 }}</p>
                <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Active Members</p>
            </div>
            <div class="stat-card text-center">
                <p class="text-2xl font-bold text-purple-500">{{ ($level2 ?? 0) + ($level3 ?? 0) }}</p>
                <p class="text-xs text-[var(--text-secondary)] uppercase tracking-wider">Paid Accounts</p>
            </div>
        </div>

    </div>
</div>
@endsection
