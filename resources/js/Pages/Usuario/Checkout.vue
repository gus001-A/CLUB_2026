<template>

    <Head :title="`Checkout | Club de Fantasías`" />

    <AppLayout active-nav="shop">
        <div class="checkout-page">
            <!-- ============================================================ -->
            <!-- HEADER -->
            <!-- ============================================================ -->
            <div class="page-header">
                <button class="btn-back" @click="volverAlCarrito">
                    <i class="pi pi-arrow-left"></i>
                    <span>Volver al carrito</span>
                </button>
                <div class="page-header__badges">
                    <span class="badge badge--secure">
                        <i class="pi pi-lock"></i> Pago seguro
                    </span>
                    <span class="badge badge--trust">
                        <i class="pi pi-shield"></i> Compra protegida
                    </span>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- BARRA DE NAVEGACIÓN - MEJORADA Y MÁS PRO -->
            <!-- ============================================================ -->
            <div class="steps-navigation">
                <div v-for="(paso, index) in pasos" :key="paso.id" class="step-nav" :class="{
                    'step-nav--active': pasoActivo === paso.id,
                    'step-nav--completed': paso.id < pasoActivo,
                    'step-nav--clickable': paso.id < pasoActivo
                }" @click="paso.id < pasoActivo && irAlPaso(paso.id)">
                    <div class="step-nav__indicator">
                        <div class="step-nav__circle">
                            <span v-if="paso.id < pasoActivo" class="step-nav__check">
                                <i class="pi pi-check"></i>
                            </span>
                            <span v-else class="step-nav__number">{{ index + 1 }}</span>
                        </div>
                        <div class="step-nav__connector" v-if="index < pasos.length - 1">
                            <div class="connector-line" :class="{ 'connector-line--active': paso.id <= pasoActivo }">
                            </div>
                        </div>
                    </div>
                    <div class="step-nav__content">
                        <span class="step-nav__label">{{ paso.label }}</span>
                        <span class="step-nav__sub">{{ paso.sub }}</span>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- PASO 1: DIRECCIÓN DE ENVÍO -->
            <!-- ============================================================ -->
            <section v-show="pasoActivo === 1" class="content-grid">
                <div class="main-column">
                    <div class="panel panel--step1">
                        <div class="panel__header">
                            <div class="panel__header-icon">
                                <i class="pi pi-map-marker"></i>
                            </div>
                            <div>
                                <span class="panel__step">Paso 1 de 3</span>
                                <h2>Dirección de envío</h2>
                                <p class="panel__sub">Ingresa los datos donde quieres recibir tu pedido</p>
                            </div>
                        </div>

                        <div class="panel__body">
                            <div class="address-form">
                                <div class="field-row">
                                    <div class="field">
                                        <label>Nombre completo <em class="req">*</em></label>
                                        <div class="input input--blue">
                                            <i class="pi pi-user"></i>
                                            <input v-model="direccionForm.destinatario" type="text"
                                                placeholder="Nombre completo" />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>Teléfono <em class="req">*</em></label>
                                        <div class="input input--green">
                                            <i class="pi pi-phone"></i>
                                            <input v-model="direccionForm.telefono" type="tel"
                                                placeholder="55 1234 5678" maxlength="10" @input="formatearTelefono" />
                                        </div>
                                    </div>
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label>Calle y número <em class="req">*</em></label>
                                        <div class="input input--red">
                                            <i class="pi pi-home"></i>
                                            <input v-model="direccionForm.calle" type="text"
                                                placeholder="Calle, número exterior e interior" />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>Colonia <em class="req">*</em></label>
                                        <div class="input input--purple">
                                            <i class="pi pi-map"></i>
                                            <input v-model="direccionForm.colonia" type="text"
                                                placeholder="Nombre de la colonia" />
                                        </div>
                                    </div>
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label>Ciudad <em class="req">*</em></label>
                                        <div class="input input--gold">
                                            <i class="pi pi-building"></i>
                                            <input v-model="direccionForm.ciudad" type="text" placeholder="Ciudad" />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>Estado <em class="req">*</em></label>
                                        <div class="input input--pink">
                                            <i class="pi pi-flag"></i>
                                            <input v-model="direccionForm.estado" type="text" placeholder="Estado" />
                                        </div>
                                    </div>
                                </div>

                                <div class="field-row">
                                    <div class="field">
                                        <label>Código postal <em class="req">*</em></label>
                                        <div class="input input--red">
                                            <i class="pi pi-hashtag"></i>
                                            <input v-model="direccionForm.codigo_postal" type="text"
                                                placeholder="Código postal" maxlength="5"
                                                @input="formatearCodigoPostal" />
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label>Referencias <em class="opt">(opcional)</em></label>
                                        <div class="input input--purple">
                                            <i class="pi pi-align-left"></i>
                                            <input v-model="direccionForm.referencias" type="text"
                                                placeholder="Calle entre, puntos de referencia" />
                                        </div>
                                    </div>
                                </div>

                                <div class="address-form__footer">
                                    <span class="address-form__note">
                                        <i class="pi pi-info-circle"></i>
                                        El país de envío es <strong>México</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="consent-actions-row">
                                <div class="consent-section">
                                    <label class="consent consent--red">
                                        <input type="checkbox" v-model="form.terminos" />
                                        <span class="consent__box"></span>
                                        <span>Acepto los <Link href="/terminos" class="consent__link">Términos y
                                                Condiciones</Link></span>
                                    </label>
                                </div>

                                <div class="actions-section">
                                    <button class="btn btn--primary btn--large" @click="siguientePaso"
                                        :disabled="!puedeContinuarPaso1 || isSubmitting">
                                        <span v-if="isSubmitting" class="btn__loading">
                                            <i class="pi pi-spin pi-spinner"></i> Procesando...
                                        </span>
                                        <span v-else>
                                            Continuar al pago <i class="pi pi-arrow-right"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="resume-card">
                        <div class="resume-card__header">
                            <span class="resume-card__title">
                                <i class="pi pi-receipt"></i> Resumen del pedido
                            </span>
                            <span class="resume-card__badge">{{ carritoItems.length }} producto{{ carritoItems.length >
                                1 ? 's' : '' }}</span>
                        </div>

                        <div class="resume-card__items">
                            <div v-for="item in carritoItems" :key="item.id" class="cart-item-mini">
                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                <div class="cart-item-mini__info">
                                    <strong>{{ item.nombre }}</strong>
                                    <span>{{ item.cantidad }} × ${{ formatoMoneda(item.precio) }}</span>
                                </div>
                                <span class="cart-item-mini__price">${{ formatoMoneda(item.precio * item.cantidad)
                                }}</span>
                            </div>
                        </div>

                        <div class="resume-card__divider"></div>

                        <div class="resume-card__totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>${{ formatoMoneda(subtotal) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Envío</span>
                                <span :class="{ 'text-success': envioGratis }">
                                    {{ envioGratis ? 'Gratis' : '$' + formatoMoneda(costoEnvio) }}
                                </span>
                            </div>
                            <div class="total-row total-row--grand">
                                <span>Total</span>
                                <strong>${{ formatoMoneda(total) }}</strong>
                            </div>
                        </div>

                        <div class="resume-card__footer">
                            <div class="shipping-info">
                                <i class="pi pi-map-marker" style="color: var(--brand);"></i>
                                <span v-if="direccionForm.calle && direccionForm.colonia">
                                    {{ direccionForm.calle }}, {{ direccionForm.colonia }}, {{ direccionForm.ciudad }}
                                </span>
                                <span v-else class="shipping-info__empty">
                                    <i class="pi pi-info-circle"></i> Completa la dirección
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card info-card--privacy">
                        <div class="privacy-icon">
                            <i class="pi pi-shield"></i>
                        </div>
                        <div>
                            <strong>Privacidad garantizada</strong>
                            <p>Tus datos están protegidos y no se compartirán con terceros.</p>
                        </div>
                    </div>
                </aside>
            </section>

            <!-- ============================================================ -->
            <!-- PASO 2: MÉTODO DE PAGO -->
            <!-- ============================================================ -->
            <section v-show="pasoActivo === 2" class="content-grid">
                <div class="main-column">
                    <div class="panel panel--step2">
                        <div class="panel__header">
                            <div class="panel__header-icon">
                                <i class="pi pi-credit-card"></i>
                            </div>
                            <div>
                                <span class="panel__step">Paso 2 de 3</span>
                                <h2>Método de pago</h2>
                                <p class="panel__sub">Selecciona cómo quieres pagar tu pedido</p>
                            </div>
                        </div>

                        <div class="panel__body">
                            <div class="payment-methods">
                                <label class="payment-methods__label">Método de pago</label>
                                <div class="payment-methods__grid">
                                    <button class="payment-method"
                                        :class="{ 'payment-method--active': metodoPago === 'tarjeta' }"
                                        @click="metodoPago = 'tarjeta'">
                                        <i class="pi pi-credit-card"></i>
                                        <span>Tarjeta</span>
                                    </button>
                                    <button class="payment-method"
                                        :class="{ 'payment-method--active': metodoPago === 'oxxo' }"
                                        @click="metodoPago = 'oxxo'">
                                        <i class="pi pi-receipt"></i>
                                        <span>OXXO</span>
                                    </button>
                                    <button class="payment-method"
                                        :class="{ 'payment-method--active': metodoPago === 'paypal' }"
                                        @click="metodoPago = 'paypal'">
                                        <i class="pi pi-paypal" style="font-size: 1.2rem;"></i>
                                        <span>PayPal</span>
                                    </button>
                                    <button class="payment-method"
                                        :class="{ 'payment-method--active': metodoPago === 'mercado_pago' }"
                                        @click="metodoPago = 'mercado_pago'">
                                        <i class="pi pi-shopping-cart"></i>
                                        <span>Mercado Pago</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tarjeta -->
                            <div v-if="metodoPago === 'tarjeta'" class="card-data">
                                <label class="card-data__label">Datos de la tarjeta</label>
                                <div class="card-data__grid">
                                    <div class="form-group form-group--full">
                                        <label style="color: var(--blue);">Número de tarjeta</label>
                                        <div class="input-modern input--blue">
                                            <i class="pi pi-credit-card" style="color: var(--blue);"></i>
                                            <input v-model="pagoForm.numero_tarjeta" type="text"
                                                placeholder="1234 1234 1234 1234" maxlength="19"
                                                @input="formatearNumeroTarjeta" />
                                            <span class="card-type" v-if="tipoTarjeta !== 'unknown'">
                                                {{ tipoTarjeta.toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group form-group--full">
                                        <label style="color: var(--purple);">Nombre en la tarjeta</label>
                                        <div class="input-modern input--purple">
                                            <i class="pi pi-user" style="color: var(--purple);"></i>
                                            <input v-model="pagoForm.nombre_tarjeta" type="text"
                                                placeholder="Nombre del titular" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--gold);">Fecha expiración</label>
                                        <div class="input-modern input--gold">
                                            <i class="pi pi-calendar" style="color: var(--gold);"></i>
                                            <input v-model="pagoForm.expiracion" type="text" placeholder="MM/AA"
                                                maxlength="5" @input="formatearExpiracion" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: var(--brand);">CVV</label>
                                        <div class="input-modern input--red">
                                            <i class="pi pi-lock" style="color: var(--brand);"></i>
                                            <input v-model="pagoForm.cvv" type="text" placeholder="123" maxlength="4"
                                                @input="formatearCVV" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- OXXO -->
                            <div v-else-if="metodoPago === 'oxxo'" class="payment-info payment-info--oxxo">
                                <i class="pi pi-info-circle"></i>
                                <div>
                                    <strong>Pago en OXXO</strong>
                                    <p>Recibirás un correo con el código de barras para realizar tu pago en cualquier
                                        sucursal OXXO.</p>
                                </div>
                            </div>

                            <!-- PayPal -->
                            <div v-else-if="metodoPago === 'paypal'" class="payment-info payment-info--paypal">
                                <i class="pi pi-paypal" style="font-size: 1.5rem;"></i>
                                <div>
                                    <strong>Pago con PayPal</strong>
                                    <p>Serás redirigido a PayPal para completar tu pago de forma segura.</p>
                                </div>
                            </div>

                            <!-- Mercado Pago -->
                            <div v-else-if="metodoPago === 'mercado_pago'" class="payment-info payment-info--mp">
                                <i class="pi pi-shopping-cart"></i>
                                <div>
                                    <strong>Pago con Mercado Pago</strong>
                                    <p>Paga con tarjeta, efectivo o saldo de Mercado Pago.</p>
                                </div>
                            </div>

                            <div class="secure-banner">
                                <i class="pi pi-lock" style="color: var(--blue);"></i>
                                <div>
                                    <strong style="color: var(--blue);">Pago 100% seguro</strong>
                                    <span>Tus datos están protegidos con cifrado SSL de 256 bits</span>
                                </div>
                            </div>

                            <div class="form-actions form-actions--dual">
                                <button class="btn btn--secondary" @click="pasoAnterior" type="button">
                                    <i class="pi pi-arrow-left"></i> Anterior
                                </button>
                                <button class="btn btn--primary btn--large btn--pulse" @click="siguientePaso"
                                    :disabled="isSubmitting || (metodoPago === 'tarjeta' && !puedePagar)" type="button">
                                    <span v-if="isSubmitting" class="btn__loading">
                                        <i class="pi pi-spin pi-spinner"></i> Procesando...
                                    </span>
                                    <span v-else>
                                        Revisar pedido <i class="pi pi-arrow-right"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="resume-card">
                        <div class="resume-card__header">
                            <span class="resume-card__title">
                                <i class="pi pi-receipt"></i> Resumen
                            </span>
                            <span class="resume-card__badge resume-card__badge--pay">Pago pendiente</span>
                        </div>

                        <div class="resume-card__items">
                            <div v-for="item in carritoItems" :key="item.id" class="cart-item-mini">
                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                <div class="cart-item-mini__info">
                                    <strong>{{ item.nombre }}</strong>
                                    <span>{{ item.cantidad }} × ${{ formatoMoneda(item.precio) }}</span>
                                </div>
                                <span class="cart-item-mini__price">${{ formatoMoneda(item.precio * item.cantidad)
                                }}</span>
                            </div>
                        </div>

                        <div class="resume-card__divider"></div>

                        <div class="resume-card__totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>${{ formatoMoneda(subtotal) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Envío</span>
                                <span :class="{ 'text-success': envioGratis }">
                                    {{ envioGratis ? 'Gratis' : '$' + formatoMoneda(costoEnvio) }}
                                </span>
                            </div>
                            <div class="total-row total-row--grand">
                                <span>Total</span>
                                <strong>${{ formatoMoneda(total) }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>

            <!-- ============================================================ -->
            <!-- PASO 3: CONFIRMAR PEDIDO - MEJORADO -->
            <!-- ============================================================ -->
            <section v-show="pasoActivo === 3" class="content-grid">
                <div class="main-column">
                    <div class="panel panel--step3">
                        <div class="panel__header">
                            <div class="panel__header-icon">
                                <i class="pi pi-check-circle"></i>
                            </div>
                            <div>
                                <span class="panel__step">Paso 3 de 3</span>
                                <h2>Confirmar pedido</h2>
                                <p class="panel__sub">Revisa todos los datos antes de finalizar</p>
                            </div>
                        </div>

                        <div class="panel__body">
                            <!-- Resumen del pedido mejorado -->
                            <div class="order-summary-enhanced">
                                <!-- Productos -->
                                <div class="order-section">
                                    <div class="order-section__header">
                                        <div class="order-section__header-left">
                                            <i class="pi pi-shopping-bag"></i>
                                            <h4>Productos <span class="order-section__count">{{ carritoItems.length }}
                                                    items</span></h4>
                                        </div>
                                        <span class="order-section__total-label">Total</span>
                                    </div>
                                    <div class="order-items-enhanced">
                                        <div v-for="item in carritoItems" :key="item.id" class="order-item-enhanced">
                                            <div class="order-item-enhanced__left">
                                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                                <div class="order-item-enhanced__info">
                                                    <strong>{{ item.nombre }}</strong>
                                                    <span class="order-item-enhanced__qty">{{ item.cantidad }} × ${{
                                                        formatoMoneda(item.precio) }}</span>
                                                </div>
                                            </div>
                                            <span class="order-item-enhanced__price">${{ formatoMoneda(item.precio *
                                                item.cantidad) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dirección de envío -->
                                <div class="order-section">
                                    <div class="order-section__header">
                                        <i class="pi pi-map-marker"></i>
                                        <h4>Dirección de envío</h4>
                                    </div>
                                    <div class="order-address-enhanced">
                                        <div class="order-address-enhanced__card">
                                            <div class="order-address-enhanced__row">
                                                <span class="order-address-enhanced__label">Destinatario:</span>
                                                <strong>{{ direccionForm.destinatario || 'No especificado' }}</strong>
                                            </div>
                                            <div class="order-address-enhanced__row">
                                                <span class="order-address-enhanced__label">Dirección:</span>
                                                <span>{{ direccionForm.calle || 'Calle no especificada' }}, {{
                                                    direccionForm.colonia || 'Colonia no especificada' }}</span>
                                            </div>
                                            <div class="order-address-enhanced__row">
                                                <span class="order-address-enhanced__label">Ciudad:</span>
                                                <span>{{ direccionForm.ciudad || 'Ciudad no especificada' }}, {{
                                                    direccionForm.estado || 'Estado no especificado' }}</span>
                                            </div>
                                            <div class="order-address-enhanced__row">
                                                <span class="order-address-enhanced__label">Código postal:</span>
                                                <span>{{ direccionForm.codigo_postal || 'No especificado' }}</span>
                                            </div>
                                            <div class="order-address-enhanced__row" v-if="direccionForm.referencias">
                                                <span class="order-address-enhanced__label">Referencias:</span>
                                                <span>{{ direccionForm.referencias }}</span>
                                            </div>
                                            <div class="order-address-enhanced__row">
                                                <span class="order-address-enhanced__label">Teléfono:</span>
                                                <span><i class="pi pi-phone"></i> {{ direccionForm.telefono || 'No especificado' }}</span>
                                            </div>
                                            <div class="order-address-enhanced__row">
                                                <span class="order-address-enhanced__label">País:</span>
                                                <span><i class="pi pi-flag"></i> México</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Método de pago -->
                                <div class="order-section">
                                    <div class="order-section__header">
                                        <i class="pi pi-credit-card"></i>
                                        <h4>Método de pago</h4>
                                    </div>
                                    <div class="order-payment-enhanced">
                                        <div class="order-payment-enhanced__card">
                                            <div class="order-payment-enhanced__icon">
                                                <i v-if="metodoPago === 'tarjeta'" class="pi pi-credit-card"></i>
                                                <i v-else-if="metodoPago === 'oxxo'" class="pi pi-receipt"></i>
                                                <i v-else-if="metodoPago === 'paypal'" class="pi pi-paypal"></i>
                                                <i v-else class="pi pi-shopping-cart"></i>
                                            </div>
                                            <div class="order-payment-enhanced__info">
                                                <strong>{{ metodoPagoLabel }}</strong>
                                                <span v-if="metodoPago === 'tarjeta' && pagoForm.numero_tarjeta">
                                                    Tarjeta terminación en <strong>{{ ultimosDigitos }}</strong>
                                                </span>
                                                <span v-else-if="metodoPago === 'oxxo'">
                                                    Pago en efectivo en OXXO
                                                </span>
                                                <span v-else-if="metodoPago === 'paypal'">
                                                    Pago con PayPal
                                                </span>
                                                <span v-else>
                                                    Pago con Mercado Pago
                                                </span>
                                            </div>
                                            <span class="order-payment-enhanced__status status--confirmed">
                                                <i class="pi pi-check-circle"></i> Confirmado
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de totales -->
                            <div class="order-totals-enhanced">
                                <div class="order-totals-enhanced__row">
                                    <span>Subtotal</span>
                                    <span>${{ formatoMoneda(subtotal) }}</span>
                                </div>
                                <div class="order-totals-enhanced__row">
                                    <span>Envío</span>
                                    <span :class="{ 'text-success': envioGratis }">
                                        {{ envioGratis ? 'Gratis' : '$' + formatoMoneda(costoEnvio) }}
                                    </span>
                                </div>
                                <div class="order-totals-enhanced__divider"></div>
                                <div class="order-totals-enhanced__row order-totals-enhanced__row--grand">
                                    <span>Total a pagar</span>
                                    <strong>${{ formatoMoneda(total) }}</strong>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="form-actions-enhanced">
                                <button class="btn btn--secondary btn--large" @click="pasoAnterior" type="button">
                                    <i class="pi pi-arrow-left"></i> Anterior
                                </button>
                                <button class="btn btn--primary btn--large btn--pulse" @click="confirmarPedido"
                                    :disabled="isSubmitting" type="button">
                                    <span v-if="isSubmitting" class="btn__loading">
                                        <i class="pi pi-spin pi-spinner"></i> Procesando...
                                    </span>
                                    <span v-else>
                                        <i class="pi pi-lock"></i> Confirmar pedido
                                        <span class="btn__price">${{ formatoMoneda(total) }}</span>
                                    </span>
                                </button>
                            </div>
                            <p class="cta-note">
                                <i class="pi pi-shield" style="color: var(--green);"></i>
                                Al confirmar, aceptas los <Link href="/terminos" class="cta-link">términos y condiciones
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="resume-card">
                        <div class="resume-card__header">
                            <span class="resume-card__title">
                                <i class="pi pi-receipt"></i> Resumen final
                            </span>
                            <span class="resume-card__badge resume-card__badge--confirm">Confirmar</span>
                        </div>

                        <div class="resume-card__items">
                            <div v-for="item in carritoItems" :key="item.id" class="cart-item-mini">
                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                <div class="cart-item-mini__info">
                                    <strong>{{ item.nombre }}</strong>
                                    <span>{{ item.cantidad }} × ${{ formatoMoneda(item.precio) }}</span>
                                </div>
                                <span class="cart-item-mini__price">${{ formatoMoneda(item.precio * item.cantidad)
                                    }}</span>
                            </div>
                        </div>

                        <div class="resume-card__divider"></div>

                        <div class="resume-card__totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>${{ formatoMoneda(subtotal) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Envío</span>
                                <span :class="{ 'text-success': envioGratis }">
                                    {{ envioGratis ? 'Gratis' : '$' + formatoMoneda(costoEnvio) }}
                                </span>
                            </div>
                            <div class="total-row total-row--grand">
                                <span>Total</span>
                                <strong>${{ formatoMoneda(total) }}</strong>
                            </div>
                        </div>

                        <div class="resume-card__footer">
                            <div class="shipping-info">
                                <i class="pi pi-map-marker" style="color: var(--brand);"></i>
                                <span>{{ direccionForm.destinatario || 'Sin dirección' }}</span>
                            </div>
                            <div class="shipping-info">
                                <i class="pi pi-credit-card" style="color: var(--blue);"></i>
                                <span>{{ metodoPagoLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card info-card--privacy">
                        <div class="privacy-icon">
                            <i class="pi pi-shield"></i>
                        </div>
                        <div>
                            <strong>Compra protegida</strong>
                            <p>Tu pedido está protegido. Si hay algún problema, te reembolsamos el 100%.</p>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// ============================================================
// OBTENER USUARIO DESDE page.props
// ============================================================
const page = usePage();

// ============================================================
// PROPS DESDE EL CONTROLADOR
// ============================================================
const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            id: null,
            nombre: 'Usuario',
            email: '',
            telefono: '',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false,
            rol: 'usuario'
        })
    },
    notificaciones: {
        type: Number,
        default: 0
    },
    favoritos: {
        type: Number,
        default: 0
    },
    mensajes: {
        type: Number,
        default: 0
    },
    carrito: {
        type: Array,
        default: () => []
    },
    direcciones: {
        type: Array,
        default: () => []
    },
    config: {
        type: Object,
        default: () => ({})
    }
});

// ============================================================
// FUNCIÓN PARA OBTENER AVATAR CORRECTAMENTE
// ============================================================
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) return avatar;
    if (avatar.startsWith('/storage/')) return avatar;
    if (!avatar.startsWith('/')) return '/storage/' + avatar;
    return avatar;
};

