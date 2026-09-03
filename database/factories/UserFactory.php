<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Le mot de passe est hache UNE SEULE FOIS puis reutilise : bcrypt est
     * volontairement lent, le rehacher pour chaque utilisateur ferait durer le
     * seed plusieurs secondes.
     */
    protected static ?string $password;

    /**
     * Prenoms et noms senegalais.
     *
     * Faker ne propose pas de locale senegalaise, et sa locale par defaut
     * generait des noms anglophones ("Eric Davis") dans une application
     * destinee a une ecole de Dakar. On fournit donc nos propres listes.
     *
     * @var list<string>
     */
    protected array $prenoms = [
        'Awa', 'Fatou', 'Aminata', 'Mariama', 'Khadija', 'Ndeye', 'Sokhna',
        'Coumba', 'Bineta', 'Astou', 'Rokhaya', 'Seynabou', 'Adama', 'Nogaye',
        'Moussa', 'Ousmane', 'Ibrahima', 'Abdoulaye', 'Cheikh', 'Mamadou',
        'Modou', 'Alioune', 'Babacar', 'Serigne', 'Assane', 'Lamine',
        'Malick', 'Souleymane', 'Idrissa', 'Demba', 'Pape', 'Elhadji',
    ];

    /** @var list<string> */
    protected array $noms = [
        'Diop', 'Ndiaye', 'Fall', 'Sow', 'Ba', 'Sarr', 'Gueye', 'Diallo',
        'Faye', 'Seck', 'Mbaye', 'Cisse', 'Diouf', 'Thiam', 'Sy', 'Kane',
        'Camara', 'Toure', 'Diagne', 'Niang', 'Sagna', 'Badji', 'Sane', 'Coly',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prenom = fake()->randomElement($this->prenoms);
        $nom = fake()->randomElement($this->noms);

        return [
            'name' => $prenom . ' ' . $nom,

            // L'adresse derive du nom pour rester lisible dans l'interface.
            // Le suffixe numerique garantit l'unicite : avec 32 prenoms et
            // 24 noms tires au hasard, deux membres peuvent porter le meme nom,
            // et la colonne email a une contrainte unique.
            'email' => Str::slug($prenom . ' ' . $nom, '.')
                . fake()->unique()->numberBetween(10, 999)
                . '@cohorte.test',

            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
