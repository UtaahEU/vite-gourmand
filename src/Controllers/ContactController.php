<?php

namespace App\Controllers;

use App\Core\Mailer;
use App\Core\View;

class ContactController
{
    // GET /contact : formulaire vide
    public function formulaire(): void
    {
        View::render('contact', ['titre' => 'Contact', 'erreurs' => [], 'saisie' => []]);
    }

    // POST /contact: vérification puis envoi
    public function envoyer(): void
    {
        if (!csrf_valide()) {
            flash('erreur', 'Votre session a expiré. Merci de renvoyer le formulaire.');
            rediriger('/contact');
        }

        // On ne garde que des chaînes, sans espaces autour
        $lire = fn(string $cle): string => is_string($_POST[$cle] ?? null) ? trim($_POST[$cle]) : '';
        $saisie = [
            'titre' => $lire('titre'),
            'email' => $lire('email'),
            'description' => $lire('description'),
        ];

        $erreurs = [];
        if ($saisie['titre'] === '' || mb_strlen($saisie['titre']) > 100) {
            $erreurs['titre'] = 'Indiquez l\'objet de votre demande (100 caractères maximum).';
        }
        if (!filter_var($saisie['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Indiquez une adresse e-mail valide, par exemple nom@example.fr.';
        }
        if (mb_strlen($saisie['description']) < 10 || mb_strlen($saisie['description']) > 2000) {
            $erreurs['description'] = 'Votre message doit contenir entre 10 et 2000 caractères.';
        }

        if ($erreurs === []) {
            $envoye = Mailer::envoyer(
                $_ENV['CONTACT_EMAIL'],
                '[Contact] ' . $saisie['titre'],
                "Message de : {$saisie['email']}\n\n{$saisie['description']}",
                $saisie['email']
            );

            if ($envoye) {
                flash('succes', 'Merci ! Votre message a bien été envoyé. Nous vous répondons sous 48 h ouvrées.');

                rediriger('/contact'); // Evite un double envoie si on actualise la page
            }
            $erreurs['envoi'] = 'L\'envoi a échoué. Merci de réessayer plus tard.';
        }

        // Erreurs : on réaffiche le formulaire avec ce que l'utilisateur avait tapé
        http_response_code(422);
        View::render('contact', ['titre' => 'Contact', 'erreurs' => $erreurs, 'saisie' => $saisie]);
    }
}