// ============================================================
// USUARIO CON AVATAR CORREGIDO
// ============================================================
const usuario = computed(() => {
    const user = props.usuario || page.props.usuario || {};
    let avatar = user.avatar || '/images/shared/avatar-default.jpg';
    avatar = getAvatarUrl(avatar);
    return {
        id: user.id || null,
        nombre: user.nombre || 'Invitado',
        avatar: avatar,
        verificado: user.verificado || false,
        rol: user.rol || 'invitado',
        email: user.email || '',
        telefono: user.telefono || '',
    };
});

// ---------- PASOS ----------
const pasos = [
    { id: 1, label: 'Envío', sub: 'Dirección de entrega' },
    { id: 2, label: 'Pago', sub: 'Método de pago' },
    { id: 3, label: 'Confirmar', sub: 'Revisar pedido' },
];
const pasoActivo = ref(1);
const isSubmitting = ref(false);

// ---------- CARRITO ----------
const carritoItems = ref(props.carrito || []);

// ---------- ENVÍO ----------
const envioGratisDesde = 500;
const costoEnvio = 150;

// ---------- DIRECCIÓN ----------
const direccionForm = reactive({
    destinatario: '',
    telefono: '',
    calle: '',
    colonia: '',
    ciudad: '',
    estado: '',
    codigo_postal: '',
    referencias: '',
});

