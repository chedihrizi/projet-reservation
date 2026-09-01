<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// ============ CONFIG SMTP - Modifiez ces valeurs ============
$SMTP_HOST     = 'smtp.gmail.com';
$SMTP_PORT     = 587;
$SMTP_USERNAME = 'rayenrebai778@gmail.com';
$SMTP_PASSWORD = 'ogid yftn frdm kwxb';
$SMTP_FROM     = 'rayenrebai778@gmail.com';
$SMTP_FROM_NAME = 'SallesPro';
// ==============================================================

function sendEmail($to, $subject, $htmlBody) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_USERNAME, $SMTP_PASSWORD, $SMTP_FROM, $SMTP_FROM_NAME;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USERNAME;
        $mail->Password   = $SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($SMTP_FROM, $SMTP_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur envoi email à $to : " . $e->getMessage());
        return false;
    }
}

function reservationConfirmedTemplate($res) {
    $prenom = htmlspecialchars($res['prenom']);
    $nom = htmlspecialchars($res['nom']);
    $salle = htmlspecialchars($res['nom_salle']);
    $batiment = htmlspecialchars($res['nom_batiment']);
    $date = date('d/m/Y', strtotime($res['date_reservation']));
    $heure_debut = substr($res['heure_debut'], 0, 5);
    $heure_fin = substr($res['heure_fin'], 0, 5);
    $objet = htmlspecialchars($res['objet']);

    return '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="margin:0;padding:0;background-color:#f0fdfa;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdfa;padding:40px 20px;">
            <tr><td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0d9488,#06b6d4);padding:30px 40px;text-align:center;">
                            <div style="width:50px;height:50px;border-radius:12px;background:rgba(255,255,255,0.2);display:inline-block;line-height:50px;font-size:24px;color:#fff;margin-bottom:12px;">&#127968;</div>
                            <h1 style="color:#ffffff;font-size:22px;font-weight:700;margin:0;">SallesPro</h1>
                            <p style="color:rgba(255,255,255,0.8);font-size:13px;margin:6px 0 0;">Réservation confirmée</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:35px 40px;">
                            <p style="color:#1e293b;font-size:15px;margin:0 0 8px;">Bonjour <strong>' . $prenom . '</strong>,</p>
                            <p style="color:#475569;font-size:14px;line-height:1.7;margin:0 0 28px;">Votre réservation a été <strong style="color:#059669;">confirmée</strong> par le gestionnaire. Voici les détails :</p>

                            <!-- Details Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdfa;border-radius:12px;border:1px solid #99f6e4;margin-bottom:28px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:8px 0;color:#64748b;font-size:13px;width:120px;">&#127970; Bâtiment</td>
                                                <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;">' . $batiment . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0;color:#64748b;font-size:13px;">&#128196; Salle</td>
                                                <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;">' . $salle . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0;color:#64748b;font-size:13px;">&#128197; Date</td>
                                                <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;">' . $date . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0;color:#64748b;font-size:13px;">&#128336; Horaires</td>
                                                <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;">' . $heure_debut . ' — ' . $heure_fin . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0;color:#64748b;font-size:13px;">&#128196; Objet</td>
                                                <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;">' . $objet . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#475569;font-size:14px;line-height:1.7;margin:0;">Vous pouvez consulter vos réservations en tout temps depuis votre espace personnel.</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
                            <p style="color:#94a3b8;font-size:12px;margin:0;">Cet email a été envoyé par <strong style="color:#0d9488;">SallesPro</strong> — Système de Réservation de Salles</p>
                        </td>
                    </tr>

                </table>
            </td></tr>
        </table>
    </body>
    </html>';
}
?>