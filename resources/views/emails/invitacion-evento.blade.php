<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $eventoNombre }} — Club de Fantasías</title>
</head>
<body style="margin:0;padding:0;background:#FAF8F7;font-family:Arial,Helvetica,sans-serif;color:#171412;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;padding:32px 16px;">
<tr>
<td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFFFF;border-radius:16px;overflow:hidden;border:1px solid #EDE9E7;">

    <!-- Header -->
    <tr>
        <td style="background:linear-gradient(135deg,#C81E3A,#A5182F);padding:28px 32px 24px;text-align:center;">
            <p style="margin:0;color:#FBE6EA;font-size:12px;text-transform:uppercase;letter-spacing:0.08em;">Invitación exclusiva</p>
            <p style="margin:6px 0 0;color:#FFFFFF;font-size:22px;font-weight:700;">{{ $eventoNombre }}</p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:32px;">
            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                Hola{{ $nombre ? ' ' . $nombre : '' }},
            </p>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#4B4744;">
                Un administrador de <strong>Club de Fantasías</strong> te invitó a este evento. Entra a la
                ficha del evento para ver todos los detalles y reservar tu lugar.
            </p>

            @if($mensaje)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;border-radius:12px;margin-bottom:20px;">
                <tr>
                    <td style="padding:16px 18px;font-size:14px;line-height:1.5;color:#4B4744;font-style:italic;">
                        &ldquo;{{ $mensaje }}&rdquo;
                    </td>
                </tr>
            </table>
            @endif

            <!-- Detalles del evento -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF8F7;border-radius:12px;margin-bottom:24px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:4px 0;font-size:13px;color:#8A8481;width:90px;">Fecha</td>
                                <td style="padding:4px 0;font-size:13px;color:#171412;font-weight:600;">{{ $eventoFecha }}{{ $eventoHora ? ' · ' . $eventoHora : '' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;font-size:13px;color:#8A8481;">Ciudad</td>
                                <td style="padding:4px 0;font-size:13px;color:#171412;font-weight:600;">{{ $eventoCiudad ?? 'Por confirmar' }}</td>
                            </tr>
                            @if($eventoZona)
                            <tr>
                                <td style="padding:4px 0;font-size:13px;color:#8A8481;">Zona</td>
                                <td style="padding:4px 0;font-size:13px;color:#171412;font-weight:600;">{{ $eventoZona }}</td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Botón -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <a href="{{ $urlEvento }}" style="display:inline-block;background:#C81E3A;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:600;padding:14px 32px;border-radius:10px;">
                            Ver evento y reservar
                        </a>
                    </td>
                </tr>
            </table>
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