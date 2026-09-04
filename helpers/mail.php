<?php
/**
 * Email Helper Utility (Hostinger SMTP & Native Fallback)
 * Biswas Enterprise E-Commerce
 */

if (!function_exists('sendSmtpMail')) {
    /**
     * Send HTML email using direct SMTP socket connection (Hostinger / custom SMTP)
     */
    function sendSmtpMail(string $to, string $subject, string $htmlBody, string $replyToEmail = '', string $replyToName = ''): bool {
        $host = env('MAIL_HOST');
        $port = (int)env('MAIL_PORT', 465);
        $user = env('MAIL_USERNAME');
        $pass = env('MAIL_PASSWORD');

        if (empty($host) || empty($user) || empty($pass)) {
            return false;
        }

        $socketHost = ($port == 465) ? 'ssl://' . $host : $host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client($socketHost . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            error_log("SMTP Connection Error: $errstr ($errno)");
            return false;
        }

        $read = function() use ($socket) {
            $response = '';
            while ($line = fgets($socket, 515)) {
                $response .= $line;
                if (substr($line, 3, 1) == ' ') break;
            }
            return $response;
        };

        $write = function($cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $read(); // banner
        $write("EHLO " . gethostname());
        $read();

        if ($port == 587) {
            $write("STARTTLS");
            $read();
            @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write("EHLO " . gethostname());
            $read();
        }

        $write("AUTH LOGIN");
        $read();
        $write(base64_encode($user));
        $read();
        $write(base64_encode($pass));
        $authResp = $read();

        if (substr($authResp, 0, 3) !== '235') {
            error_log("SMTP Auth Failed: " . trim($authResp));
            fclose($socket);
            return false;
        }

        $write("MAIL FROM: <" . $user . ">");
        $read();
        $write("RCPT TO: <" . $to . ">");
        $read();
        $write("DATA");
        $read();

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Biswas Enterprise System <" . $user . ">\r\n";
        $headers .= "To: <" . $to . ">\r\n";
        if (!empty($replyToEmail)) {
            $headers .= "Reply-To: " . (!empty($replyToName) ? $replyToName . " " : "") . "<" . $replyToEmail . ">\r\n";
        }
        $headers .= "Subject: " . $subject . "\r\n";

        $write($headers . "\r\n" . $htmlBody . "\r\n.");
        $dataResp = $read();

        $write("QUIT");
        fclose($socket);

        return (substr($dataResp, 0, 3) === '250');
    }
}

if (!function_exists('sendEnquiryAdminNotification')) {

    /**
     * Send email notification to Admin when a new bulk enquiry is submitted.
     * 
     * @param array $data
     * @return bool
     */
    function sendEnquiryAdminNotification(array $data): bool {
        $adminEmail = env('ADMIN_EMAIL', 'admin@biswas-enterprise.in');
        $siteName   = env('APP_NAME', 'Biswas Enterprise');

        $enquiryId  = $data['enquiry_id'] ?? 'N/A';
        $fullName   = htmlspecialchars($data['full_name'] ?? 'N/A');
        $email      = htmlspecialchars($data['email'] ?? 'N/A');
        $phone      = htmlspecialchars($data['phone'] ?? 'N/A');
        $product    = htmlspecialchars($data['product_name'] ?? 'General Enquiry');
        $quantity   = htmlspecialchars(!empty($data['quantity']) ? $data['quantity'] : 'Not Specified');
        $destination = htmlspecialchars(!empty($data['destination']) ? $data['destination'] : 'Not Specified');
        $details    = nl2br(htmlspecialchars($data['requirement_details'] ?? 'No additional details provided.'));
        date_default_timezone_set('Asia/Kolkata');
        $dateStr    = date('d M Y, h:i A');

        $subject = "New Bulk Wholesale Enquiry #" . $enquiryId . " - " . $fullName;

        // Modern HTML Email Template
        $htmlContent = '
        <!DOCTYPE html>