// ---------- FORMULARIO ----------
const form = reactive({
    terminos: false
});

const puedeContinuarPaso1 = computed(() => {
    return form.terminos &&
        direccionForm.destinatario.trim() !== '' &&
        direccionForm.telefono.trim() !== '' &&
        direccionForm.calle.trim() !== '' &&
        direccionForm.colonia.trim() !== '' &&
        direccionForm.ciudad.trim() !== '' &&
        direccionForm.estado.trim() !== '' &&
        direccionForm.codigo_postal.trim() !== '';
});

// ---------- PAGO ----------
const metodoPago = ref('tarjeta');

const metodoPagoLabel = computed(() => {
    const labels = {
        tarjeta: 'Tarjeta de crédito/débito',
        oxxo: 'Pago en OXXO',
        paypal: 'PayPal',
        mercado_pago: 'Mercado Pago'
    };
    return labels[metodoPago.value] || 'Tarjeta';
});

const pagoForm = reactive({
    numero_tarjeta: '',
    nombre_tarjeta: '',
    expiracion: '',
    cvv: ''
});

const tipoTarjeta = computed(() => {
    const num = pagoForm.numero_tarjeta.replace(/\s/g, '');
    if (num.startsWith('4')) return 'visa';
    if (num.startsWith('5')) return 'mastercard';
    if (num.startsWith('3')) return 'amex';
    return 'unknown';
});

