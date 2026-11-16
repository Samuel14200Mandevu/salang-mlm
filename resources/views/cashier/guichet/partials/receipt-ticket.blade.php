@php
    $member = $withdrawal->user;
    $receiptNo = $withdrawal->receipt_number ?: ('GUICH-'.$withdrawal->id);
    $receiptDate = $withdrawal->completed_at ?? $withdrawal->created_at ?? now();
    $statusLabel = match ($withdrawal->status) {
        'completed' => 'REMIS',
        'pending' => 'EN ATTENTE',
        'cancelled' => 'ANNULE',
        'rejected' => 'REJETE',
        default => strtoupper((string) $withdrawal->status),
    };
    $statusPaid = $withdrawal->status === 'completed';
@endphp
<div class="guichet-ticket">
    <div class="header">
        <div class="store-logo">
            <img src="{{ asset('images/salang_logo.png') }}" alt="Salang Group Logo">
        </div>
        <div class="store-name">SALANG GROUP SARL</div>
        <div class="store-sub">E-COMMERCE &amp; MLM</div>
        <div class="store-contact">
            <strong>Tel:</strong> +243 975 220 079<br>
            <strong>Email:</strong> support@salanggroup.com<br>
            <strong>Site:</strong> www.salanggroup.com
        </div>
        <div class="store-address">
            Rond Point CHIKUDU, Batiment KBS au 3eme Niveau
        </div>
        <div class="store-reg">
            <strong>N° ID.NAT:</strong> 22-7300-N634640<br>
            <strong>N° RCCM:</strong> CD/BKVIRCM/20-8-001165001<br>
            <strong>N° IMPORT-EXPORT:</strong> 0024/CBX-21/1000439SK/Z
        </div>

        <div class="separator-dashed"></div>

        <div class="doc-title">REÇU RETRAIT GUICHET</div>
        <div class="doc-place">Goma, le {{ $receiptDate->format('d/m/Y') }}</div>

        <div class="ticket-number">{{ $receiptNo }}</div>
        <div class="ticket-date">{{ $receiptDate->format('d/m/Y H:i') }}</div>

        <span class="source-badge">Espèces · Guichet</span>
        <span class="status-badge {{ $statusPaid ? 'is-paid' : 'is-pending' }}">{{ $statusLabel }}</span>
    </div>

    <div class="info">
        <div class="row">
            <span class="label">Membre</span>
            <span class="value">{{ $member->name ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">N° ID</span>
            <span class="value">{{ $member->sponsor_id ?? '—' }}</span>
        </div>
        @if(filled($member?->phone))
            <div class="row">
                <span class="label">Tel</span>
                <span class="value">{{ $member->phone }}</span>
            </div>
        @endif
        @if(filled($member?->email))
            <div class="row">
                <span class="label">Email</span>
                <span class="value">{{ $member->email }}</span>
            </div>
        @endif
        <div class="row">
            <span class="label">Retrait N°</span>
            <span class="value">#{{ $withdrawal->id }}</span>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">Montant remis</div>
        <div class="value">${{ number_format((float) $withdrawal->amount, 2) }}</div>
    </div>

    <div class="total">
        <div class="row">
            <span class="label">Montant</span>
            <span class="value">${{ number_format((float) $withdrawal->amount, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">Frais</span>
            <span class="value">${{ number_format((float) ($withdrawal->fee ?? 0), 2) }}</span>
        </div>
        <div class="row total-row">
            <span class="label">NET REMIS</span>
            <span class="value">${{ number_format((float) ($withdrawal->net_amount ?? $withdrawal->amount), 2) }}</span>
        </div>
    </div>

    <div class="payment-info">
        <div class="row">
            <span class="label">Mode</span>
            <span class="value">Espèces (guichet)</span>
        </div>
        <div class="row">
            <span class="label">Statut</span>
            <span class="value {{ $statusPaid ? 'paid' : '' }}">{{ $statusLabel }}</span>
        </div>
        <div class="row">
            <span class="label">Caissier</span>
            <span class="value">{{ $withdrawal->cashier->name ?? auth()->user()->name }}</span>
        </div>
        @if($withdrawal->completed_at)
            <div class="row">
                <span class="label">Remis le</span>
                <span class="value">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</span>
            </div>
        @endif
    </div>

    <div class="footer">
        <div class="thank-you">MERCI — SALANG HEALTH CARE</div>
        <div class="barcode">*{{ $receiptNo }}*</div>
        <div>Conservez ce reçu comme preuve de retrait.</div>
        <div>www.salanggroup.com</div>
    </div>
</div>
