<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tu código de verificación</title>
</head>
<body style="margin:0;padding:0;background:#FAF8F7;font-family:Arial,Helvetica,sans-serif;color:#171412;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;padding:32px 16px;">
<tr>
<td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#FFFFFF;border-radius:16px;overflow:hidden;border:1px solid #EDE9E7;">

    <!-- Header -->
    <tr>
        <td style="background:linear-gradient(135deg,#C81E3A,#A5182F);padding:28px 32px;text-align:center;">
            <p style="margin:0;color:#FFFFFF;font-size:20px;font-weight:700;letter-spacing:0.02em;">Club de Fantasías</p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:32px;text-align:center;">
            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#171412;">
                Tu código de verificación es:
            </p>
            <p style="margin:0 0 24px;font-size:13px;line-height:1.5;color:#8A8481;">
                Escríbelo en la página de registro para confirmar tu correo.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed #EDE9E7;border-radius:12px;margin-bottom:24px;">
                <tr>
                    <td style="padding:24px;text-align:center;">
                        <p style="margin:0;font-size:36px;font-weight:800;letter-spacing:0.2em;color:#C81E3A;">{{ $codigo }}</p>
                    </td>
                </tr>
            </table>

            <p style="margin:0;font-size:13px;line-height:1.5;color:#8A8481;">
                Este código vence en 10 minutos. Si tú no lo pediste, puedes ignorar este correo.
            </p>
        </td>
    </tr>

</table>
</td>
</tr>
</table>
</body>
</html>