const ultimosDigitos = computed(() => {
    const num = pagoForm.numero_tarjeta.replace(/\s/g, '');
    return num.slice(-4);
});

const puedePagar = computed(() => {
    if (metodoPago.value === 'tarjeta') {
        const num = pagoForm.numero_tarjeta.replace(/\s/g, '');
        return num.length >= 16 &&
            pagoForm.nombre_tarjeta.trim() !== '' &&
            pagoForm.expiracion.length === 5 &&
            pagoForm.cvv.length >= 3;
    }
    return true;
});

// ---------- CÁLCULOS ----------
const subtotal = computed(() => {
    return carritoItems.value.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
});

const envioGratis = computed(() => {
    return subtotal.value >= envioGratisDesde;
});

const total = computed(() => {
    return subtotal.value + (envioGratis.value ? 0 : costoEnvio);
});

// ---------- NAVEGACIÓN ----------
function irAlPaso(paso) {
    if (paso >= 1 && paso <= pasos.length) {
        pasoActivo.value = paso;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function pasoAnterior() {
    if (pasoActivo.value > 1) {
        irAlPaso(pasoActivo.value - 1);
    }
}

function siguientePaso() {
    if (pasoActivo.value === 1) {
        if (!puedeContinuarPaso1.value) {
            return;
        }
        irAlPaso(2);
    } else if (pasoActivo.value === 2) {
        if (metodoPago.value === 'tarjeta' && !puedePagar.value) {
            return;
        }
        irAlPaso(3);
    }
}

// ---------- CONFIRMAR PEDIDO ----------
function confirmarPedido() {
    isSubmitting.value = true;

    const datosPedido = {
        productos: carritoItems.value.map(item => ({
            id: item.id,
            cantidad: item.cantidad,
            precio: item.precio,
            nombre: item.nombre,
            imagen: item.imagen,
        })),
        direccion: {
            destinatario: direccionForm.destinatario,
            telefono: direccionForm.telefono,
            calle: direccionForm.calle,
            colonia: direccionForm.colonia,
            ciudad: direccionForm.ciudad,
            estado: direccionForm.estado,
            codigo_postal: direccionForm.codigo_postal,
            referencias: direccionForm.referencias || '',
        },
        metodo_pago: metodoPago.value,
        datos_tarjeta: metodoPago.value === 'tarjeta' ? {
            numero: pagoForm.numero_tarjeta.replace(/\s/g, ''),
            nombre: pagoForm.nombre_tarjeta,
            expiracion: pagoForm.expiracion,
            cvv: pagoForm.cvv
        } : null,
        subtotal: subtotal.value,
        costo_envio: envioGratis.value ? 0 : costoEnvio,
        descuento: 0,
        total: total.value
    };

    router.post(route('tienda.pedido.confirmar'), datosPedido, {
        onSuccess: () => {
            isSubmitting.value = false;
        },
        onError: (errors) => {
            isSubmitting.value = false;
            console.error('Error al confirmar pedido:', errors);
        }
    });
}

// ---------- UTILIDADES ----------
function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(Math.round(valor));
}

function getImageUrl(imagen) {
    if (!imagen) return '/images/shared/placeholder.jpg';
    if (imagen.startsWith('http://') || imagen.startsWith('https://')) return imagen;
    if (imagen.startsWith('/storage/') || imagen.startsWith('/images/')) return imagen;
    return '/storage/' + imagen.replace(/^\/+/, '');
}

function volverAlCarrito() {
    router.visit('/tienda');
}

function formatearNumeroTarjeta(event) {
    let value = event.target.value.replace(/\D/g, '');
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    event.target.value = formatted.substring(0, 19);
    pagoForm.numero_tarjeta = formatted.substring(0, 19);
}

function formatearExpiracion(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    event.target.value = value.substring(0, 5);
    pagoForm.expiracion = value.substring(0, 5);
}

function formatearCVV(event) {
    let value = event.target.value.replace(/\D/g, '');
    event.target.value = value.substring(0, 4);
    pagoForm.cvv = value.substring(0, 4);
}

function formatearTelefono(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    event.target.value = value;
    direccionForm.telefono = value;
}

function formatearCodigoPostal(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length > 5) {
        value = value.substring(0, 5);
    }
    event.target.value = value;
    direccionForm.codigo_postal = value;
}

