<?php

// Échappe une valeur avant de l'afficher dans le HTML (protection contre les failles XSS)
function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}