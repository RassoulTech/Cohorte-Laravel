<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'promotion_id',
        'role',
        'points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ----------------------------------------------------------- Relations

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(Reponse::class);
    }

    public function appelsIa(): HasMany
    {
        return $this->hasMany(AppelIa::class);
    }

    // -------------------------------------------------------------- Roles
    // Le role est teste par une methode et jamais par une chaine ecrite un
    // peu partout : le jour ou les roles changent, on ne modifie qu'ici.

    public function estEnseignant(): bool
    {
        return $this->role === 'enseignant';
    }

    public function estDelegue(): bool
    {
        return $this->role === 'delegue';
    }

    // ---------------------------------------------------------- Quota d'IA
    // L'offre gratuite d'OpenRouter est limitee a environ 200 requetes par
    // jour : sans compteur, une promotion entiere la consommerait en une heure.

    /**
     * Nombre d'appels a l'IA passes depuis minuit.
     *
     * On compare a now()->startOfDay() et NON avec whereDate() : whereDate
     * delegue la comparaison a MySQL, qui n'utilise pas le fuseau de
     * l'application. Minuit ne tomberait pas au bon moment.
     */
    public function appelsIaAujourdhui(): int
    {
        return $this->appelsIa()
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
    }

    public function quotaIaRestant(): int
    {
        // max(0, ...) : si le quota etait abaisse en cours de journee, le
        // restant ne doit jamais devenir negatif a l'affichage.
        return max(0, config('cohorte.quota_ia_quotidien') - $this->appelsIaAujourdhui());
    }

    public function peutAppelerIa(): bool
    {
        return $this->quotaIaRestant() > 0;
    }
}