onMounted(() => {
    if (carritoItems.value.length === 0) {
        try {
            const stored = localStorage.getItem('club_fantasias_carrito');
            if (stored) {
                carritoItems.value = JSON.parse(stored);
            }
        } catch (e) {
            console.error('Error al cargar carrito:', e);
        }
    }
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.checkout-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --blue: #2563EB;
    --blue-soft: #EFF6FF;
    --green: #16A34A;
    --green-soft: #F0FDF4;
    --purple: #7C3AED;
    --purple-soft: #F5F3FF;
    --gold: #D97706;
    --gold-soft: #FFFBEB;
    --pink: #DB2777;
    --pink-soft: #FDF2F8;
    --ink: #1B160F;
    --ink-soft: #55503F;
    --paper: #F6F1E6;
    --card: #FFFFFF;
    --line: #E4DAC2;
    --muted: #918A76;
    --muted-light: #B7B2AF;
    --shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.12);
    --radius: 16px;
    --radius-sm: 10px;
    --radius-full: 999px;
    --ease: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --font-sans: 'Inter', system-ui, sans-serif;

    font-family: var(--font-sans);
    color: var(--ink);
    background: #f0f2f5;
    min-height: 100vh;
    padding: 1.5rem 2rem 3rem;
    max-width: 1180px;
    margin: 0 auto;
}

