<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trace de chaque appel a OpenRouter : c'est la base du calcul du quota.
 * Pas de factory, ce sont des appels reels.
 */
class AppelIa extends Model
{
    /**
     * Laravel deduirait "appel_ias" du nom de la classe : on impose le nom
     * de table demande par le cahier des charges.
     */
    protected $table = 'appels_ia';

    protected $fillable = ['user_id', 'contexte', 'modele', 'reussi', 'duree_ms'];

    protected function casts(): array
    {
        return ['reussi' => 'boolean'];
    }

    // ----------------------------------------------------------- Relations

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
