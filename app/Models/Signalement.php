<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table de journalisation : un signalement relie un membre a une publication.
 * Pas de factory, ce sont des actions reelles d'utilisateurs.
 */
class Signalement extends Model
{
    // Sans $fillable, les create() de la phase 8 leveraient une MassAssignmentException.
    protected $fillable = ['publication_id', 'user_id', 'motif'];

    // ----------------------------------------------------------- Relations

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