/* =========================================================================
   PAGE HEADER
   ========================================================================= */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.btn-back {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    background: var(--card);
    border: 1px solid var(--line);
    padding: 0.5rem 1.2rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow);
}

.btn-back:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: translateX(-3px);
    box-shadow: var(--shadow-hover);
}

.page-header__badges {
    display: flex;
    gap: 0.6rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.4rem 1rem;
    border-radius: 50px;
}

.badge--secure {
    color: var(--blue);
    background: var(--blue-soft);
    border: 1px solid #BFDBFE;
}

.badge--trust {
    color: var(--green);
    background: var(--green-soft);
    border: 1px solid #86EFAC;
}

/* =========================================================================
   STEPS NAVIGATION - MEJORADA Y MÁS PRO
   ========================================================================= */
.steps-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card);
    border-radius: var(--radius);
    padding: 1.25rem 2rem;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.steps-navigation::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--brand), #10B981, var(--brand));
    background-size: 200% 100%;
    animation: gradientMove 3s ease-in-out infinite;
}

@keyframes gradientMove {

    0%,
    100% {
        background-position: -200% 0;
    }

    50% {
        background-position: 200% 0;
    }
}

.step-nav {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex: 1;
    position: relative;
    padding: 0.25rem 0;
}

.step-nav__indicator {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.step-nav__circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    transition: var(--ease);
    border: 3px solid var(--line);
    background: var(--white);
    color: var(--muted);
    position: relative;
    z-index: 2;
}

.step-nav--active .step-nav__circle {
    border-color: var(--brand);
    background: var(--brand);
    color: var(--white);
    box-shadow: 0 0 0 6px rgba(200, 30, 58, 0.12), 0 4px 16px rgba(200, 30, 58, 0.25);
    transform: scale(1.05);
}

.step-nav--completed .step-nav__circle {
    border-color: #10B981;
    background: #10B981;
    color: var(--white);
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.12);
}

.step-nav__check {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-nav__number {
    font-weight: 700;
}

.step-nav__connector {
    width: 40px;
    padding: 0 0.25rem;
    flex-shrink: 0;
}

.connector-line {
    height: 3px;
    background: var(--line);
    border-radius: 2px;
    transition: var(--ease);
    position: relative;
    overflow: hidden;
}

.connector-line--active {
    background: linear-gradient(90deg, var(--brand), #10B981);
}

.connector-line--active::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
    0% {
        left: -100%;
    }

    100% {
        left: 100%;
    }
}

.step-nav__content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.step-nav__label {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--muted);
    transition: var(--ease);
    letter-spacing: 0.02em;
}

.step-nav--active .step-nav__label {
    color: var(--brand);
}

.step-nav--completed .step-nav__label {
    color: #10B981;
}

.step-nav__sub {
    font-size: 0.7rem;
    color: var(--muted-light);
    font-weight: 400;
}

.step-nav--active .step-nav__sub {
    color: var(--muted);
}

.step-nav--clickable {
    cursor: pointer;
}

.step-nav--clickable:hover .step-nav__circle {
    transform: scale(1.08);
    border-color: var(--brand);
}

.step-nav--clickable:hover .step-nav__label {
    color: var(--brand);
}

@media (max-width: 768px) {
    .steps-navigation {
        flex-direction: column;
        padding: 1rem;
        gap: 0.75rem;
    }

    .step-nav {
        gap: 0.6rem;
        width: 100%;
    }

    .step-nav__connector {
        display: none;
    }

    .step-nav__circle {
        width: 36px;
        height: 36px;
        font-size: 0.75rem;
    }

    .step-nav__sub {
        display: none;
    }
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* =========================================================================
   PANEL
   ========================================================================= */
.panel {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
    overflow: hidden;
}

.panel__header {
    padding: 1.5rem 1.75rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid var(--line);
    background: var(--paper);
}

.panel__header-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.panel__step {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted-light);
}

.panel__header h2 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
}

.panel__sub {
    font-size: 0.8rem;
    color: var(--muted);
    margin: 0;
}

.panel__body {
    padding: 1.75rem;
}

/* =========================================================================
   FIELD ROW Y INPUTS
   ========================================================================= */
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

@media (max-width: 600px) {
    .field-row {
        grid-template-columns: 1fr;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.field label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.req {
    color: var(--brand);
    font-style: normal;
}

.opt {
    font-weight: 400;
    color: var(--muted);
    font-style: normal;
}

.input {
    display: flex;
    align-items: center;
    background: var(--paper);
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0 0.85rem;
    transition: var(--ease);
}

.input:focus-within {
    background: var(--card);
}

.input--red:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12);
}

.input--blue:focus-within {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.input--green:focus-within {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
}

.input--purple:focus-within {
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}

.input--gold:focus-within {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
}

.input--pink:focus-within {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12);
}

.input i {
    font-size: 0.85rem;
    color: var(--muted);
    margin-right: 0.6rem;
}

.input input {
    border: none;
    background: transparent;
    padding: 0.7rem 0;
    font-size: 0.85rem;
    font-family: var(--font-sans);
    color: var(--ink);
    width: 100%;
    outline: none;
}

.address-form__footer {
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--line);
}

.address-form__note {
    font-size: 0.78rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* =========================================================================
   CONSENT Y ACTIONS
   ========================================================================= */
.consent-actions-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--line);
}

