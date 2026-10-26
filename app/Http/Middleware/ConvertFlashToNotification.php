<?php

namespace App\Http\Middleware;

use App\Notifications\ActionFeedbackNotification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Transforme les messages flash (success/error/info/warning)
 * en notifications persistantes dans l'icône cloche.
 */
class ConvertFlashToNotification
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $flashes = [
                'success' => 'success',
                'error' => 'danger',
                'info' => 'info',
                'warning' => 'warning',
            ];

            foreach ($flashes as $flashKey => $tone) {
                $message = session($flashKey);

                if (! is_string($message) || trim($message) === '') {
                    continue;
                }

                $user->notify(new ActionFeedbackNotification(
                    message: trim($message),
                    tone: $tone,
                    url: $request->fullUrl(),
                ));

                // Plus de bannière dans la page : uniquement la cloche
                $request->session()->forget($flashKey);

                // Marqueur pour animer la cloche (sans réafficher le texte)
                session()->flash('_action_feedback_pulse', [
                    'tone' => $tone,
                ]);
            }
        }

        return $next($request);
    }
}
