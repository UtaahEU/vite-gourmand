<?php

namespace App\Core;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Envoie des e-mails avec PHPMailer.
// Si MAIL_HOST est vide dans .env (développement), l'e-mail est écrit dans logs/mails.log
class Mailer
{
    public static function envoyer(string $destinataire, string $sujet, string $texte, ?string $repondreA = null): bool
    {
        if (empty($_ENV['MAIL_HOST'])) {
            return self::journaliser($destinataire, $sujet, $texte, $repondreA);
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->Port = (int) $_ENV['MAIL_PORT'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USER'];
            $mail->Password = $_ENV['MAIL_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(($_ENV['MAIL_FROM'] ?? '') ?: 'no-reply@vite-gourmand.fr', 'Vite & Gourmand');
            $mail->addAddress($destinataire);
            if ($repondreA !== null) {
                $mail->addReplyTo($repondreA);
            }
            $mail->Subject = $sujet;
            $mail->Body = $texte;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Erreur e-mail : ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function journaliser(string $destinataire, string $sujet, string $texte, ?string $repondreA): bool
    {
        $dossier = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0775, true);
        }

        $contenu = sprintf(
            "==== %s ===\nÀ : %s\nRépondre à : %s\nSujet : %s\n\n%s\n\n",
            date('Y-m-d H:i:s'), $destinataire, $repondreA ?? '-', $sujet, $texte
        );

        return file_put_contents($dossier . '/mails.log', $contenu, FILE_APPEND) !== false;
    }
}