@media (max-width: 768px) {
    .consent-actions-row {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}

.consent {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    cursor: pointer;
    font-size: 0.8rem;
    color: var(--ink-soft);
    line-height: 1.4;
}

.consent input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.consent__box {
    width: 20px;
    height: 20px;
    border-radius: 5px;
    border: 2px solid var(--line);
    background: var(--paper);
    flex-shrink: 0;
    margin-top: 0.05rem;
    position: relative;
    transition: var(--ease);
    display: flex;
    align-items: center;
    justify-content: center;
}

.consent__box::after {
    content: '✓';
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    opacity: 0;
    transform: scale(0.5);
    transition: var(--ease);
}

.consent--red input:checked+.consent__box {
    background: var(--brand);
    border-color: var(--brand);
}

.consent--red input:checked+.consent__box::after {
    opacity: 1;
    transform: scale(1);
}

.consent__link {
    color: var(--brand);
    font-weight: 600;
    text-decoration: none;
}

.consent__link:hover {
    text-decoration: underline;
}

.actions-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.3rem;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .actions-section {
        align-items: stretch;
    }
}

/* =========================================================================
   BUTTONS
   ========================================================================= */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.7rem 1.6rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn--large {
    padding: 0.85rem 2rem;
    font-size: 0.95rem;
}

.btn--primary {
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    color: #fff;
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.25);
}

.btn--primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.35);
}

.btn--secondary {
    background: #f0f0f2;
    color: var(--ink-soft);
    border: 2px solid var(--line);
}

.btn--secondary:hover:not(:disabled) {
    background: var(--line);
    transform: translateY(-2px);
}

.btn--pulse {
    animation: btnPulse 2s infinite;
}

@keyframes btnPulse {

    0%,
    100% {
        box-shadow: 0 4px 16px rgba(200, 30, 58, 0.25);
    }

    50% {
        box-shadow: 0 4px 32px rgba(200, 30, 58, 0.45);
    }
}

.btn__loading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn__price {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.15rem 0.7rem;
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    margin-left: 0.3rem;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.resume-card {
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.resume-card__header {
    padding: 0.8rem 1.2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--line);
    background: var(--paper);
}

.resume-card__title {
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.resume-card__badge {
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
    background: #FEF3C7;
    color: #92400E;
}

.resume-card__badge--pay {
    background: var(--blue-soft);
    color: var(--blue);
}

.resume-card__badge--confirm {
    background: var(--brand-soft);
    color: var(--brand);
}

.resume-card__items {
    padding: 0.4rem 1rem;
    max-height: 180px;
    overflow-y: auto;
}

.cart-item-mini {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0;
    border-bottom: 1px solid var(--line);
}

.cart-item-mini:last-child {
    border-bottom: none;
}

.cart-item-mini img {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
}

.cart-item-mini__info {
    flex: 1;
    min-width: 0;
}

.cart-item-mini__info strong {
    display: block;
    font-size: 0.72rem;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cart-item-mini__info span {
    font-size: 0.65rem;
    color: var(--muted);
}

.cart-item-mini__price {
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}

.resume-card__divider {
    border-top: 1px dashed var(--line);
    margin: 0 1rem;
}

.resume-card__totals {
    padding: 0.6rem 1rem 0.6rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--ink-soft);
    padding: 0.15rem 0;
}

.text-success {
    color: var(--green) !important;
}

.total-row--grand {
    font-weight: 700;
    font-size: 0.9rem;
    padding-top: 0.3rem;
    border-top: 1px solid var(--line);
}

.total-row--grand strong {
    color: var(--brand);
    font-size: 1.1rem;
}

.resume-card__footer {
    padding: 0.5rem 1rem 0.8rem;
    border-top: 1px solid var(--line);
    background: var(--paper);
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.shipping-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.72rem;
    color: var(--ink-soft);
}

.shipping-info i {
    font-size: 0.9rem;
    flex-shrink: 0;
}

.shipping-info__empty {
    color: var(--muted-light);
}

/* =========================================================================
   INFO CARDS
   ========================================================================= */
.info-card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 1rem 1.2rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.info-card--privacy {
    display: flex;
    gap: 0.8rem;
    align-items: flex-start;
    border-left: 3px solid var(--purple);
    background: var(--purple-soft);
}

.privacy-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--white);
    color: var(--purple);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-card strong {
    display: block;
    font-size: 0.8rem;
}

.info-card--privacy strong {
    color: var(--purple);
}

.info-card p {
    font-size: 0.72rem;
    color: var(--muted);
    margin: 0.2rem 0 0;
    line-height: 1.4;
}

/* =========================================================================
   PAYMENT METHODS
   ========================================================================= */
.payment-methods {
    margin-bottom: 1.25rem;
}

.payment-methods__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: block;
    margin-bottom: 0.75rem;
}

.payment-methods__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.payment-method {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    padding: 0.75rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--card);
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
}

.payment-method:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.payment-method i {
    font-size: 1.3rem;
    color: var(--muted);
    transition: var(--ease);
}

.payment-method span {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--muted);
    transition: var(--ease);
}

.payment-method--active {
    border-color: var(--blue);
    background: var(--blue-soft);
}

.payment-method--active i {
    color: var(--blue);
}

.payment-method--active span {
    color: var(--blue);
}

/* =========================================================================
   CARD DATA
   ========================================================================= */
.card-data {
    margin-bottom: 1.25rem;
}

.card-data__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: block;
    margin-bottom: 0.75rem;
}

.card-data__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.card-data__grid .form-group--full {
    grid-column: 1 / -1;
}

