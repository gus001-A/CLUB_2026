<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tu invitación a Club de Fantasías</title>
</head>
<body style="margin:0;padding:0;background:#FAF8F7;font-family:Arial,Helvetica,sans-serif;color:#171412;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;padding:32px 16px;">
<tr>
<td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFFFF;border-radius:16px;overflow:hidden;border:1px solid #EDE9E7;">

    <!-- Header -->
    <tr>
        <td style="background:linear-gradient(135deg,#C81E3A,#A5182F);padding:32px 32px 28px;text-align:center;">
            <p style="margin:0;color:#FFFFFF;font-size:22px;font-weight:700;letter-spacing:0.02em;">Club de Fantasías</p>
            <p style="margin:6px 0 0;color:#FBE6EA;font-size:13px;">Has sido invitado{{ $tipoLabel !== 'Registro' ? " · Acceso {$tipoLabel}" : '' }}</p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:32px;">
            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                Hola{{ $nombre ? ' ' . $nombre : '' }},
            </p>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#4B4744;">
                Un administrador de <strong>Club de Fantasías</strong> te invitó a registrarte en la plataforma.
                Usa el código de abajo para crear tu cuenta.
            </p>

            @if($mensaje)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;border-radius:12px;margin-bottom:24px;">
                <tr>
                    <td style="padding:16px 18px;font-size:14px;line-height:1.5;color:#4B4744;font-style:italic;">
                        &ldquo;{{ $mensaje }}&rdquo;
                    </td>
                </tr>
            </table>
            @endif

            <!-- Código -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed #EDE9E7;border-radius:12px;margin-bottom:24px;">
                <tr>
                    <td style="padding:20px;text-align:center;">
                        <p style="margin:0 0 4px;font-size:11px;letter-spacing:0.08em;text-transform:uppercase;color:#8A8481;">Tu código de invitación</p>
                        <p style="margin:0;font-size:26px;font-weight:700;letter-spacing:0.08em;color:#C81E3A;">{{ $codigo }}</p>
                    </td>
                </tr>
            </table>

            <!-- Botón -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="padding-bottom:24px;">
                        <a href="{{ $urlRegistro }}" style="display:inline-block;background:#C81E3A;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:600;padding:14px 32px;border-radius:10px;">
                            Crear mi cuenta
                        </a>
                    </td>
                </tr>
            </table>

            @if($expiraEn)
            <p style="margin:0;font-size:13px;line-height:1.5;color:#8A8481;text-align:center;">
                Este código vence el {{ $expiraEn->locale('es')->translatedFormat('d \d\e F \d\e Y') }}.
            </p>
            @endif
        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="padding:20px 32px;border-top:1px solid #EDE9E7;text-align:center;">
            <p style="margin:0;font-size:11px;color:#B7B2AF;">
                Si no esperabas esta invitación, puedes ignorar este correo.
            </p>
        </td>
    </tr>

</table>
</td>
</tr>
</table>
</body>
</html>