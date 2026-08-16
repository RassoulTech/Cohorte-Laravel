<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reponse extends Model
{
    /** @use HasFactory<\Database\Factories\ReponseFactory> */
    use HasFactory;

    protected $fillable = ['publication_id', 'user_id', 'contenu'];

    // ----------------------------------------------------------- Relations

    /**
     * Meme nommage que sur Publication : un auteur s'appelle toujours auteur().
     */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
