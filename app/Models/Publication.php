<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    /** @use HasFactory<\Database\Factories\PublicationFactory> */
    use HasFactory;

    protected $fillable = [
        'promotion_id', 'user_id', 'type', 'titre', 'contenu',
        'statut', 'motif_moderation', 'epingle_le', 'reponse_retenue_id',
    ];

    protected function casts(): array
    {
        return ['epingle_le' => 'datetime'];
    }

    // ----------------------------------------------------------- Relations

    /**
     * L'auteur s'appelle auteur() et non user() : le nom est plus lisible dans
     * les vues. Comme il ne suit pas la convention, on precise la cle 'user_id'.
     */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(Reponse::class);
    }

    public function signalements(): HasMany
    {
        return $this->hasMany(Signalement::class);
    }

    public function reponseRetenue(): BelongsTo
    {
        return $this->belongsTo(Reponse::class, 'reponse_retenue_id');
    }

    // -------------------------------------------------------------- Scopes
    // Un scope est un morceau de requete reutilisable. La regle metier est
    // ecrite une seule fois ici, au lieu d'etre recopiee dans chaque controleur.

    /**
     * Le cloisonnement : on ne voit que les publications de sa promotion.
     */
    public function scopeDeLaPromotion(Builder $query, int $promotionId): void
    {
        $query->where('promotion_id', $promotionId);
    }

    /**
     * Les publications reellement affichables dans le fil.
     */
    public function scopeVisibles(Builder $query): void
    {
        $query->where('statut', 'publie');
    }

    public function scopeQuestions(Builder $query): void
    {
        $query->where('type', 'question');
    }

    public function scopePosts(Builder $query): void
    {
        $query->where('type', 'post');
    }
}
