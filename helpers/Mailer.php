<?php
require_once __DIR__ . '/../config/env.php';

class Mailer
{

  public static function enviarCodigoVerificacion(string $destinatario, string $nombre, string $codigo): array
  {
    $asunto = "Tu código de verificación en Gavel.net: {$codigo}";
    $cuerpoHtml = self::generarPlantillaCodigo($nombre, $codigo);

    return self::enviar($destinatario, $nombre, $asunto, $cuerpoHtml);
  }


  public static function enviar(string $para, string $nombrePara, string $asunto, string $html): array
  {
    $driver = defined('MAIL_DRIVER') ? MAIL_DRIVER : 'dev';

    if ($driver === 'dev' || empty(SMTP_USER) || SMTP_PASS === 'tu_contraseña_aqui') {
      self::logDevEmail($para, $asunto, $html);
      return [
        'exito' => true,
        'modo'  => 'dev',
        'mensaje' => 'Correo registrado en modo desarrollo/simulación.'
      ];
    }

    // Modo SMTP 
    try {
      self::enviarViaSmtpSocket($para, $nombrePara, $asunto, $html);
      return [
        'exito' => true,
        'modo'  => 'smtp',
        'mensaje' => 'Correo enviado exitosamente vía SMTP.'
      ];
    } catch (Throwable $e) {
      error_log("[Mailer Error] No se pudo enviar correo a {$para}: " . $e->getMessage());
      return [
        'exito' => false,
        'modo'  => 'smtp',
        'mensaje' => 'Error al enviar correo por SMTP: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Cliente SMTP nativo sobre socket con autenticación y cifrado SSL/TLS.
   * Diseñado para máxima compatibilidad con cPanel Exim/Postfix sin dependencias pesadas.
   */
  private static function enviarViaSmtpSocket(string $para, string $nombrePara, string $asunto, string $html): void
  {
    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? SMTP_PORT : 465;
    $secure = defined('SMTP_SECURE') ? strtolower(SMTP_SECURE) : 'ssl';
    $user = SMTP_USER;
    $pass = SMTP_PASS;
    $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : $user;
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Gavel.net';

    $socketHost = ($secure === 'ssl') ? "ssl://{$host}" : $host;
    $timeout = 15;

    $socket = @fsockopen($socketHost, $port, $errno, $errstr, $timeout);
    if (!$socket) {
      throw new Exception("No se pudo conectar al servidor SMTP {$host}:{$port} ({$errno} - {$errstr})");
    }

    stream_set_timeout($socket, $timeout);

    self::leerRespuestaSmtp($socket, 220);
    self::enviarComandoSmtp($socket, "EHLO " . gethostname(), 250);

    if ($secure === 'tls') {
      self::enviarComandoSmtp($socket, "STARTTLS", 220);
      if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        throw new Exception("Fallo al iniciar cifrado TLS");
      }
      self::enviarComandoSmtp($socket, "EHLO " . gethostname(), 250);
    }

    self::enviarComandoSmtp($socket, "AUTH LOGIN", 334);
    self::enviarComandoSmtp($socket, base64_encode($user), 334);
    self::enviarComandoSmtp($socket, base64_encode($pass), 235);

    self::enviarComandoSmtp($socket, "MAIL FROM:<{$fromEmail}>", 250);
    self::enviarComandoSmtp($socket, "RCPT TO:<{$para}>", 250);

    self::enviarComandoSmtp($socket, "DATA", 354);

    $boundary = "==_Multipart_Boundary_" . md5(uniqid(microtime(), true));
    $asuntoEncoded = "=?UTF-8?B?" . base64_encode($asunto) . "?=";
    $fromNameEncoded = "=?UTF-8?B?" . base64_encode($fromName) . "?=";

    $cabeceras = [];
    $cabeceras[] = "Date: " . date('r');
    $cabeceras[] = "From: {$fromNameEncoded} <{$fromEmail}>";
    $cabeceras[] = "To: <{$para}>";
    $cabeceras[] = "Subject: {$asuntoEncoded}";
    $cabeceras[] = "MIME-Version: 1.0";
    $cabeceras[] = "Content-Type: text/html; charset=UTF-8";
    $cabeceras[] = "Content-Transfer-Encoding: base64";

    $mensajeData = implode("\r\n", $cabeceras) . "\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n.\r\n";

    fwrite($socket, $mensajeData);
    self::leerRespuestaSmtp($socket, 250);

    fwrite($socket, "QUIT\r\n");
    fclose($socket);
  }

  private static function enviarComandoSmtp($socket, string $cmd, int $esperado): string
  {
    fwrite($socket, $cmd . "\r\n");
    return self::leerRespuestaSmtp($socket, $esperado);
  }

  private static function leerRespuestaSmtp($socket, int $esperado): string
  {
    $respuesta = "";
    while ($linea = fgets($socket, 515)) {
      $respuesta .= $linea;
      if (isset($linea[3]) && $linea[3] === ' ') {
        break;
      }
    }
    $codigo = (int)substr($respuesta, 0, 3);
    if ($codigo !== $esperado) {
      throw new Exception("Respuesta SMTP inesperada: {$respuesta} (esperado: {$esperado})");
    }
    return $respuesta;
  }

  private static function logDevEmail(string $para, string $asunto, string $html): void
  {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
      @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/mail.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] PARA: {$para} | ASUNTO: {$asunto}\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);
  }


  private static function generarPlantillaCodigo(string $nombre, string $codigo): string
  {
    $nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
    $codigoSafe = htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verifica tu cuenta</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f5f7; padding: 40px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="520" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; border: 1px solid #e1e4e8; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
          
          <tr>
            <td style="padding: 28px 32px 20px 32px; border-bottom: 1px solid #edf0f2;">
              <span style="font-size: 22px; font-weight: 700; color: #111111; letter-spacing: -0.5px;">Gavel<span style="color: #658807;">.net</span></span>
            </td>
          </tr>

          <tr>
            <td style="padding: 32px; text-align: left;">
              <h2 style="margin: 0 0 16px 0; color: #111111; font-size: 20px; font-weight: 600;">Código de verificación</h2>
              
              <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 15px; line-height: 1.6;">
                Hola <strong>{$nombreSafe}</strong>, utiliza el siguiente código para verificar tu correo electrónico y completar tu registro:
              </p>

              <div style="margin: 28px 0; padding: 18px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; text-align: center;">
                <span style="font-size: 34px; font-weight: 700; letter-spacing: 8px; color: #1a202c; font-family: monospace;">{$codigoSafe}</span>
              </div>

              <p style="margin: 0 0 8px 0; color: #718096; font-size: 13px; line-height: 1.5;">
                Este código es válido durante <strong>15 minutos</strong>. Si no solicitaste este registro en Gavel.net, puedes ignorar este mensaje de forma segura.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding: 20px 32px; background-color: #fafbfc; border-top: 1px solid #edf0f2; text-align: left; color: #a0aec0; font-size: 12px; line-height: 1.4;">
              Gavel.net — Plataforma de subastas en línea.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
  }
}
