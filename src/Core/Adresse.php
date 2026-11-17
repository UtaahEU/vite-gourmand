<?php

namespace App\Core;

// Localise une adresse avec l'API Géoplateforme de l'État (gratuite, sans clé)
// et calcule la distance depuis Bordeaux
class Adresse
{
    private const BORDEAUX_LAT = 44.837789;
    private const BORDEAUX_LON = -0.57918;

    // ['ville' => ..., 'bordeaux => bool, 'km' => float], ou null si l'adresse est introuvable
    public static function localiser(string $adresse, string $codePostal, string $ville): ?array
    {
        $url = 'https://data.geopf.fr/geocodage/search?' . http_build_query([
            'q' => "$adresse $codePostal $ville",
            'postcode' => $codePostal,
            'limit' => 1,
        ]);

        // 4 secondes maximum : on ne bloque pas la page si l'API ne répond pas
        $reponse = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 4]]));
        $donnees = $reponse ? json_decode($reponse, true) : null;
        $lieu = $donnees['features'][0] ?? null;

        // Score trop faible, ou seule la ville a été trouvée (pas la rue) = adresse incorrecte
        if ($lieu === null || $lieu['properties']['score'] < 0.4 || $lieu['properties']['type'] === 'municipality') {
            return null;
        }

        [$lon, $lat] = $lieu['geometry']['coordinates'];
        $bordeaux = $lieu['properties']['city'] === 'Bordeaux';

        return [
            'ville' => $lieu['properties']['city'],
            'bordeaux' => $bordeaux,
            'km' => $bordeaux ? 0.0 : self::distance($lat, $lon),
        ];
    }

    // Distance à vol d'oiseau depuis Bordeaux (formule de haversine), arrondie a 0,1km
    private static function distance(float $lat, float $lon): float
    {
        $dLat= deg2rad($lat -self::BORDEAUX_LAT);
        $dLon= deg2rad($lon -self::BORDEAUX_LON);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad(self::BORDEAUX_LAT)) * cos(deg2rad($lat)) * sin($dLon / 2) ** 2;

        return round(6371 * 2 * asin(sqrt($a)), 1);// rayon de la Terre = 6371 km
    }
}