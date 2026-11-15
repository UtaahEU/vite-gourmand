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

// Redirige vers une autre page puis arrête le script
function rediriger(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// Jeton CSRF : une valeur secrète par session, cachée dans chaque formulaire POST.
// Un site pirate ne peut pas la connaître, doncin ne peut pas envoyer le formulaire à la place de l'utilisateur.
function csrf_jeton(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_champ(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_jeton() . '">';
}

function csrf_valide(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && is_string($_POST['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// Message "flash" : stocké en session, affiché une seule fois sur la page suivante
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function lire_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Attributs d'un champ de formulaire : classe Bootstrap + attributs RGAA si le champ est en erreur
// $aide = id d'un texte d'aide lié au champ
function attributs_champ(array $erreurs, string $champ, string $aide = ''): string
{
    $classe = 'form-control';
    $decritPar = $aide;
    $attributs = '';

    if (isset($erreurs[$champ])) {
        $classe .= ' is-invalid';
        $decritPar = trim($decritPar . ' erreur-' . $champ);
        $attributs .= ' aria-invalid="true"';
    }
    if ($decritPar) {
        $attributs .= ' aria-describedby="' . $decritPar .'"';
    }

    return 'class="' . $classe . '"' . $attributs;
}

// Message d'erreur affiché sous un champ
function erreur_champ(array $erreurs, string $champ): string
{
    if (!isset($erreurs[$champ])) {
        return '';
    }
    return '<div id="erreur-' . $champ . '" class="invalid-feedback">' . e($erreurs[$champ]) . '</div>';
}