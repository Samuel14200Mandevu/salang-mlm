<?php
// app/Helpers/NotificationHelper.php

namespace App\Helpers;

class NotificationHelper
{
    /**
     * Obtenir le libellé d'un type de commission
     */
    public static function getCommissionTypeLabel(string $type): string
    {
        $labels = [
            'direct' => 'Commission directe',
            'indirect' => 'Commission indirecte',
            'leadership' => 'Bonus de leadership',
            'retail' => 'Profit retail',
            'bonus' => 'Bonus',
            'unilevel' => 'Commission Unilevel',
            'sponsor' => 'Commission de parrainage',
            'team' => 'Commission d\'équipe',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Obtenir le libellé d'une méthode de paiement
     */
    public static function getPaymentMethodLabel(string $method): string
    {
        $labels = [
            'crypto' => 'Cryptomonnaie',
            'mobile_money' => 'Mobile Money',
            'bank' => 'Virement bancaire',
            'stripe' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'usdt' => 'USDT (Tether)',
            'btc' => 'Bitcoin',
            'eth' => 'Ethereum',
            'airtel' => 'Airtel Money',
            'orange' => 'Orange Money',
            'mpesa' => 'M-Pesa',
        ];

        return $labels[$method] ?? ucfirst($method);
    }

    /**
     * Obtenir le libellé d'un statut
     */
    public static function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'completed' => 'Terminé',
            'paid' => 'Payé',
            'cancelled' => 'Annulé',
            'failed' => 'Échoué',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'verified' => 'Vérifié',
            'not_submitted' => 'Non soumis',
            'partial' => 'Partiel',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Obtenir le libellé d'un niveau de rang
     */
    public static function getRankLabel(string $rank): string
    {
        $labels = [
            'distributor' => 'Distributeur',
            'supervisor' => 'Superviseur',
            'assistant-manager' => 'Assistant Manager',
            'manager' => 'Manager',
            'senior-manager' => 'Senior Manager',
            'soaring-manager' => 'Soaring Manager',
            'sapphire-manager' => 'Sapphire Manager',
            'blue-diamond' => 'Blue Diamond',
            'diamond' => 'Diamant',
            'pearl' => 'Perle',
        ];

        return $labels[$rank] ?? ucfirst(str_replace('-', ' ', $rank));
    }

    /**
     * Obtenir le libellé d'un type de package
     */
    public static function getPackageLabel(string $package): string
    {
        $labels = [
            'starter' => 'Package Starter',
            'silver' => 'Package Silver',
            'bronze' => 'Package Bronze',
            'gold' => 'Package Gold',
            'emerald' => 'Package Emerald',
        ];

        return $labels[$package] ?? ucfirst($package);
    }

    /**
     * Formater un montant en USD
     */
    public static function formatAmount(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }

    /**
     * Formater un montant avec devise
     */
    public static function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'XOF' => 'CFA',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . ' ' . number_format($amount, 2);
    }

    /**
     * Formater une date
     */
    public static function formatDate($date): string
    {
        if (!$date) {
            return 'N/A';
        }

        if ($date instanceof \DateTime) {
            return $date->format('d/m/Y à H:i');
        }

        return $date;
    }

    /**
     * Formater une date courte
     */
    public static function formatDateShort($date): string
    {
        if (!$date) {
            return 'N/A';
        }

        if ($date instanceof \DateTime) {
            return $date->format('d/m/Y');
        }

        return $date;
    }

    /**
     * Obtenir le préfixe d'un type de notification
     */
    public static function getNotificationPrefix(string $type): string
    {
        $prefixes = [
            'commission_paid' => 'Nouvelle commission',
            'rank_upgraded' => 'Promotion de rang',
            'package_purchased' => 'Achat de package',
            'payment_received' => 'Paiement reçu',
            'withdrawal_approved' => 'Retrait approuvé',
            'withdrawal_rejected' => 'Retrait rejeté',
            'welcome' => 'Bienvenue',
            'new_downline' => 'Nouveau membre',
            'kyc_verified' => 'KYC vérifié',
            'kyc_rejected' => 'KYC rejeté',
        ];

        return $prefixes[$type] ?? 'Notification';
    }

    /**
     * Obtenir la classe CSS d'un statut
     */
    public static function getStatusClass(string $status): string
    {
        $classes = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            'failed' => 'bg-red-100 text-red-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'verified' => 'bg-green-100 text-green-800',
            'not_submitted' => 'bg-gray-100 text-gray-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
        ];

        return $classes[$status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Obtenir la couleur d'un statut (pour les badges)
     */
    public static function getStatusColor(string $status): string
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'paid' => 'success',
            'cancelled' => 'secondary',
            'failed' => 'danger',
            'approved' => 'success',
            'rejected' => 'danger',
            'active' => 'success',
            'inactive' => 'secondary',
            'verified' => 'success',
            'not_submitted' => 'secondary',
            'partial' => 'warning',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Obtenir le message d'une notification
     */
    public static function getNotificationMessage(array $data): string
    {
        $type = $data['type'] ?? '';
        $message = $data['message'] ?? '';

        switch ($type) {
            case 'commission_paid':
                $amount = self::formatAmount($data['amount'] ?? 0);
                $typeLabel = self::getCommissionTypeLabel($data['commission_type'] ?? '');
                return "Vous avez reçu une commission {$typeLabel} de {$amount}.";
                
            case 'rank_upgraded':
                $newRank = $data['new_rank'] ?? 'Nouveau rang';
                return "Félicitations ! Vous avez été promu au rang de {$newRank}.";
                
            case 'package_purchased':
                $packageName = $data['package_name'] ?? 'Package';
                $amount = self::formatAmount($data['price'] ?? 0);
                return "Achat du package {$packageName} confirmé pour un montant de {$amount}.";
                
            case 'payment_received':
                $amount = self::formatAmount($data['amount'] ?? 0);
                $method = self::getPaymentMethodLabel($data['method'] ?? '');
                return "Paiement de {$amount} reçu via {$method}.";
                
            case 'withdrawal_approved':
                $amount = self::formatAmount($data['amount'] ?? 0);
                return "Votre demande de retrait de {$amount} a été approuvée.";
                
            case 'withdrawal_rejected':
                $amount = self::formatAmount($data['amount'] ?? 0);
                $reason = $data['reason'] ?? 'motif non spécifié';
                return "Votre demande de retrait de {$amount} a été rejetée. Motif : {$reason}.";
                
            case 'welcome':
                $sponsor = $data['sponsor_name'] ?? '';
                if ($sponsor) {
                    return "Bienvenue sur Salang MLM ! Vous avez été parrainé par {$sponsor}.";
                }
                return "Bienvenue sur Salang MLM ! Nous sommes ravis de vous accueillir.";
                
            case 'new_downline':
                $name = $data['downline_name'] ?? 'Un nouveau membre';
                $level = $data['level'] ?? 1;
                return "{$name} a rejoint votre réseau (niveau {$level}).";
                
            case 'kyc_verified':
                return "Votre vérification KYC a été approuvée avec succès.";
                
            case 'kyc_rejected':
                $reason = $data['reason'] ?? 'motif non spécifié';
                return "Votre vérification KYC a été rejetée. Motif : {$reason}.";
                
            default:
                return $message ?: 'Vous avez une nouvelle notification.';
        }
    }

    /**
     * Obtenir le sujet d'un email de notification
     */
    public static function getNotificationSubject(string $type, array $data = []): string
    {
        $prefix = self::getNotificationPrefix($type);
        
        switch ($type) {
            case 'commission_paid':
                $amount = self::formatAmount($data['amount'] ?? 0);
                return "{$prefix} - {$amount}";
                
            case 'rank_upgraded':
                $rank = $data['new_rank'] ?? 'Nouveau rang';
                return "{$prefix} - {$rank}";
                
            case 'package_purchased':
                $package = $data['package_name'] ?? 'Package';
                return "{$prefix} - {$package}";
                
            case 'payment_received':
                $amount = self::formatAmount($data['amount'] ?? 0);
                return "{$prefix} - {$amount}";
                
            case 'withdrawal_approved':
            case 'withdrawal_rejected':
                $amount = self::formatAmount($data['amount'] ?? 0);
                return "{$prefix} - {$amount}";
                
            default:
                return $prefix;
        }
    }
}