@media (max-width: 600px) {
    .card-data__grid {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.form-group label {
    font-size: 0.75rem;
    font-weight: 600;
}

.input-modern {
    display: flex;
    align-items: center;
    background: var(--paper);
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0 0.75rem;
    transition: var(--ease);
}

.input-modern:focus-within {
    background: var(--card);
}

.input--blue:focus-within {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.input--purple:focus-within {
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}

.input--gold:focus-within {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
}

.input--red:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12);
}

.input-modern i {
    font-size: 0.85rem;
    margin-right: 0.5rem;
}

.input-modern input {
    border: none;
    background: transparent;
    padding: 0.6rem 0;
    font-size: 0.82rem;
    font-family: var(--font-sans);
    color: var(--ink);
    width: 100%;
    outline: none;
}

.input-modern .card-type {
    font-size: 0.5rem;
    font-weight: 700;
    margin-left: auto;
    text-transform: uppercase;
    color: var(--muted-light);
}

/* =========================================================================
   PAYMENT INFO
   ========================================================================= */
.payment-info {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 0.8rem 1rem;
    border-radius: var(--radius-sm);
    margin-bottom: 1rem;
}

.payment-info i {
    font-size: 1.2rem;
    margin-top: 0.1rem;
}

.payment-info strong {
    display: block;
    font-size: 0.82rem;
}

.payment-info p {
    font-size: 0.75rem;
    color: var(--muted);
    margin: 0.2rem 0 0;
}

.payment-info--oxxo {
    background: var(--gold-soft);
    border: 1px solid #FDE68A;
}

.payment-info--oxxo i {
    color: var(--gold);
}

.payment-info--oxxo strong {
    color: #78350F;
}

.payment-info--paypal {
    background: #E8F0FE;
    border: 1px solid #0070BA;
}

.payment-info--paypal i {
    color: #0070BA;
}

.payment-info--paypal strong {
    color: #003087;
}

.payment-info--mp {
    background: #E6F7FF;
    border: 1px solid #009EE3;
}

.payment-info--mp i {
    color: #009EE3;
}

.payment-info--mp strong {
    color: #006699;
}

/* =========================================================================
   SECURE BANNER
   ========================================================================= */
.secure-banner {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: var(--blue-soft);
    border: 2px solid #BFDBFE;
    padding: 0.6rem 1rem;
    border-radius: var(--radius-sm);
    margin: 1rem 0 0;
}

.secure-banner strong {
    display: block;
    font-size: 0.78rem;
}

.secure-banner span {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   FORM ACTIONS
   ========================================================================= */
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--line);
}

.form-actions--dual {
    justify-content: space-between;
}

@media (max-width: 600px) {
    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

/* =========================================================================
   ORDER SUMMARY ENHANCED
   ========================================================================= */
.order-summary-enhanced {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-section {
    background: var(--paper);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    border: 1px solid var(--line);
}

.order-section__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.order-section__header-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-section__header i {
    color: var(--brand);
    font-size: 1rem;
}

.order-section__header h4 {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-section__count {
    font-size: 0.7rem;
    font-weight: 400;
    color: var(--muted);
    background: var(--line);
    padding: 0.1rem 0.5rem;
    border-radius: var(--radius-full);
}

.order-section__total-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--muted);
}

.order-items-enhanced {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.order-item-enhanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

.order-item-enhanced:last-child {
    border-bottom: none;
}

.order-item-enhanced__left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.order-item-enhanced img {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    object-fit: cover;
}

.order-item-enhanced__info strong {
    display: block;
    font-size: 0.78rem;
}

.order-item-enhanced__qty {
    font-size: 0.65rem;
    color: var(--muted);
}

.order-item-enhanced__price {
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
}

.order-address-enhanced__card {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.order-address-enhanced__row {
    display: flex;
    gap: 0.5rem;
    font-size: 0.78rem;
}

.order-address-enhanced__label {
    color: var(--muted);
    min-width: 85px;
    font-weight: 500;
}

.order-address-enhanced__row strong {
    color: var(--ink);
}

.order-address-enhanced__row i {
    color: var(--brand);
    font-size: 0.7rem;
}

.order-payment-enhanced__card {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.order-payment-enhanced__icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.order-payment-enhanced__info {
    flex: 1;
}

.order-payment-enhanced__info strong {
    display: block;
    font-size: 0.82rem;
}

.order-payment-enhanced__info span {
    font-size: 0.72rem;
    color: var(--muted);
}

.order-payment-enhanced__info span strong {
    display: inline;
    font-size: 0.72rem;
    color: var(--ink);
}

.order-payment-enhanced__status {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.status--confirmed {
    color: var(--green);
    background: var(--green-soft);
}

.order-totals-enhanced {
    background: var(--paper);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    border: 1px solid var(--line);
}

.order-totals-enhanced__row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--ink-soft);
    padding: 0.15rem 0;
}

.order-totals-enhanced__divider {
    border-top: 1px dashed var(--line);
    margin: 0.3rem 0;
}

.order-totals-enhanced__row--grand {
    font-weight: 700;
    font-size: 1.05rem;
}

.order-totals-enhanced__row--grand strong {
    color: var(--brand);
    font-size: 1.2rem;
}

.form-actions-enhanced {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    justify-content: space-between;
}

@media (max-width: 600px) {
    .form-actions-enhanced {
        flex-direction: column;
    }

    .form-actions-enhanced .btn {
        width: 100%;
        justify-content: center;
    }
}

.cta-note {
    font-size: 0.7rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    justify-content: center;
    margin-top: 0.5rem;
}

.cta-link {
    color: var(--brand);
    font-weight: 600;
    text-decoration: none;
}

.cta-link:hover {
    text-decoration: underline;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 768px) {
    .checkout-page {
        padding: 1rem;
    }

    .panel__body {
        padding: 1.25rem;
    }

    .panel__header {
        padding: 1rem 1.25rem;
    }

    .btn {
        font-size: 0.85rem;
        padding: 0.65rem 1.2rem;
    }

    .btn--large {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }

    .resume-card__items {
        max-height: 140px;
    }

    .order-section {
        padding: 0.75rem 1rem;
    }

    .order-address-enhanced__row {
        flex-direction: column;
        gap: 0.1rem;
    }

    .order-address-enhanced__label {
        min-width: auto;
    }

    .order-payment-enhanced__card {
        flex-wrap: wrap;
    }

    .order-payment-enhanced__status {
        margin-left: auto;
    }

    .order-item-enhanced {
        flex-wrap: wrap;
        gap: 0.3rem;
    }

    .order-item-enhanced__left {
        flex: 1;
        min-width: 0;
    }
}

@media (max-width: 480px) {
    .checkout-page {
        padding: 0.5rem;
    }

    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .page-header__badges {
        justify-content: center;
    }

    .btn-back {
        justify-content: center;
    }

    .payment-methods__grid {
        grid-template-columns: 1fr 1fr;
    }

    .order-totals-enhanced__row--grand {
        font-size: 0.95rem;
    }

    .order-totals-enhanced__row--grand strong {
        font-size: 1rem;
    }

    .order-payment-enhanced__icon {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }
}
</style>