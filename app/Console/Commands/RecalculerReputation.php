<?php

namespace App\Console\Commands;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Remet d'aplomb la colonne users.points.
 *
 * Le score est STOCKE (incremente au fil de l'eau par les controleurs) et non
 * recalcule a chaque affichage : c'est rapide a lire, mais ca peut se
 * desynchroniser si un increment est oublie quelque part. Cette commande est
 * le filet de securite qui rend le choix du stockage defendable.
 *
 * Usage : php artisan cohorte:recalculer-reputation
 */
class RecalculerReputation extends Command
{
    protected $signature = 'cohorte:recalculer-reputation';

    protected $description = 'Recalcule le score de contribution de tous les membres';

    public function handle(): int
    {
        $modifies = 0;

        // chunkById traite les utilisateurs par paquets de cent au lieu de
        // tous les charger en memoire. Reflexe a prendre pour toute commande
        // qui parcourt une table entiere.
        User::query()->chunkById(100, function ($membres) use (&$modifies) {
            foreach ($membres as $membre) {
                /*
                 * Sous-requete : "compte les questions dont la reponse retenue
                 * figure parmi les reponses ecrites par ce membre".
                 *
                 * Ecrite ainsi, elle ne ramene AUCUN identifiant en PHP :
                 * c'est la base de donnees qui fait tout le travail, en une
                 * seule requete.
                 */
                $reponsesRetenues = Publication::query()
                    ->whereIn('reponse_retenue_id', $membre->reponses()->select('id'))
                    ->count();

                // Le bareme recompense l'UTILITE, pas le volume.
                $score = $reponsesRetenues * 10                                     // une reponse retenue
                    + $membre->reponses()->count() * 3                              // une reponse ecrite
                    + $membre->publications()->questions()->count() * 1             // une question posee
                    - $membre->publications()->where('statut', 'refuse')->count() * 5; // une publication refusee

                // max(0, ...) : la reputation ne descend jamais sous zero.
                $score = max(0, $score);

                if ($membre->points !== $score) {
                    $membre->update(['points' => $score]);
                    $modifies++;
                }
            }
        });

        $this->info("Réputation recalculée. {$modifies} membre(s) mis à jour.");

        return self::SUCCESS;
    }
}
