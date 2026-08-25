<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tu cuenta en Club de Fantasías</title>
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
            <p style="margin:6px 0 0;color:#FBE6EA;font-size:13px;">Tu cuenta ya está creada</p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:32px;">
            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                Hola{{ $nombre ? ' ' . $nombre : '' }},
            </p>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#4B4744;">
                Un administrador creó una cuenta para ti en <strong>Club de Fantasías</strong>.
                Estos son tus datos de acceso:
            </p>

            <!-- Credenciales -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;border-radius:12px;margin-bottom:24px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <p style="margin:0 0 10px;font-size:13px;color:#8A8481;">
                            Usuario: <strong style="color:#171412;">{{ $apodo }}</strong>
                        </p>
                        <p style="margin:0;font-size:13px;color:#8A8481;">
                            Contraseña: <strong style="color:#171412;">{{ $password }}</strong>
                        </p>
                    </td>
                </tr>
            </table>

            <p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#4B4744;">
                Por seguridad, la primera vez que inicies sesión te vamos a pedir confirmar
                tu correo con este código:
            </p>

            <!-- Código -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed #EDE9E7;border-radius:12px;margin-bottom:24px;">
                <tr>
                    <td style="padding:20px;text-align:center;">
                        <p style="margin:0;font-size:32px;font-weight:800;letter-spacing:0.2em;color:#C81E3A;">{{ $codigo }}</p>
                    </td>
                </tr>
            </table>

            <!-- Botón -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="padding-bottom:16px;">
                        <a href="{{ $urlLogin }}" style="display:inline-block;background:#C81E3A;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:600;padding:14px 32px;border-radius:10px;">
                            Iniciar sesión
                        </a>
                    </td>
                </tr>
            </table>

            <p style="margin:0;font-size:13px;line-height:1.5;color:#8A8481;text-align:center;">
                Te recomendamos cambiar tu contraseña una vez que entres.
            </p>
        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="padding:20px 32px;border-top:1px solid #EDE9E7;text-align:center;">
            <p style="margin:0;font-size:11px;color:#B7B2AF;">
                Si no esperabas este correo, contacta a soporte de Club de Fantasías.
            </p>
        </td>
    </tr>

</table>
</td>
</tr>
</table>
</body>
</html>