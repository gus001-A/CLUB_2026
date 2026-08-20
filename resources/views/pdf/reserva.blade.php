<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Reserva - {{ $reserva->folio }}</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        background: #f0f2f5;
        color: #1f2024;
        padding: 40px;
        font-size: 12px;
    }

    .voucher-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .voucher-container {
        background: #f0f0f2;
        border-radius: 16px;
        padding: 20px;
    }

    .voucher {
        max-width: 900px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 16px;
        padding: 40px 45px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    }

    /* ========================================================================
           HEADER - Logo grande a la izquierda con tagline debajo
           ======================================================================== */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        gap: 3rem;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f2;
        min-height: 180px;
    }

    .brand {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        gap: 6px;
        flex-shrink: 0;
    }

    .brand-logo {
        width: 180px;
        height: 180px;
        object-fit: contain;
        display: block;
    }

    .brand .tagline {
        font-size: 10px;
        letter-spacing: 3px;
        color: #C81E3A;
        font-weight: 700;
        text-align: left;
    }

    .title-section {
        text-align: right;
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1;
    }

    .title-section .badge {
        display: inline-block;
        background: #22c55e;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 4px 16px;
        border-radius: 50px;
        letter-spacing: 1px;
        margin-bottom: 8px;
        align-self: flex-end;
    }

    .title-section h1 {
        font-family: 'Georgia', serif;
        font-size: 28px;
        letter-spacing: 1px;
        margin: 0 0 4px;
        color: #1f2024;
    }

    .title-section p {
        font-size: 13px;
        color: #6b6b70;
        margin: 0;
    }

    /* ========================================================================
           LEYENDA SUPERIOR
           ======================================================================== */
    .leyenda-superior {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 12px 18px;
        margin-bottom: 25px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        color: #92400e;
    }

    .leyenda-superior .icono {
        margin-right: 8px;
    }

    /* ========================================================================
           EVENT CARD
           ======================================================================== */
    .event-card {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        border: 1px solid #ececee;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .event-image {
        min-height: 240px;
        background: #f0f0f2;
    }

    .event-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .event-body {
        padding: 20px 24px;
        background: #fafafa;
    }

    .event-body h2 {
        font-size: 20px;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        color: #1f2024;
    }

    .vip-tag {
        background: #C81E3A;
        color: #ffffff;
        font-size: 9px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 4px;
        letter-spacing: 0.5px;
    }

    .event-meta {
        list-style: none;
        margin: 0 0 12px;
        padding: 0;
    }

    .event-meta li {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #2a2a2e;
        padding: 4px 0;
    }

    .event-meta li .icono {
        color: #C81E3A;
        font-weight: 700;
        width: 20px;
        font-size: 14px;
    }

    .event-divider {
        border: none;
        border-top: 1px solid #e8e8ea;
        margin: 12px 0;
    }

    .event-desc {
        font-size: 13px;
        color: #55555a;
        line-height: 1.6;
        margin: 0;
    }

    /* ========================================================================
           DETAILS GRID
           ======================================================================== */
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .detail-card {
        border: 1px solid #ececee;
        border-radius: 12px;
        padding: 20px 24px;
        background: #fafafa;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .detail-card h3 {
        font-size: 12px;
        letter-spacing: 1px;
        color: #C81E3A;
        margin: 0 0 14px;
        padding-bottom: 6px;
        border-bottom: 2px solid #C81E3A;
        display: inline-block;
        font-weight: 700;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-row dt {
        color: #6b6b70;
        font-weight: 400;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .detail-row dt .icono {
        color: #C81E3A;
    }

    .detail-row dd {
        margin: 0;
        font-weight: 600;
        color: #1f2024;
    }

    .badge-acceso {
        background: #fef3c7;
        color: #d97706;
        padding: 2px 10px;
        border-radius: 50px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .payment-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        font-size: 12px;
        color: #6b6b70;
    }

    .payment-row strong {
        color: #1f2024;
        font-weight: 700;
    }

    .payment-divider {
        border: none;
        border-top: 1px dashed #d8d8dc;
        margin: 4px 0;
    }

    .payment-row--total {
        padding-top: 8px;
    }

    .payment-row--total span {
        font-weight: 700;
        font-size: 13px;
        color: #1f2024;
    }

    .payment-row--total strong {
        color: #C81E3A;
        font-size: 24px;
    }

    .payment-row--total small {
        font-size: 12px;
    }

    /* ========================================================================
           ACCESS CARD - QR
           ======================================================================== */
    .access-card {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 25px;
        border: 2px solid #ececee;
        border-radius: 12px;
        padding: 20px 30px;
        margin-bottom: 25px;
        background: linear-gradient(135deg, #fafafa, #f5f5f7);
    }

    .access-left h3 {
        font-size: 13px;
        letter-spacing: 0.5px;
        margin: 0 0 10px;
        color: #1f2024;
    }

    .access-hint {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .access-hint-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #e8e8ea;
        color: #C81E3A;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .access-hint p {
        font-size: 12px;
        color: #55555a;
        margin: 0;
    }

    .qr-code-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .qr-code img {
        width: 150px;
        height: 150px;
        display: block;
        border: 4px solid #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        padding: 8px;
        background: #ffffff;
    }

    .qr-label {
        text-align: center;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px;
        color: #C81E3A;
        margin-top: 4px;
        display: block;
    }

    .folio-right {
        text-align: right;
    }

    .folio-right .label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #6b6b70;
    }

    .folio-right .value {
        font-size: 22px;
        font-weight: 700;
        margin: 4px 0;
        color: #1f2024;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
    }

    .folio-right .note {
        font-size: 11px;
        color: #a5a5aa;
    }

    /* ========================================================================
           LEYENDA INFERIOR
           ======================================================================== */
    .leyenda-inferior {
        background: #f0fdf4;
        border: 2px solid #bbf7d0;
        border-radius: 8px;
        padding: 14px 20px;
        margin-bottom: 25px;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        color: #166534;
    }

    .leyenda-inferior .icono {
        margin-right: 8px;
    }

    /* ========================================================================
           FOOTER
           ======================================================================== */
    .footer {
        display: flex;
        justify-content: center;
        align-items: center;
        border-top: 2px solid #f0f0f2;
        padding-top: 20px;
    }

    .footer-thanks {
        display: flex;
        align-items: center;
        gap: 16px;
        text-align: center;
    }

    .footer-thanks .mark {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #C81E3A, #A6152D);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 16px;
        flex-shrink: 0;
    }

    .footer-thanks p {
        font-size: 14px;
        font-weight: 600;
        color: #1f2024;
        margin: 0 0 4px;
        line-height: 1.4;
    }

    .footer-thanks span {
        font-size: 10px;
        letter-spacing: 2px;
        color: #C81E3A;
        font-weight: 700;
    }

    /* ========================================================================
           RESPONSIVE
           ======================================================================== */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1rem;
            min-height: auto;
        }

        .brand {
            align-items: center;
        }

        .brand .tagline {
            text-align: center;
        }

        .title-section {
            text-align: center;
        }

        .title-section .badge {
            align-self: center;
        }

        .brand-logo {
            width: 140px;
            height: 140px;
        }

        .title-section h1 {
            font-size: 22px;
        }

        .event-card {
            grid-template-columns: 1fr;
        }

        .details-grid {
            grid-template-columns: 1fr;
        }

        .access-card {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .access-hint {
            justify-content: center;
        }

        .folio-right {
            text-align: center;
        }

        .qr-code img {
            width: 120px;
            height: 120px;
        }

        .voucher {
            padding: 24px 20px;
        }

        .voucher-container {
            padding: 10px;
        }

        body {
            padding: 15px;
        }
    }

    @media (max-width: 480px) {
        .voucher {
            padding: 16px;
        }

        .brand-logo {
            width: 100px;
            height: 100px;
        }

        .title-section h1 {
            font-size: 18px;
        }

        .event-body {
            padding: 14px 16px;
        }

        .detail-card {
            padding: 14px 16px;
        }

        .access-card {
            padding: 16px;
        }

        .qr-code img {
            width: 100px;
            height: 100px;
        }

        .folio-right .value {
            font-size: 18px;
        }

        .payment-row--total strong {
            font-size: 20px;
        }

        .footer-thanks {
            flex-direction: column;
        }
    }

    /* ========================================================================
           PRINT OPTIMIZATIONS
           ======================================================================== */
    @media print {
        body {
            background: #ffffff;
            padding: 20px;
            font-size: 10px;
        }

        .voucher-wrapper {
            max-width: 100%;
        }

        .voucher-container {
            background: #ffffff;
            padding: 0;
            border-radius: 0;
        }

        .voucher {
            border: none;
            padding: 20px 30px;
            border-radius: 0;
            box-shadow: none;
        }

        .event-card {
            box-shadow: none;
        }

        .detail-card {
            box-shadow: none;
            background: #ffffff;
        }

        .access-card {
            background: #ffffff;
            border: 1px solid #ddd;
        }

        .qr-code img {
            width: 120px;
            height: 120px;
            border: 2px solid #ddd;
        }

        .brand-logo {
            width: 150px;
            height: 150px;
        }

        .header {
            border-bottom: 2px solid #ddd;
            min-height: 150px;
        }

        .footer {
            border-top: 2px solid #ddd;
        }

        .payment-row--total strong {
            font-size: 20px;
        }

        .title-section h1 {
            font-size: 24px;
        }

        .leyenda-superior {
            background: #fef3c7;
            border: 1px solid #fcd34d;
        }

        .leyenda-inferior {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
        }
    }
    </style>
</head>

<body>
    <div class="voucher-wrapper">
        <div class="voucher-container">
            <div class="voucher">

                <!-- ============================================================ -->
                <!-- HEADER - Logo grande con tagline debajo y título a la derecha -->
                <!-- ============================================================ -->
                <header class="header">
                    <div class="brand">
                        <img src="{{ asset('images/LOGO.png') }}" alt="Logo" class="brand-logo" />
                        <span class="tagline">PRIVADO · EXCLUSIVO · DISCRETO</span>
                    </div>

                    <div class="title-section">
                        <span class="badge">RESERVA CONFIRMADA</span>
                        <h1>COMPROBANTE DE RESERVA</h1>
                        <p>Tu lugar está asegurado para este evento exclusivo.</p>
                    </div>
                </header>

                <!-- ============================================================ -->
                <!-- LEYENDA SUPERIOR -->
                <!-- ============================================================ -->
                <div class="leyenda-superior">
                    <span class="icono">&#9888;</span>
                    <strong>IMPORTANTE:</strong> Este comprobante es personal e intransferible. Debes presentarlo en la
                    entrada del evento para poder ingresar.
                </div>

                <!-- ============================================================ -->
                <!-- EVENT CARD -->
                <!-- ============================================================ -->
                <section class="event-card">
                    <div class="event-image">
                        <img src="{{ $evento->imagen ?? asset('images/eventos/default-event.jpg') }}"
                            alt="{{ $evento->nombre }}" />
                    </div>
                    <div class="event-body">
                        <h2>
                            {{ $evento->nombre }}
                            @if($reserva->tipo_acceso === 'vip')
                            <span class="vip-tag">VIP</span>
                            @endif
                        </h2>
                        <ul class="event-meta">
                            <li><span class="icono">&#128197;</span> {{ $fechaFormateada }}</li>
                            <li><span class="icono">&#128339;</span> {{ $evento->hora_formateada ?? '21:00 hrs' }}</li>
                            <li><span class="icono">&#128205;</span> {{ $evento->ciudad ?? 'Ciudad de México' }}</li>
                            <li><span class="icono">&#128230;</span> <strong>Dirección:</strong>
                                {{ $direccionCompleta ?? $evento->ubicacion ?? 'Locación privada' }}</li>
                        </ul>
                        <hr class="event-divider" />
                        <p class="event-desc">
                            {{ $evento->descripcion ?? 'Experiencia exclusiva para nuestros miembros.' }}</p>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- DETAILS GRID -->
                <!-- ============================================================ -->
                <section class="details-grid">
                    <div class="detail-card">
                        <h3>DETALLES DE LA RESERVA</h3>
                        <dl>
                            <div class="detail-row">
                                <dt><span class="icono">&#128081;</span> Tipo de acceso:</dt>
                                <dd><span class="badge-acceso">{{ strtoupper($reserva->tipo_acceso) }}</span></dd>
                            </div>
                            <div class="detail-row">
                                <dt><span class="icono">&#128101;</span> Perfil:</dt>
                                <dd>{{ $perfilAcompanante }}</dd>
                            </div>
                            <div class="detail-row">
                                <dt><span class="icono">&#128100;</span> Titular:</dt>
                                <dd>{{ $titularNombre }}</dd>
                            </div>
                            @if($nombresAcompanantes && $nombresAcompanantes !== 'Ninguno')
                            <div class="detail-row">
                                <dt><span class="icono">&#128101;</span> Acompañante(s):</dt>
                                <dd>{{ $nombresAcompanantes }}</dd>
                            </div>
                            @endif
                            <div class="detail-row">
                                <dt><span class="icono">&#128101;</span> Total asistentes:</dt>
                                <dd><strong>{{ $reserva->asistentes }}</strong></dd>
                            </div>
                            <div class="detail-row">
                                <dt><span class="icono">&#128179;</span> Método de pago:</dt>
                                <dd>{{ $metodoPago }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="detail-card">
                        <h3>RESUMEN DE PAGO</h3>
                        <div class="payment-row">
                            <span>Precio por persona</span>
                            <strong>${{ number_format($precioUnitario, 0, ',', '.') }} MXN</strong>
                        </div>
                        <div class="payment-row">
                            <span>Cantidad</span>
                            <strong>{{ $reserva->asistentes }}</strong>
                        </div>
                        <hr class="payment-divider" />
                        <div class="payment-row">
                            <span>Subtotal</span>
                            <strong>${{ number_format($subtotal, 0, ',', '.') }} MXN</strong>
                        </div>
                        <div class="payment-row">
                            <span>Cargo por servicio</span>
                            <strong>${{ number_format($cargoServicio, 0, ',', '.') }} MXN</strong>
                        </div>
                        <hr class="payment-divider" />
                        <div class="payment-row payment-row--total">
                            <span>TOTAL PAGADO</span>
                            <strong>${{ number_format($reserva->total, 0, ',', '.') }} <small>MXN</small></strong>
                        </div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- ACCESS CARD - QR -->
                <!-- ============================================================ -->
                <section class="access-card">
                    <div class="access-left">
                        <h3>ACCESO AL EVENTO</h3>
                        <div class="access-hint">
                            <div class="access-hint-icon">&#128241;</div>
                            <p>Presenta este código QR en la entrada del evento.</p>
                        </div>
                    </div>
                    <div class="qr-code-wrapper">
                        <div class="qr-code">
                            <img src="data:image/png;base64,{{ $qrCode }}" alt="Código QR de acceso" />
                        </div>
                        <span class="qr-label">CÓDIGO DE ACCESO</span>
                    </div>
                    <div class="folio-right">
                        <div class="label">FOLIO DE RESERVA</div>
                        <div class="value">{{ $reserva->folio }}</div>
                        <div class="note">Guarda este comprobante.</div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- LEYENDA INFERIOR -->
                <!-- ============================================================ -->
                <div class="leyenda-inferior">
                    <span class="icono">&#10004;</span>
                    <strong>IMPORTANTE:</strong> Es OBLIGATORIO presentar este comprobante (impreso o digital) para
                    poder ingresar al evento. Sin él, no se permitirá el acceso.
                </div>

                <!-- ============================================================ -->
                <!-- FOOTER -->
                <!-- ============================================================ -->
                <footer class="footer">
                    <div class="footer-thanks">
                        <div class="mark">CF</div>
                        <div>
                            <p>Gracias por ser parte de nuestra comunidad exclusiva.</p>
                            <span>PRIVADO · EXCLUSIVO · DISCRETO</span>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</body>

</html>