<?php

namespace App\Services;

use App\Models\User;
use App\Support\MlmRank;
use Illuminate\Support\Str;

class MemberAssistantService
{
    public function memberContext(User $user): array
    {
        $user->loadMissing(['wallet', 'rank']);

        $wallet = $user->wallet;
        $balance = $wallet ? (float) $wallet->balance : 0.0;

        $rankLevel = (int) ($user->getAttributes()['rank_level'] ?? $user->rank?->level ?? 1);
        $rankName = $user->rank?->name ?? MlmRank::label(max(0, min(9, $rankLevel)));

        $kycLabels = [
            'verified' => 'Vérifié',
            'pending' => 'En attente',
            'rejected' => 'Refusé',
            'none' => 'Non soumis',
        ];
        $kycStatus = $user->kyc_status ?? 'none';

        return [
            'balance' => number_format($balance, 2),
            'personal_pv' => number_format((int) round((float) ($user->pv_balance ?? 0))),
            'rank_name' => $rankName,
            'kyc_label' => $kycLabels[$kycStatus] ?? ucfirst((string) $kycStatus),
        ];
    }

    public function welcomeReply(User $user): array
    {
        $context = $this->memberContext($user);

        return [
            'title' => 'Assistant Salang',
            'body' => 'Bonjour ! Je réponds aux questions sur l’application : portefeuille, dépôts, retraits (2,5 %), PV, grades, commissions, réseau et KYC. '
                . 'Solde : ' . $context['balance'] . ' USD · PV : ' . $context['personal_pv'] . ' · Grade : ' . $context['rank_name'] . ' · KYC : ' . $context['kyc_label'] . '.',
            'links' => [],
            'related' => [],
        ];
    }

    /**
     * Réponse unique pour l’UI chat (meilleur résultat + suggestions).
     *
     * @return array{title: string, body: string, links: array<int, array{label: string, url: string}>, related: array<int, array{title: string, url: string|null}>}
     */
    public function chatReply(User $user, string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return $this->welcomeReply($user);
        }

        $results = $this->search($user, $query);
        $primary = $results[0];

        $links = [];
        if (! empty($primary['url']) && ! empty($primary['route_label'])) {
            $links[] = [
                'label' => $primary['route_label'],
                'url' => $primary['url'],
            ];
        }

        $related = [];
        foreach (array_slice($results, 1, 3) as $row) {
            if (($row['id'] ?? '') === 'no-match') {
                continue;
            }
            $related[] = [
                'title' => $row['title'] ?? '',
                'url' => $row['url'] ?? null,
            ];
        }

        return [
            'title' => $primary['title'] ?? 'Réponse',
            'body' => $primary['answer'] ?? '',
            'links' => $links,
            'related' => $related,
        ];
    }

    /**
     * @return array<int, array{id: string, title: string, answer: string, route: string|null, route_label: string|null, url: string|null, score: int}>
     */
    public function search(User $user, ?string $query): array
    {
        $articles = config('member-assistant.articles', []);
        $limit = (int) config('member-assistant.max_results', 8);
        $context = $this->memberContext($user);

        $query = trim((string) $query);
        if ($query === '') {
            return $this->formatResults(array_slice($articles, 0, $limit), $context, 0);
        }

        $terms = $this->tokenize($query);
        $scored = [];

        foreach ($articles as $article) {
            $score = $this->scoreArticle($article, $terms, $query);
            if ($score > 0) {
                $scored[] = ['article' => $article, 'score' => $score];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        if ($scored === []) {
            return [[
                'id' => 'no-match',
                'title' => 'Aucune réponse exacte',
                'answer' => 'Reformulez votre question (ex. « solde », « retrait », « PV ») ou contactez le support pour une aide personnalisée.',
                'route' => 'contact',
                'route_label' => 'Nous contacter',
                'url' => route('contact'),
                'score' => 0,
            ]];
        }

        $picked = array_map(fn ($row) => $row['article'], array_slice($scored, 0, $limit));

        return $this->formatResults($picked, $context, $scored[0]['score'] ?? 0);
    }

    /**
     * @param  array<int, string>  $terms
     */
    private function scoreArticle(array $article, array $terms, string $rawQuery): int
    {
        $score = 0;
        $title = Str::lower($article['title'] ?? '');
        $answer = Str::lower($article['answer'] ?? '');
        $keywords = array_map('strtolower', $article['keywords'] ?? []);
        $haystack = $title . ' ' . $answer . ' ' . implode(' ', $keywords);

        if (Str::contains($haystack, Str::lower($rawQuery))) {
            $score += 5;
        }

        foreach ($terms as $term) {
            if (strlen($term) < 2) {
                continue;
            }
            foreach ($keywords as $keyword) {
                if (Str::contains($keyword, $term) || Str::contains($term, $keyword)) {
                    $score += 4;
                }
            }
            if (Str::contains($title, $term)) {
                $score += 3;
            }
            if (Str::contains($answer, $term)) {
                $score += 1;
            }
        }

        return $score;
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $query): array
    {
        $normalized = Str::lower($query);
        $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($parts) ? $parts : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $articles
     * @return array<int, array{id: string, title: string, answer: string, route: string|null, route_label: string|null, url: string|null, score: int}>
     */
    private function formatResults(array $articles, array $context, int $topScore): array
    {
        $out = [];

        foreach ($articles as $article) {
            $answer = $article['answer'] ?? '';
            if (! empty($article['dynamic'])) {
                $answer = str_replace(
                    [':balance', ':personal_pv', ':rank_name', ':kyc_label'],
                    [$context['balance'], $context['personal_pv'], $context['rank_name'], $context['kyc_label']],
                    $answer
                );
            }

            $routeName = $article['route'] ?? null;
            $url = null;
            if ($routeName && app('router')->has($routeName)) {
                $url = route($routeName);
            }

            $out[] = [
                'id' => $article['id'] ?? Str::uuid()->toString(),
                'title' => $article['title'] ?? '',
                'answer' => $answer,
                'route' => $routeName,
                'route_label' => $article['route_label'] ?? null,
                'url' => $url,
                'score' => $topScore,
            ];
        }

        return $out;
    }
}
