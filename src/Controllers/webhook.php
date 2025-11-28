// <?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

// require "../vendor/autoload.php";

// $mail = new PHPMailer(true);

// try {
//     // 1. Server Settings (Configured with Dotenv)
//     $mail->isSMTP();
//     $mail->Host = $_ENV["SMTP_HOST"]; // e.g., smtp.gmail.com
//     $mail->SMTPAuth = true;
//     $mail->Username = $_ENV["SMTP_USER"];
//     $mail->Password = $_ENV["SMTP_PASS"];
//     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//     $mail->Port = 587;

//     // 2. Recipients
//     $mail->setFrom("no-reply@bonten.com", "Bonten Events");
//     $mail->addAddress("customer@example.com", "Jerome");

//     // 3. Content
//     $mail->isHTML(true);
//     $mail->Subject = "Booking Confirmed";
//     $mail->Body = "<b>Your booking is confirmed!</b>";

//     $mail->send();
//     echo "Message has been sent";
// } catch (Exception $e) {
//     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
// }
