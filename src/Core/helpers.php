<?php

// Échappe une valeur avant de l'afficher dans le HTML (protection contre les failles XSS)
function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

// Formate un prix : 45 -> "45", 45.5 -> "45,50", 1200 -> "1 200"
function prix(float|string $montant): string
{
    $montant = (float) $montant;
    $decimales = floor($montant) == $montant ? 0 : 2;
    return number_format($montant, $decimales, ',', ' ');
}