<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

class EmailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setupMailServer();
    }

    private function setupMailServer() {
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['SMTP_EMAIL'];
        $this->mail->Password   = $_ENV['SMTP_PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
    }

    public function sendVerificationEmail($toEmail, $tokenId) {
        $this->mail->setFrom('no-reply@example.com', 'IT113 E-Commerce');
        $this->mail->addAddress($toEmail);

        $verificationLink = "http://localhost/user-auth/verifyEmail.php?token=" . $tokenId;

        $this->mail->isHTML(true);
        $this->mail->Subject = 'Verify Your Email Address';
        $this->mail->Body    = "Click the link to verify your email: <a href='$verificationLink'>$verificationLink</a>";

        try {
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
