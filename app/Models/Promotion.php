<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;

    protected $fillable = ['nom', 'code_invitation', 'annee', 'ouverte'];

    protected function casts(): array
    {
        return [
            'ouverte' => 'boolean',
            'annee' => 'integer',
        ];
    }

    // ----------------------------------------------------------- Relations

    /**
     * Les membres de la promotion : la cle promotion_id est sur la table users.
     */
    public function membres(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
