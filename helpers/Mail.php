<?php





class Mail
{
    








    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        
        $logDir = BASE_PATH . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'mail.log';

        $logEntry = "[" . date('Y-m-d H:i:s') . "] TO: {$toName} <{$toEmail}>\n"
                  . "SUBJECT: {$subject}\n"
                  . "BODY:\n" . strip_tags($htmlBody) . "\n"
                  . str_repeat('-', 60) . "\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);

        
        if (defined('SMTP_HOST') && !empty(SMTP_HOST) && defined('SMTP_USER') && !empty(SMTP_USER)) {
            $smtpSuccess = self::sendViaSmtp($toEmail, $toName, $subject, $htmlBody);
            if ($smtpSuccess) {
                return true;
            }
        }

        
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: Content Planner TVRI <no-reply@tvri-jatim.id>';
        $headers[] = "Reply-To: no-reply@tvri-jatim.id";
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $headerStr = implode("\r\n", $headers);

        try {
            return @mail($toEmail, $subject, $htmlBody, $headerStr);
        } catch (Throwable $e) {
            return false;
        }
    }

    


    private static function sendViaSmtp(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : 587;
        $user = defined('SMTP_USER') ? SMTP_USER : '';
        $pass = defined('SMTP_PASS') ? str_replace(' ', '', SMTP_PASS) : '';
        $from = defined('SMTP_FROM') ? SMTP_FROM : $user;
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Content Planner TVRI';

        if (empty($host) || empty($user) || empty($pass)) {
            return false;
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            return false;
        }

        $readResponse = function() use ($socket) {
            $response = '';
            while ($line = @fgets($socket, 512)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            return $response;
        };

        $sendCommand = function(string $cmd) use ($socket, $readResponse) {
            @fputs($socket, $cmd . "\r\n");
            return $readResponse();
        };

        
        $readResponse();

        
        $res = $sendCommand('EHLO ' . gethostname());
        if (!str_starts_with($res, '250')) {
            @fclose($socket);
            return false;
        }

        
        if ($port === 587) {
            $res = $sendCommand('STARTTLS');
            if (!str_starts_with($res, '220')) {
                @fclose($socket);
                return false;
            }

            
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }
            if (!@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                @fclose($socket);
                return false;
            }

            
            $sendCommand('EHLO ' . gethostname());
        }

        
        $res = $sendCommand('AUTH LOGIN');
        if (!str_starts_with($res, '334')) {
            @fclose($socket);
            return false;
        }

        $res = $sendCommand(base64_encode($user));
        if (!str_starts_with($res, '334')) {
            @fclose($socket);
            return false;
        }

        $res = $sendCommand(base64_encode($pass));
        if (!str_starts_with($res, '235')) {
            @fclose($socket);
            return false;
        }

        
        $res = $sendCommand("MAIL FROM: <{$from}>");
        if (!str_starts_with($res, '250')) {
            @fclose($socket);
            return false;
        }

        
        $res = $sendCommand("RCPT TO: <{$toEmail}>");
        if (!str_starts_with($res, '250')) {
            @fclose($socket);
            return false;
        }

        
        $res = $sendCommand('DATA');
        if (!str_starts_with($res, '354')) {
            @fclose($socket);
            return false;
        }

        
        $domain = 'gmail.com';
        if (str_contains($from, '@')) {
            $parts = explode('@', $from);
            $domain = end($parts);
        }
        $msgId = '<' . date('YmdHis') . '.' . bin2hex(random_bytes(6)) . '@' . $domain . '>';

        
        $boundary = 'b1_' . md5(uniqid((string)time(), true));

        
        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], ["\n", "\n", "\n", "\n\n", "\n\n"], $htmlBody));
        $plainText = trim(preg_replace("/\n\s+\n/", "\n\n", $plainText));

        
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $headers[] = 'From: "' . addslashes($fromName) . '" <' . $from . '>';
        $headers[] = 'Reply-To: "' . addslashes($fromName) . '" <' . $from . '>';
        $headers[] = 'To: "' . addslashes($toName) . '" <' . $toEmail . '>';
        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'Message-ID: ' . $msgId;
        $headers[] = 'Auto-Submitted: auto-generated';
        $headers[] = 'X-Auto-Response-Suppress: All';
        $headers[] = 'X-Report-Abuse-To: <' . $from . '>';

        
        $body  = "--" . $boundary . "\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $plainText . "\r\n\r\n";
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        $body .= "--" . $boundary . "--";

        $emailData = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $res = $sendCommand($emailData);

        
        $sendCommand('QUIT');
        @fclose($socket);

        return str_starts_with($res, '250');
    }

    


    public static function sendOtp(string $toEmail, string $toName, string $otpCode): bool
    {
        $subject = "Kode Verifikasi Pendaftaran Akun - TVRI Content Planner";
        
        $htmlBody = '
        <div style="font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #ffffff;">
            <div style="background-color: #003399; padding: 20px; text-align: center; color: #ffffff;">
                <h2 style="margin: 0; font-size: 20px;">Content Planner TVRI Jatim</h2>
            </div>
            <div style="padding: 24px; color: #333333; line-height: 1.6;">
                <p>Halo <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                <p>Terima kasih telah melakukan pendaftaran akun user magang. Gunakan kode verifikasi (OTP) berikut untuk melanjutkan proses registrasi:</p>
                <div style="text-align: center; margin: 25px 0;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #003399; background: #f0f4ff; padding: 12px 24px; border-radius: 8px; border: 1px dashed #003399; display: inline-block;">' . htmlspecialchars($otpCode) . '</span>
                </div>
                <p style="font-size: 13px; color: #666666;">Kode verifikasi ini berlaku selama <strong>15 menit</strong>. Jangan berikan kode ini kepada siapapun demi keamanan.</p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="font-size: 12px; color: #888888; margin: 0;">Pesan ini dikirimkan secara otomatis oleh sistem TVRI Content Planner. Jangan membalas email ini.</p>
            </div>
        </div>';

        return self::send($toEmail, $toName, $subject, $htmlBody);
    }

    


    public static function sendPasswordResetOtp(string $toEmail, string $toName, string $otpCode): bool
    {
        $subject = "Kode Verifikasi Reset Password - TVRI Content Planner";

        $htmlBody = '
        <div style="font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #ffffff;">
            <div style="background-color: #003399; padding: 20px; text-align: center; color: #ffffff;">
                <h2 style="margin: 0; font-size: 20px;">Content Planner TVRI Jatim</h2>
            </div>
            <div style="padding: 24px; color: #333333; line-height: 1.6;">
                <p>Halo <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                <p>Kami menerima permintaan untuk mengatur ulang password akun Anda. Gunakan kode verifikasi (OTP) berikut untuk melanjutkan proses reset password:</p>
                <div style="text-align: center; margin: 25px 0;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #003399; background: #f0f4ff; padding: 12px 24px; border-radius: 8px; border: 1px dashed #003399; display: inline-block;">' . htmlspecialchars($otpCode) . '</span>
                </div>
                <p style="font-size: 13px; color: #666666;">Kode verifikasi ini berlaku selama <strong>15 menit</strong>. Jangan berikan kode ini kepada siapapun demi keamanan.</p>
                <p style="font-size: 13px; color: #666666;">Jika Anda tidak meminta reset password, abaikan email ini dan password Anda tetap aman.</p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="font-size: 12px; color: #888888; margin: 0;">Pesan ini dikirimkan secara otomatis oleh sistem TVRI Content Planner. Jangan membalas email ini.</p>
            </div>
        </div>';

        return self::send($toEmail, $toName, $subject, $htmlBody);
    }

    


    public static function sendApprovalNotification(string $toEmail, string $toName, string $username): bool
    {
        $subject = "Konfirmasi Akun Magang - TVRI Content Planner";
        $loginUrl = BASE_URL . '/login';

        $htmlBody = '
        <div style="font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #ffffff;">
            <div style="background-color: #003399; padding: 20px; text-align: center; color: #ffffff;">
                <h2 style="margin: 0; font-size: 20px;">Content Planner TVRI Jatim</h2>
            </div>
            <div style="padding: 24px; color: #333333; line-height: 1.6;">
                <h3 style="color: #2e7d32; margin-top: 0;">Pendaftaran Akun Dikonfirmasi</h3>
                <p>Halo <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                <p>Selamat! Pendaftaran akun magang Anda telah <strong>dikonfirmasi</strong> oleh Admin. Akun Anda kini telah aktif dan dapat digunakan untuk masuk ke sistem.</p>
                <div style="background: #f8f9fa; border-left: 4px solid #2e7d32; padding: 12px 16px; margin: 20px 0; border-radius: 4px;">
                    <p style="margin: 0; font-size: 14px;"><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
                    <p style="margin: 5px 0 0 0; font-size: 14px;"><strong>Status:</strong> Aktif</p>
                </div>
                <div style="text-align: center; margin: 25px 0;">
                    <a href="' . $loginUrl . '" style="background-color: #003399; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Masuk ke Aplikasi</a>
                </div>
                <p style="font-size: 13px; color: #666666;">Jika tombol di atas tidak bekerja, salin dan buka URL berikut di browser Anda:<br><a href="' . $loginUrl . '" style="color: #003399;">' . $loginUrl . '</a></p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="font-size: 12px; color: #888888; margin: 0;">TVRI Jawa Timur - Content Planner System</p>
            </div>
        </div>';

        return self::send($toEmail, $toName, $subject, $htmlBody);
    }

    


    public static function sendRejectionNotification(string $toEmail, string $toName, ?string $reason = null): bool
    {
        $subject = "Pemberitahuan Status Pendaftaran Akun - TVRI Content Planner";

        $reasonText = !empty($reason) ? htmlspecialchars($reason) : 'Pendaftaran tidak memenuhi kriteria persyaratan yang ditentukan.';

        $htmlBody = '
        <div style="font-family: Arial, sans-serif; max-width: 550px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: #ffffff;">
            <div style="background-color: #d32f2f; padding: 20px; text-align: center; color: #ffffff;">
                <h2 style="margin: 0; font-size: 20px;">Content Planner TVRI Jatim</h2>
            </div>
            <div style="padding: 24px; color: #333333; line-height: 1.6;">
                <h3 style="color: #d32f2f; margin-top: 0;">Pendaftaran Akun Ditolak</h3>
                <p>Halo <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                <p>Mohon maaf, pendaftaran akun magang Anda <strong>tidak dapat dikonfirmasi</strong> oleh Admin saat ini.</p>
                <div style="background: #ffebee; border-left: 4px solid #d32f2f; padding: 12px 16px; margin: 20px 0; border-radius: 4px;">
                    <p style="margin: 0; font-size: 14px; color: #c62828;"><strong>Catatan/Alasan Admin:</strong></p>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #333333;">' . $reasonText . '</p>
                </div>
                <p style="font-size: 13px; color: #666666;">Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi tim penanggung jawab magang TVRI Jawa Timur.</p>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                <p style="font-size: 12px; color: #888888; margin: 0;">TVRI Jawa Timur - Content Planner System</p>
            </div>
        </div>';

        return self::send($toEmail, $toName, $subject, $htmlBody);
    }
}
