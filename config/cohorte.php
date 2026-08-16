<?php

return [
    // Nombre d'appels a l'IA autorises par membre et par jour
    'quota_ia_quotidien' => (int) env('COHORTE_QUOTA_IA', 10),

    // Nombre de signalements a partir duquel une publication est masquee
    'seuil_signalement' => (int) env('COHORTE_SEUIL_SIGNALEMENT', 3),

    // Score de reputation ouvrant le droit d'epingler
    'seuil_epinglage' => (int) env('COHORTE_SEUIL_EPINGLAGE', 50),

    // Que faire si OpenRouter ne repond pas : publier quand meme (true)
    // ou envoyer en file de moderation (false) ? Decision a justifier.
    'moderation_fail_open' => (bool) env('COHORTE_MODERATION_FAIL_OPEN', false),
];

