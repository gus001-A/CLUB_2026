<template>
    <Head title="Publica tu contenido" />

    <AppLayout
        active-nav="comunidad"
        :usuario="usuario"
        :notificaciones="5"
        :favoritos="2"
        :mensajes="3"
    >
        <div class="publicar-page">
            <!-- Hero -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <p class="hero__eyebrow">
                            <i class="pi pi-star-fill" style="color: var(--brand); font-size: 0.7rem;"></i>
                            Publica tu <strong>contenido</strong>
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            <span class="hero__title-highlight">Comparte</span> tu pasión<br />
                            y <span class="hero__title-highlight">conecta</span> con tu comunidad
                        </h1>
                        <p class="hero__text">
                            Sube tu contenido exclusivo, configura la visibilidad 
                            y publícalo para que tus suscriptores puedan disfrutarlo.
                        </p>
                    </div>

                    <div class="hero__media">
                        <img src="/images/Perfil_creador.png" alt="Perfil creador" class="hero__img" />
                        <div class="hero__fade"></div>
                    </div>
                </div>
            </section>

            <!-- Quick Stats -->
            <section class="quick-stats">
                <div v-for="b in beneficiosHero" :key="b.texto" class="stat-card">
                    <span class="stat-card__icon"><i class="pi" :class="b.icon"></i></span>
                    <div class="stat-card__body">
                        <span class="stat-card__title">{{ b.texto }}</span>
                        <span class="stat-card__desc">Beneficio exclusivo para creadores</span>
                    </div>
                </div>
            </section>

            <!-- Contenido principal -->
            <div class="content-grid">
                <div class="main-column">
                    <!-- Indicador de pasos -->
                    <div class="steps-indicator">
                        <div 
                            v-for="(paso, index) in pasos" 
                            :key="paso.id"
                            class="step-item"
                            :class="{
                                'step-item--active': pasoActivo === paso.id,
                                'step-item--completed': paso.id < pasoActivo,
                                'step-item--clickable': paso.id < pasoActivo
                            }"
                            @click="paso.id < pasoActivo && irAlPaso(paso.id)"
                        >
                            <span class="step-number">{{ index + 1 }}</span>
                            <span class="step-label">{{ paso.label }}</span>
                            <span v-if="paso.id < pasoActivo" class="step-check"><i class="pi pi-check"></i></span>
                        </div>
                    </div>

                    <!-- Paso 1: Perfil de Creador -->
                    <section v-show="pasoActivo === 1" class="form-card form-card--step1">
                        <h2>
                            <i class="pi pi-user-edit"></i>
                            Tu perfil de creador
                            <span class="step-badge">Paso {{ pasoActivo }} de {{ pasos.length }}</span>
                        </h2>

                        <div class="field-grid">
                            <div class="field" :class="{ 'field--error': erroresForm.nombreMostrar }">
                                <label>Nombre para mostrar <span class="required">*</span></label>
                                <input
                                    type="text"
                                    v-model="form.nombreMostrar"
                                    @blur="validarCampo('nombreMostrar')"
                                    placeholder="Ej: Alexandra"
                                    class="form-input"
                                    :class="{ 'input-error': erroresForm.nombreMostrar }"
                                />
                                <span v-if="erroresForm.nombreMostrar" class="error-message">
                                    <i class="pi pi-exclamation-circle"></i> {{ erroresForm.nombreMostrar }}
                                </span>
                            </div>
                            <div class="field" :class="{ 'field--error': erroresForm.descripcion }">
                                <label>Descripción</label>
                                <textarea
                                    v-model="form.descripcion"
                                    :maxlength="descripcionMax"
                                    rows="3"
                                    @blur="validarCampo('descripcion')"
                                    placeholder="Cuéntanos sobre ti..."
                                    class="form-textarea"
                                    :class="{ 'input-error': erroresForm.descripcion }"
                                ></textarea>
                                <span class="char-count">{{ form.descripcion.length }}/{{ descripcionMax }}</span>
                            </div>
                        </div>

                        <div class="field-grid">
                            <div class="field" :class="{ 'field--error': erroresForm.categorias }">
                                <label>Categorías / Intereses <span class="required">*</span></label>
                                <div class="categoria-selector">
                                    <div class="tag-input">
                                        <span
                                            v-for="cat in form.categorias"
                                            :key="cat"
                                            class="tag-chip"
                                        >
                                            {{ cat }}
                                            <button @click="toggleCategoria(cat)" type="button">
                                                <i class="pi pi-times"></i>
                                            </button>
                                        </span>
                                        <span v-if="!form.categorias.length" class="tag-placeholder">
                                            Selecciona tus categorías
                                        </span>
                                    </div>
                                    <div class="categoria-options">
                                        <button
                                            v-for="cat in categoriasDisponibles"
                                            :key="cat"
                                            class="categoria-option"
                                            :class="{ selected: form.categorias.includes(cat) }"
                                            @click="toggleCategoria(cat)"
                                            type="button"
                                        >
                                            {{ cat }}
                                        </button>
                                    </div>
                                    <span v-if="erroresForm.categorias" class="error-message">
                                        <i class="pi pi-exclamation-circle"></i> {{ erroresForm.categorias }}
                                    </span>
                                </div>
                            </div>
                            <div class="field">
                                <label>Tipo de contenido</label>
                                <div class="content-type-row">
                                    <button
                                        class="content-type-pill"
                                        :class="{ selected: form.tipoContenido === 'fotos' }"
                                        @click="form.tipoContenido = 'fotos'"
                                        type="button"
                                    >
                                        <i class="pi pi-image"></i> Fotos
                                    </button>
                                    <button
                                        class="content-type-pill"
                                        :class="{ selected: form.tipoContenido === 'videos' }"
                                        @click="form.tipoContenido = 'videos'"
                                        type="button"
                                    >
                                        <i class="pi pi-video"></i> Videos
                                    </button>
                                    <button
                                        class="content-type-pill"
                                        :class="{ selected: form.tipoContenido === 'exclusivo' }"
                                        @click="form.tipoContenido = 'exclusivo'"
                                        type="button"
                                    >
                                        <i class="pi pi-lock"></i> Exclusivo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="premium-toggle">
                            <span class="premium-toggle__icon"><i class="pi pi-crown"></i></span>
                            <div class="premium-toggle__text">
                                <strong>Perfil premium</strong>
                                <span>Activa tu perfil premium para destacar y ganar más visibilidad.</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" v-model="form.perfilPremium" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="form-actions">
                            <button
                                class="btn btn--primary"
                                @click="siguientePaso"
                                :disabled="isSubmitting"
                            >
                                <i class="pi pi-arrow-right"></i>
                                Siguiente
                            </button>
                        </div>
                    </section>

                    <!-- Paso 2: Verificación -->
                    <section v-show="pasoActivo === 2" class="form-card form-card--step2">
                        <h2>
                            <i class="pi pi-shield"></i>
                            Verificación de identidad
                            <span class="step-badge">Paso {{ pasoActivo }} de {{ pasos.length }}</span>
                        </h2>

                        <div class="age-confirmation">
                            <div class="age-confirmation__checkbox">
                                <input 
                                    type="checkbox" 
                                    id="age-confirmation" 
                                    v-model="confirmacionEdad"
                                />
                                <label for="age-confirmation">
                                    <strong>Confirmo que soy mayor de 18 años</strong>
                                    <span>Para poder publicar contenido en la plataforma, debes ser mayor de edad.</span>
                                </label>
                            </div>
                            <div v-if="!confirmacionEdad && mostrarErrorEdad" class="age-confirmation__error">
                                <i class="pi pi-exclamation-circle"></i>
                                Debes confirmar que eres mayor de edad para continuar.
                            </div>
                        </div>

                        <div class="verification-grid">
                            <div class="verification-block">
                                <span class="verification-block__label">
                                    Selfie para corroborar identidad <i class="pi pi-info-circle"></i>
                                    <span class="required">*</span>
                                </span>
                                <div class="verification-photo">
                                    <img 
                                        :src="verificacion.selfieUrl || '/images/shared/avatar-default.jpg'" 
                                        alt="Selfie" 
                                        @error="(e) => { e.target.src = '/images/shared/avatar-default.jpg' }"
                                    />
                                    <label class="verification-upload-btn">
                                        <i class="pi pi-camera"></i>
                                        <input type="file" accept="image/*" @change="manejarSelfie" />
                                    </label>
                                    <div v-if="uploadingSelfie" class="upload-progress">
                                        <div class="upload-progress__bar" :style="{ width: uploadProgress + '%' }"></div>
                                    </div>
                                </div>
                                <span class="status-chip" :class="verificacion.selfieSubida ? 'status-chip--ok' : 'status-chip--pending'">
                                    <i class="pi" :class="verificacion.selfieSubida ? 'pi-check-circle' : 'pi-clock'"></i>
                                    {{ verificacion.selfieSubida ? 'Selfie subida' : 'Pendiente' }}
                                </span>
                                <span class="verification-hint">Sube una foto clara de tu rostro</span>
                                <button 
                                    v-if="verificacion.selfieSubida" 
                                    class="btn btn--danger btn--small"
                                    @click="confirmarEliminarDocumento('selfie')"
                                    type="button"
                                >
                                    <i class="pi pi-trash"></i> Eliminar
                                </button>
                            </div>

                            <div class="verification-block verification-block--full">
                                <span class="verification-block__label">
                                    Identificación oficial <i class="pi pi-info-circle"></i>
                                    <span class="required">*</span>
                                </span>
                                <div class="verification-thumbs">
                                    <!-- Mostrar fotos del INE ya subidas -->
                                    <div
                                        v-for="(url, i) in verificacion.fotosIdentificacionUrls"
                                        :key="i"
                                        class="verification-thumb"
                                    >
                                        <img :src="url" alt="Identificación" @error="(e) => { e.target.style.display = 'none' }" />
                                        <button class="verification-thumb__delete" @click="confirmarEliminarDocumento('identificacion', i)" type="button">
                                            <i class="pi pi-times"></i>
                                        </button>
                                        <span class="verification-thumb__label">{{ i === 0 ? 'Frente' : 'Reverso' }}</span>
                                    </div>
                                    <!-- Botón para subir una foto (si hay menos de 2) -->
                                    <label class="verification-thumb verification-thumb--add" v-if="verificacion.fotosIdentificacionUrls.length < 2">
                                        <i class="pi pi-plus"></i>
                                        <span>Subir {{ verificacion.fotosIdentificacionUrls.length === 0 ? 'frente' : 'reverso' }}</span>
                                        <input type="file" accept="image/*" @change="manejarIdentificacion" />
                                    </label>
                                </div>
                                <span class="status-chip" :class="verificacion.fotosIdentificacionUrls.length >= 2 ? 'status-chip--ok' : 'status-chip--pending'">
                                    <i class="pi" :class="verificacion.fotosIdentificacionUrls.length >= 2 ? 'pi-check-circle' : 'pi-clock'"></i>
                                    {{ verificacion.fotosIdentificacionUrls.length }}/2 subidas
                                </span>
                                <span class="verification-hint">Sube una foto del frente y otra del reverso de tu identificación oficial</span>
                            </div>
                        </div>

                        <div class="verification-info">
                            <div class="verification-info__icon">
                                <i class="pi pi-info-circle"></i>
                            </div>
                            <div class="verification-info__content">
                                <p>
                                    <strong>La revisión puede tardar entre 24 y 48 horas.</strong>
                                    Te notificaremos por correo y por la plataforma cuando tu verificación sea aprobada.
                                </p>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="btn btn--secondary" @click="pasoAnterior" type="button">
                                <i class="pi pi-arrow-left"></i> Anterior
                            </button>
                            <button class="btn btn--primary" @click="siguientePaso" :disabled="isSubmitting" type="button">
                                <i class="pi pi-arrow-right"></i> Siguiente
                            </button>
                        </div>
                    </section>

                    <!-- Paso 3: Monetización -->
                    <section v-show="pasoActivo === 3" class="form-card form-card--step3">
                        <h2>
                            <i class="pi pi-dollar"></i>
                            Configura tu monetización
                            <span class="step-badge">Paso {{ pasoActivo }} de {{ pasos.length }}</span>
                        </h2>

                        <div class="monetization-section">
                            <h3>Elige tu modelo de ingresos</h3>
                            <div class="monetization-grid">
                                <button
                                    v-for="m in modelosIngreso"
                                    :key="m.key"
                                    class="monetization-card"
                                    :class="{ 
                                        selected: modeloSeleccionado === m.key,
                                        popular: m.popular
                                    }"
                                    @click="modeloSeleccionado = m.key"
                                    type="button"
                                >
                                    <span class="monetization-card__radio" :class="{ checked: modeloSeleccionado === m.key }"></span>
                                    <span v-if="m.popular" class="popular-badge">Popular</span>
                                    <i class="pi" :class="m.icon"></i>
                                    <strong>{{ m.titulo }}</strong>
                                    <p>{{ m.desc || m.nota || '' }}</p>
                                    <div class="monetization-card__price">
                                        <template v-if="m.precio !== null">
                                            <span class="price">${{ m.precio.toFixed(2) }}</span>
                                            <span class="unit">MXN</span>
                                        </template>
                                        <span v-else class="price price--custom">{{ m.unidad }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div v-if="modeloSeleccionado === 'exclusivo'" class="custom-price-field">
                            <label>Precio personalizado (MXN)</label>
                            <input 
                                type="number" 
                                v-model="precioPersonalizado" 
                                step="0.01" 
                                min="0.99" 
                                max="999.99"
                                class="form-input"
                                placeholder="Ej: 199.99"
                            />
                        </div>

                        <div class="promociones-section">
                            <h3>Ofertas y beneficios para tus suscriptores</h3>
                            <p class="promo-subtitle">Atrae más suscriptores con promociones especiales y contenido exclusivo</p>
                            
                            <div class="promo-grid-redesign">
                                <div class="promo-card" :class="{ active: promociones.pruebaGratuita }" @click="promociones.pruebaGratuita = !promociones.pruebaGratuita">
                                    <div class="promo-card__icon">
                                        <i class="pi pi-calendar"></i>
                                    </div>
                                    <div class="promo-card__content">
                                        <strong>Prueba gratuita</strong>
                                        <span>3 días sin costo para nuevos suscriptores</span>
                                    </div>
                                    <div class="promo-card__toggle">
                                        <span class="promo-status" :class="{ active: promociones.pruebaGratuita }">
                                            {{ promociones.pruebaGratuita ? 'Activada' : 'Desactivada' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="promo-card" :class="{ active: promociones.descuentoLanzamiento }" @click="promociones.descuentoLanzamiento = !promociones.descuentoLanzamiento">
                                    <div class="promo-card__icon">
                                        <i class="pi pi-tag"></i>
                                    </div>
                                    <div class="promo-card__content">
                                        <strong>Descuento de lanzamiento</strong>
                                        <span>20% de descuento para los primeros 100 suscriptores</span>
                                    </div>
                                    <div class="promo-card__toggle">
                                        <span class="promo-status" :class="{ active: promociones.descuentoLanzamiento }">
                                            {{ promociones.descuentoLanzamiento ? 'Activada' : 'Desactivada' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="promo-card" :class="{ active: promociones.paqueteVip }" @click="promociones.paqueteVip = !promociones.paqueteVip">
                                    <div class="promo-card__icon">
                                        <i class="pi pi-star"></i>
                                    </div>
                                    <div class="promo-card__content">
                                        <strong>Paquete VIP</strong>
                                        <span>Beneficios exclusivos para suscriptores premium</span>
                                    </div>
                                    <div class="promo-card__toggle">
                                        <span class="promo-status" :class="{ active: promociones.paqueteVip }">
                                            {{ promociones.paqueteVip ? 'Activado' : 'Desactivado' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cobro-section">
                            <h3>Método de cobro</h3>
                            
                            <div class="api-message">
                                <div class="api-message__icon">
                                    <i class="pi pi-info-circle"></i>
                                </div>
                                <div class="api-message__content">
                                    <strong>Próximamente integración con Mercado Pago</strong>
                                    <p>Estamos trabajando para ofrecerte múltiples opciones de pago. Por ahora, usa la tarjeta de prueba.</p>
                                </div>
                            </div>

                            <div class="tarjeta-ficticia">
                                <div class="tarjeta-ficticia__header">
                                    <div class="tarjeta-ficticia__chip">
                                        <i class="pi pi-circle"></i>
                                        <i class="pi pi-circle"></i>
                                    </div>
                                    <span class="tarjeta-ficticia__brand">
                                        <i class="pi pi-credit-card"></i> Visa
                                    </span>
                                </div>
                                <div class="tarjeta-ficticia__number">
                                    <span>••••</span>
                                    <span>••••</span>
                                    <span>••••</span>
                                    <span>8123</span>
                                </div>
                                <div class="tarjeta-ficticia__footer">
                                    <div>
                                        <span class="tarjeta-ficticia__label">Titular</span>
                                        <span class="tarjeta-ficticia__value">{{ usuario.nombre || 'Alexandra' }}</span>
                                    </div>
                                    <div>
                                        <span class="tarjeta-ficticia__label">Expira</span>
                                        <span class="tarjeta-ficticia__value">12/26</span>
                                    </div>
                                </div>
                                <div class="tarjeta-ficticia__badge">
                                    <i class="pi pi-check-circle"></i> Tarjeta de prueba
                                </div>
                            </div>

                            <div class="payout-card payout-card--select">
                                <label>
                                    <span class="payout-card__label">Frecuencia de pago</span>
                                    <select v-model="frecuenciaPago">
                                        <option>Semanal</option>
                                        <option>Quincenal</option>
                                        <option>Mensual</option>
                                    </select>
                                </label>
                            </div>

                            <p class="protected-note"><i class="pi pi-lock"></i> Tus datos financieros están protegidos y encriptados con SSL.</p>
                        </div>

                        <div class="acceso-section">
                            <h3>Reglas de acceso a tu contenido</h3>
                            <div class="access-rules-grid">
                                <div class="access-rule">
                                    <span>Contenido visible solo para suscriptores</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="reglasAcceso.soloSuscriptores" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="access-rule">
                                    <span>Aprobar suscriptores manualmente</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="reglasAcceso.aprobarManualmente" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="access-rule">
                                    <span>Permitir mensajes premium</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="reglasAcceso.permitirMensajesPremium" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="access-rule">
                                    <span>Mostrar vista previa bloqueada</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="reglasAcceso.mostrarVistaPrevia" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="access-rule">
                                    <span>Permitir compra individual sin suscripción</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="reglasAcceso.permitirCompraIndividual" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="btn btn--secondary" @click="pasoAnterior" type="button">
                                <i class="pi pi-arrow-left"></i> Anterior
                            </button>
                            <button class="btn btn--primary" @click="siguientePaso" :disabled="isSubmitting" type="button">
                                <i class="pi pi-arrow-right"></i> Siguiente
                            </button>
                        </div>
                    </section>

                    <!-- Paso 4: Publicar -->
                    <section v-show="pasoActivo === 4" class="form-card form-card--step4">
                        <h2>
                            <i class="pi pi-send"></i>
                            Publica tu contenido
                            <span class="step-badge">Paso {{ pasoActivo }} de {{ pasos.length }}</span>
                        </h2>

                        <!-- Crear publicación -->
                        <div class="publicacion-section">
                            <h3>Crea tu publicación</h3>
                            
                            <div class="field">
                                <label>Tipo de contenido</label>
                                <div class="content-type-row">
                                    <button 
                                        class="content-type-pill" 
                                        :class="{ selected: publicacion.tipoContenido === 'foto' }" 
                                        @click="publicacion.tipoContenido = 'foto'"
                                        type="button"
                                    >
                                        <i class="pi pi-image"></i> Foto
                                    </button>
                                    <button 
                                        class="content-type-pill" 
                                        :class="{ selected: publicacion.tipoContenido === 'video' }" 
                                        @click="publicacion.tipoContenido = 'video'"
                                        type="button"
                                    >
                                        <i class="pi pi-video"></i> Video
                                    </button>
                                    <button 
                                        class="content-type-pill" 
                                        :class="{ selected: publicacion.tipoContenido === 'exclusivo' }" 
                                        @click="publicacion.tipoContenido = 'exclusivo'"
                                        type="button"
                                    >
                                        <i class="pi pi-crown"></i> Exclusivo
                                    </button>
                                </div>
                            </div>

                            <div class="field mt">
                                <label>Título de la publicación <span class="required">*</span></label>
                                <input 
                                    type="text" 
                                    v-model="publicacion.titulo" 
                                    placeholder="Ej: Mi noche más especial en Madrid"
                                    class="form-input"
                                    :class="{ 'input-error': erroresPublicacion.titulo }"
                                    @blur="validarPublicacion('titulo')"
                                />
                                <span v-if="erroresPublicacion.titulo" class="error-message">
                                    <i class="pi pi-exclamation-circle"></i> {{ erroresPublicacion.titulo }}
                                </span>
                            </div>

                            <div class="field mt">
                                <label>Descripción</label>
                                <textarea 
                                    v-model="publicacion.descripcion" 
                                    :maxlength="publicacionDescripcionMax" 
                                    rows="4" 
                                    placeholder="Comparte una descripción atractiva para tu publicación..."
                                    class="form-textarea"
                                ></textarea>
                                <span class="char-count">{{ publicacion.descripcion.length }}/{{ publicacionDescripcionMax }}</span>
                            </div>

                            <div class="upload-row mt">
                                <div class="upload-thumbs">
                                    <div 
                                        v-for="(f, i) in archivosPublicacion" 
                                        :key="i" 
                                        class="upload-thumb"
                                    >
                                        <img :src="f.url" alt="" />
                                        <button 
                                            class="upload-thumb__delete" 
                                            @click="eliminarArchivoPublicacion(i)"
                                            type="button"
                                        >
                                            <i class="pi pi-times"></i>
                                        </button>
                                        <span class="upload-thumb__badge">{{ i + 1 }}</span>
                                    </div>
                                </div>
                                <label class="upload-dropzone">
                                    <i class="pi pi-cloud-upload"></i>
                                    <span>Arrastra o haz clic para agregar archivos</span>
                                    <small>Formatos soportados: JPG, PNG, MP4 · Tamaño máximo: 500MB</small>
                                    <input 
                                        type="file" 
                                        accept="image/*,video/*" 
                                        multiple 
                                        hidden 
                                        @change="onArchivosPublicacionSeleccionados" 
                                    />
                                </label>
                                <span v-if="erroresPublicacion.archivos" class="error-message">
                                    <i class="pi pi-exclamation-circle"></i> {{ erroresPublicacion.archivos }}
                                </span>
                            </div>

                            <div class="field mt">
                                <label>Etiquetas / intereses</label>
                                <div class="tag-input">
                                    <span 
                                        v-for="tag in publicacion.etiquetas" 
                                        :key="tag" 
                                        class="tag-chip"
                                    >
                                        {{ tag }} 
                                        <button @click="quitarEtiquetaPublicacion(tag)" type="button">
                                            <i class="pi pi-times"></i>
                                        </button>
                                    </span>
                                    <input 
                                        type="text" 
                                        class="tag-input__field" 
                                        placeholder="Agrega etiquetas y presiona Enter..."
                                        @keydown.enter.prevent="agregarEtiquetaPublicacion"
                                        v-model="nuevaEtiquetaPublicacion"
                                    />
                                </div>
                                <span class="tag-hint">Presiona Enter para agregar una etiqueta</span>
                            </div>
                        </div>

                        <!-- Visibilidad -->
                        <div class="visibilidad-section">
                            <h3>Visibilidad y acceso</h3>
                            
                            <div class="visibility-item">
                                <span class="visibility-item__icon"><i class="pi pi-lock"></i></span>
                                <div class="visibility-item__text">
                                    <strong>Solo suscriptores</strong>
                                    <span>Esta publicación solo será visible para tus suscriptores.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="publicacionVisibilidad.soloSuscriptores" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="visibility-item">
                                <span class="visibility-item__icon"><i class="pi pi-eye"></i></span>
                                <div class="visibility-item__text">
                                    <strong>Mostrar vista previa bloqueada</strong>
                                    <span>Muestra una imagen difuminada con candado para usuarios no suscritos.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="publicacionVisibilidad.mostrarVistaPreviaBloqueada" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="visibility-item">
                                <span class="visibility-item__icon"><i class="pi pi-comment"></i></span>
                                <div class="visibility-item__text">
                                    <strong>Permitir comentarios</strong>
                                    <span>Los suscriptores podrán comentar tu publicación.</span>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="publicacionVisibilidad.permitirComentarios" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Vista previa -->
                        <div class="previa-section">
                            <h3><i class="pi pi-eye"></i> Vista previa de tu publicación</h3>
                            
                            <div class="post-preview">
                                <div class="post-preview__header">
                                    <img :src="usuario.avatar || '/images/shared/avatar-default.jpg'" alt="Avatar" class="post-preview__avatar" />
                                    <div class="post-preview__author">
                                        <strong>
                                            {{ usuario.nombre || 'Creador' }} 
                                            <span class="premium-chip">Premium</span>
                                        </strong>
                                        <span>Hace 5 min</span>
                                    </div>
                                    <button class="post-preview__more" type="button">
                                        <i class="pi pi-ellipsis-h"></i>
                                    </button>
                                </div>

                                <p class="post-preview__title">{{ publicacion.titulo || 'Título de tu publicación' }}</p>
                                <p class="post-preview__desc">{{ publicacion.descripcion || 'Descripción de tu publicación...' }}</p>

                                <div class="post-preview__media">
                                    <img v-if="archivosPublicacion[0]" :src="archivosPublicacion[0].url" alt="" />
                                    <div v-if="publicacionVisibilidad.mostrarVistaPreviaBloqueada" class="post-preview__overlay">
                                        <span class="post-preview__lock"><i class="pi pi-lock"></i></span>
                                        <strong>Suscríbete para ver</strong>
                                        <span>este contenido exclusivo</span>
                                    </div>
                                </div>

                                <div class="post-preview__footer">
                                    <span><i class="pi pi-heart-fill"></i> {{ likesEjemplo }}</span>
                                    <span><i class="pi pi-comment"></i> {{ comentariosEjemplo }}</span>
                                    <span class="post-preview__chip">
                                        <i class="pi pi-tag"></i> 
                                        {{ publicacion.tipoContenido === 'exclusivo' ? 'Contenido exclusivo' : 'Contenido premium' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="form-actions">
                            <button class="btn btn--secondary" @click="pasoAnterior" type="button">
                                <i class="pi pi-arrow-left"></i> Anterior
                            </button>
                            <button 
                                class="btn btn--primary btn--pulse" 
                                @click="publicarAhora" 
                                :disabled="isSubmitting || !validacionPublicacionCompleta"
                                type="button"
                            >
                                <i class="pi" :class="isSubmitting ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                                {{ isSubmitting ? 'Publicando...' : 'Publicar ahora' }}
                            </button>
                        </div>

                        <div class="publicar-help">
                            <div class="publicar-help__icon">
                                <i class="pi pi-info-circle"></i>
                            </div>
                            <div class="publicar-help__content">
                                <p>
                                    <strong>Listo para compartir!</strong> 
                                    Tu publicación será visible para todos tus suscriptores. 
                                    Puedes editar o eliminar tu publicación en cualquier momento desde tu panel de control.
                                </p>
                            </div>
                        </div>
                    </section>

                    <div v-if="Object.keys(erroresForm).length && pasoActivo === 1" class="error-summary">
                        <i class="pi pi-exclamation-triangle"></i>
                        <span>Por favor corrige los errores antes de continuar.</span>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="sidebar-column">
                    <div class="sidebar-card sidebar-card--benefits">
                        <h3><i class="pi pi-star-fill"></i> Beneficios</h3>
                        <div class="benefit-list">
                            <div v-for="b in beneficiosCreador" :key="b.titulo" class="benefit-item">
                                <span class="benefit-item__icon" :style="`background: ${b.color}20; color: ${b.color}`">
                                    <i class="pi" :class="b.icon"></i>
                                </span>
                                <div>
                                    <strong>{{ b.titulo }}</strong>
                                    <span>{{ b.desc }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h3>Estimación de ingresos</h3>
                        <div class="estimate-row">
                            <span><i class="pi pi-users"></i> Suscriptores estimados</span>
                            <strong>{{ suscriptoresEstimados.toLocaleString('es-MX') }}</strong>
                        </div>
                        <div class="estimate-row">
                            <span><i class="pi pi-check-circle"></i> Ingreso mensual estimado</span>
                            <strong class="accent">${{ ingresoMensualEstimado.toLocaleString('es-MX') }} MXN</strong>
                        </div>
                        <div class="estimate-row">
                            <span><i class="pi pi-dollar"></i> Precio actual</span>
                            <strong>${{ precioActual.toFixed(2) }} MXN</strong>
                        </div>
                        <div class="estimate-row">
                            <span><i class="pi pi-images"></i> Contenido premium / mes</span>
                            <strong>12</strong>
                        </div>
                    </div>

                    <!-- Checklist -->
                    <div class="sidebar-card checklist-card" v-if="pasoActivo === 4">
                        <div class="checklist-header">
                            <div class="checklist-header__icon">
                                <i class="pi pi-check-circle"></i>
                            </div>
                            <div>
                                <h3>Lista de verificación</h3>
                                <span class="checklist-header__subtitle">
                                    {{ checklistCompletados }}/{{ checklistPublicacion.length }} completados
                                </span>
                            </div>
                            <div class="checklist-progress">
                                <div class="checklist-progress__bar" :style="{ width: porcentajeChecklist + '%' }"></div>
                            </div>
                        </div>
                        
                        <div class="checklist-list">
                            <div v-for="item in checklistPublicacion" :key="item.titulo" class="checklist-item" :class="{ 'checklist-item--completed': item.ok }">
                                <div class="checklist-item__icon">
                                    <i class="pi" :class="item.ok ? 'pi-check-circle' : 'pi-circle'"></i>
                                </div>
                                <div class="checklist-item__content">
                                    <span class="checklist-item__title">{{ item.titulo }}</span>
                                    <span class="checklist-item__status" :class="{ 'status-ok': item.ok, 'status-pending': !item.ok }">
                                        {{ item.ok ? 'Listo' : 'Pendiente' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h3>Consejos para publicar mejor</h3>
                        <div class="tips-list">
                            <div v-for="c in consejos" :key="c.titulo" class="tip-item">
                                <span class="tip-item__icon"><i class="pi" :class="c.icon"></i></span>
                                <div>
                                    <strong>{{ c.titulo }}</strong>
                                    <span>{{ c.desc }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <ConfirmModal />
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, onMounted, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { useConfirm } from '@/composables/useConfirm';

const { confirm } = useConfirm();
const page = usePage();

const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            nombre: 'Invitado',
            avatar: null,
            verificado: false
        })
    },
    form: {
        type: Object,
        default: () => ({
            nombreMostrar: '',
            descripcion: '',
            categorias: [],
            tipoContenido: 'fotos',
            perfilPremium: false,
        })
    },
    verificacion: {
        type: Object,
        default: () => ({
            selfie: { subida: false, url: null },
            fotosIdentificacion: [],
            documentoIdentidad: { estado: 'pendiente' },
        })
    },
    monetizacionSeleccionada: {
        type: String,
        default: 'suscripcion'
    },
    privacidad: {
        type: Object,
        default: () => ({
            aprobarSeguidores: true,
            mostrarContenidoBloqueado: true,
            permitirMensajesPremium: true,
            ocultarActividad: false,
        })
    },
    pasoActivo: {
        type: Number,
        default: 1
    },
    fotosPerfil: {
        type: Array,
        default: () => []
    },
    configuracionMonetizacion: {
        type: Object,
        default: null
    }
});

const usuario = computed(() => props.usuario || {
    nombre: 'Invitado',
    avatar: null,
    verificado: false
});

const pasoActivo = ref(props.pasoActivo || 1);
const pasos = [
    { id: 1, label: 'Perfil de creador', icon: 'pi-user-edit' },
    { id: 2, label: 'Verificación', icon: 'pi-shield' },
    { id: 3, label: 'Monetización', icon: 'pi-dollar' },
    { id: 4, label: 'Publicar', icon: 'pi-send' },
];

const isSubmitting = ref(false);
const uploadingSelfie = ref(false);
const uploadingIdentificacion = ref(false);
const uploadProgress = ref(0);
const subiendoFotos = ref(false);
const errorMensaje = ref('');
const confirmacionEdad = ref(false);
const mostrarErrorEdad = ref(false);

const beneficiosHero = [
    { icon: 'pi-cog', texto: 'Control total sobre tu contenido y ganancias' },
    { icon: 'pi-link', texto: 'Conexión directa con tus seguidores' },
    { icon: 'pi-sparkles', texto: 'Herramientas exclusivas para creadores' },
];

const beneficiosCreador = [
    { icon: 'pi-dollar', titulo: 'Ingresos recurrentes', desc: 'Gana cada mes con tus suscripciones.', color: '#10B981' },
    { icon: 'pi-shield', titulo: 'Contenido exclusivo', desc: 'Controla tu contenido y comparte sin límites.', color: '#4F46E5' },
    { icon: 'pi-lock', titulo: 'Comunidad segura', desc: 'Conecta con seguidores reales en un entorno seguro.', color: '#7C3AED' },
    { icon: 'pi-bolt', titulo: 'Mayor visibilidad', desc: 'Herramientas para crecer y destacar tu perfil.', color: '#F59E0B' },
];

const form = reactive({
    nombreMostrar: props.form?.nombreMostrar || '',
    descripcion: props.form?.descripcion || '',
    categorias: props.form?.categorias || [],
    tipoContenido: props.form?.tipoContenido || 'fotos',
    perfilPremium: props.form?.perfilPremium || false,
});

const categoriasDisponibles = [
    'Lifestyle', 'Viajes', 'Bienestar', 'Noches exclusivas',
    'Arte', 'Música', 'Gastronomía', 'Fitness', 'Moda'
];

const descripcionMax = 200;
const erroresForm = reactive({});

const validaciones = {
    nombreMostrar: { required: true, minLength: 2, maxLength: 30 },
    descripcion: { maxLength: descripcionMax },
    categorias: { min: 1, max: 5 }
};

function validarCampo(campo) {
    const valor = form[campo];
    const reglas = validaciones[campo];
    if (!reglas) return true;

    let esValido = true;

    if (reglas.required && !valor?.trim()) {
        erroresForm[campo] = 'Este campo es requerido';
        esValido = false;
    } else if (reglas.minLength && valor?.length < reglas.minLength) {
        erroresForm[campo] = `Mínimo ${reglas.minLength} caracteres`;
        esValido = false;
    } else if (reglas.maxLength && valor?.length > reglas.maxLength) {
        erroresForm[campo] = `Máximo ${reglas.maxLength} caracteres`;
        esValido = false;
    } else {
        delete erroresForm[campo];
    }
    
    return esValido;
}

function validarCategorias() {
    if (form.categorias.length < validaciones.categorias.min) {
        erroresForm.categorias = 'Selecciona al menos una categoría';
        return false;
    }
    if (form.categorias.length > validaciones.categorias.max) {
        erroresForm.categorias = `Máximo ${validaciones.categorias.max} categorías`;
        return false;
    }
    delete erroresForm.categorias;
    return true;
}

function toggleCategoria(cat) {
    const index = form.categorias.indexOf(cat);
    if (index > -1) {
        form.categorias.splice(index, 1);
    } else if (form.categorias.length < validaciones.categorias.max) {
        form.categorias.push(cat);
    }
    validarCategorias();
}

// 🔥 VERIFICACION - Usar las props correctamente (selfie.url y fotosIdentificacion)
const verificacion = reactive({
    selfieUrl: props.verificacion?.selfie?.url || null,
    selfieSubida: props.verificacion?.selfie?.subida || false,
    fotosIdentificacionUrls: props.verificacion?.fotosIdentificacion || [],
    documentoEstado: props.verificacion?.documentoIdentidad?.estado || 'pendiente',
});

function manejarSelfie(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        // Usar el toast global
        window.showErrorToast('Formato no válido, solo se permiten imágenes');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        window.showErrorToast('La imagen no debe superar los 5MB');
        return;
    }
    
    // Mostrar preview inmediato
    const previewUrl = URL.createObjectURL(file);
    verificacion.selfieUrl = previewUrl;
    verificacion.selfieSubida = true;
    
    uploadingSelfie.value = true;
    uploadProgress.value = 0;
    
    const formData = new FormData();
    formData.append('foto', file);
    
    router.post(route('creador.subir.selfie'), formData, {
        preserveScroll: true,
        preserveState: true,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-Inertia': 'true'
        },
        onProgress: (progress) => {
            uploadProgress.value = Math.round((progress.loaded / progress.total) * 100);
        },
        onSuccess: (page) => {
            uploadingSelfie.value = false;
            if (page && page.props && page.props.flash) {
                const flash = page.props.flash;
                if (flash.selfieUrl) {
                    if (verificacion.selfieUrl && verificacion.selfieUrl.startsWith('blob:')) {
                        URL.revokeObjectURL(verificacion.selfieUrl);
                    }
                    verificacion.selfieUrl = flash.selfieUrl;
                    verificacion.selfieSubida = true;
                    window.showSuccessToast('Selfie subida correctamente');
                }
            }
        },
        onError: (errors) => {
            uploadingSelfie.value = false;
            if (verificacion.selfieUrl && verificacion.selfieUrl.startsWith('blob:')) {
                URL.revokeObjectURL(verificacion.selfieUrl);
            }
            verificacion.selfieUrl = null;
            verificacion.selfieSubida = false;
            
            if (errors && typeof errors === 'object') {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    window.showErrorToast(Array.isArray(firstError) ? firstError[0] : firstError);
                }
            }
        }
    });
    event.target.value = '';
}

// 🔥 MODIFICADO: Subir INE foto por foto - AHORA ACEPTA 1 FOTO A LA VEZ
function manejarIdentificacion(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validar que sea una imagen
    if (!file.type.startsWith('image/')) {
        window.showErrorToast('Formato no válido, solo se permiten imágenes');
        event.target.value = '';
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        window.showErrorToast('La imagen no debe superar los 5MB');
        event.target.value = '';
        return;
    }
    
    // Verificar que no tengamos ya 2 fotos
    if (verificacion.fotosIdentificacionUrls.length >= 2) {
        window.showWarningToast('Ya tienes las 2 fotos del INE');
        event.target.value = '';
        return;
    }
    
    // Mostrar preview inmediato
    const previewUrl = URL.createObjectURL(file);
    verificacion.fotosIdentificacionUrls = [...verificacion.fotosIdentificacionUrls, previewUrl];
    
    uploadingIdentificacion.value = true;
    
    const formData = new FormData();
    formData.append('fotos[]', file);
    
    router.post(route('creador.subir.fotos-verificacion'), formData, {
        preserveScroll: true,
        preserveState: true,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-Inertia': 'true'
        },
        onSuccess: (page) => {
            uploadingIdentificacion.value = false;
            if (page && page.props && page.props.flash) {
                const flash = page.props.flash;
                if (flash.fotosVerificacionUrls && flash.fotosVerificacionUrls.length > 0) {
                    // Reemplazar la URL blob con la real
                    const realUrls = flash.fotosVerificacionUrls;
                    // Actualizar todas las URLs con las reales
                    for (let i = 0; i < verificacion.fotosIdentificacionUrls.length; i++) {
                        const url = verificacion.fotosIdentificacionUrls[i];
                        if (url && url.startsWith('blob:')) {
                            URL.revokeObjectURL(url);
                        }
                        if (i < realUrls.length) {
                            verificacion.fotosIdentificacionUrls[i] = realUrls[i];
                        }
                    }
                    
                    // Mostrar mensaje según cuántas fotos tengamos
                    if (verificacion.fotosIdentificacionUrls.length >= 2) {
                        window.showSuccessToast('¡Excelente! Ambas fotos del INE subidas correctamente');
                    } else {
                        window.showSuccessToast(`Foto de INE subida correctamente (${verificacion.fotosIdentificacionUrls.length}/2)`);
                    }
                }
            }
        },
        onError: (errors) => {
            uploadingIdentificacion.value = false;
            // Revertir: eliminar el preview agregado
            const ultimaUrl = verificacion.fotosIdentificacionUrls[verificacion.fotosIdentificacionUrls.length - 1];
            if (ultimaUrl && ultimaUrl.startsWith('blob:')) {
                URL.revokeObjectURL(ultimaUrl);
            }
            verificacion.fotosIdentificacionUrls.pop();
            
            if (errors && typeof errors === 'object') {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    window.showErrorToast(Array.isArray(firstError) ? firstError[0] : firstError);
                }
            }
        }
    });
    event.target.value = '';
}

async function confirmarEliminarDocumento(tipo, index = null) {
    const tipos = {
        'selfie': 'selfie',
        'identificacion': 'foto de identificación'
    };
    
    const nombre = tipos[tipo] || 'documento';
    const mensaje = `¿Estás seguro de que quieres eliminar esta ${nombre}?`;
    const titulo = `Eliminar ${nombre}`;
    
    const confirmed = await confirm(mensaje, {
        title: titulo,
        confirmLabel: 'Sí, eliminar',
        cancelLabel: 'Cancelar',
        danger: true,
    });
    
    if (confirmed) {
        eliminarDocumento(tipo, index);
    }
}

function eliminarDocumento(tipo, index = null) {
    if (tipo === 'selfie') {
        if (verificacion.selfieUrl && verificacion.selfieUrl.startsWith('blob:')) {
            URL.revokeObjectURL(verificacion.selfieUrl);
        }
        verificacion.selfieUrl = null;
        verificacion.selfieSubida = false;
        window.showSuccessToast('Selfie eliminada correctamente');
    } else if (tipo === 'identificacion' && index !== null) {
        const urlToRemove = verificacion.fotosIdentificacionUrls[index];
        if (urlToRemove && urlToRemove.startsWith('blob:')) {
            URL.revokeObjectURL(urlToRemove);
        }
        verificacion.fotosIdentificacionUrls.splice(index, 1);
        window.showSuccessToast('Foto de INE eliminada correctamente');
    }
}

const modelosIngreso = reactive([
    { key: 'suscripcion', icon: 'pi-refresh', titulo: 'Suscripción mensual', precio: 199.99, unidad: 'MXN', nota: 'Acceso continuo a tu contenido premium', popular: true },
    { key: 'foto', icon: 'pi-image', titulo: 'Pago por foto', precio: 299.99, unidad: 'MXN', nota: 'Gana por cada foto premium que compartas.' },
    { key: 'video', icon: 'pi-play', titulo: 'Pago por video', precio: 499.99, unidad: 'MXN', nota: 'Monetiza tus videos exclusivos.' },
    { key: 'exclusivo', icon: 'pi-lock', titulo: 'Contenido exclusivo', precio: null, unidad: 'Precio personalizado', nota: 'Publicaciones especiales para tus suscriptores.' },
]);

const configGuardada = props.configuracionMonetizacion;

const modeloSeleccionado = ref(configGuardada?.modelo_ingresos || 'suscripcion');
const precioPersonalizado = ref(configGuardada?.precio_personalizado || null);

const promociones = reactive({
    pruebaGratuita: configGuardada?.prueba_gratuita ?? true,
    descuentoLanzamiento: configGuardada?.descuento_lanzamiento ?? true,
    paqueteVip: configGuardada?.paquete_vip ?? true,
});

const frecuenciaPago = ref(configGuardada?.frecuencia_pago || 'Mensual');

const reglasAcceso = reactive({
    soloSuscriptores: configGuardada?.solo_suscriptores ?? true,
    aprobarManualmente: configGuardada?.aprobar_manualmente ?? true,
    permitirMensajesPremium: configGuardada?.permitir_mensajes_premium ?? true,
    mostrarVistaPrevia: configGuardada?.mostrar_vista_previa ?? true,
    permitirCompraIndividual: configGuardada?.permitir_compra_individual ?? false,
});

const modeloActual = computed(() => {
    return modelosIngreso.find(m => m.key === modeloSeleccionado.value);
});

const precioActual = computed(() => {
    if (modeloSeleccionado.value === 'exclusivo' && precioPersonalizado.value) {
        return precioPersonalizado.value;
    }
    return modeloActual.value?.precio ?? 0;
});

const suscriptoresEstimados = 2500;
const ingresoMensualEstimado = computed(() => Math.round(suscriptoresEstimados * precioActual.value));

// PUBLICACIÓN
const publicacionDescripcionMax = 1000;
const nuevaEtiquetaPublicacion = ref('');

const publicacion = reactive({
    tipoContenido: 'foto',
    titulo: '',
    descripcion: '',
    etiquetas: [],
});

const archivosPublicacion = reactive([]);
const erroresPublicacion = reactive({});

const publicacionVisibilidad = reactive({
    soloSuscriptores: true,
    mostrarVistaPreviaBloqueada: true,
    permitirComentarios: true,
});

const likesEjemplo = 128;
const comentariosEjemplo = 23;

const validacionesPublicacion = {
    titulo: { required: true, minLength: 3, maxLength: 60 },
    descripcion: { maxLength: publicacionDescripcionMax },
    archivos: { required: true }
};

function validarPublicacion(campo) {
    const valor = publicacion[campo];
    const reglas = validacionesPublicacion[campo];
    if (!reglas) return true;

    let esValido = true;

    if (reglas.required && !valor?.trim()) {
        erroresPublicacion[campo] = 'Este campo es requerido';
        esValido = false;
    } else if (reglas.minLength && valor?.length < reglas.minLength) {
        erroresPublicacion[campo] = `Mínimo ${reglas.minLength} caracteres`;
        esValido = false;
    } else if (reglas.maxLength && valor?.length > reglas.maxLength) {
        erroresPublicacion[campo] = `Máximo ${reglas.maxLength} caracteres`;
        esValido = false;
    } else {
        delete erroresPublicacion[campo];
    }
    
    return esValido;
}

function validarArchivosPublicacion() {
    if (archivosPublicacion.length === 0) {
        erroresPublicacion.archivos = 'Sube al menos un archivo';
        return false;
    }
    delete erroresPublicacion.archivos;
    return true;
}

const validacionPublicacionCompleta = computed(() => {
    return archivosPublicacion.length > 0 && publicacion.titulo.trim().length >= 3;
});

function onArchivosPublicacionSeleccionados(event) {
    const files = Array.from(event.target.files || []);
    
    const invalidFiles = files.filter(f => !f.type.startsWith('image/') && !f.type.startsWith('video/'));
    if (invalidFiles.length > 0) {
        window.showErrorToast('Formato no válido, solo se permiten imágenes y videos');
        return;
    }
    
    const oversizedFiles = files.filter(f => f.size > 500 * 1024 * 1024);
    if (oversizedFiles.length > 0) {
        window.showErrorToast('Los archivos no deben superar los 500MB');
        return;
    }

    files.forEach((file) => {
        archivosPublicacion.push({
            url: URL.createObjectURL(file),
            file: file,
            temporal: true
        });
    });
    event.target.value = '';
    validarArchivosPublicacion();
}

function eliminarArchivoPublicacion(index) {
    if (archivosPublicacion[index]?.url && archivosPublicacion[index].url.startsWith('blob:')) {
        URL.revokeObjectURL(archivosPublicacion[index].url);
    }
    archivosPublicacion.splice(index, 1);
    validarArchivosPublicacion();
}

function agregarEtiquetaPublicacion() {
    const tag = nuevaEtiquetaPublicacion.value.trim();
    if (tag && !publicacion.etiquetas.includes(tag) && publicacion.etiquetas.length < 10) {
        publicacion.etiquetas.push(tag);
        nuevaEtiquetaPublicacion.value = '';
    }
}

function quitarEtiquetaPublicacion(tag) {
    publicacion.etiquetas = publicacion.etiquetas.filter(t => t !== tag);
}

const checklistPublicacion = computed(() => [
    { titulo: 'Archivo cargado', ok: archivosPublicacion.length > 0 },
    { titulo: 'Título añadido', ok: publicacion.titulo.trim().length > 0 },
    { titulo: 'Descripción añadida', ok: publicacion.descripcion.trim().length > 0 },
    { titulo: 'Visibilidad configurada', ok: true },
]);

const checklistCompletados = computed(() => {
    return checklistPublicacion.value.filter(item => item.ok).length;
});

const porcentajeChecklist = computed(() => {
    const total = checklistPublicacion.value.length;
    const completados = checklistCompletados.value;
    return total > 0 ? Math.round((completados / total) * 100) : 0;
});

const consejos = [
    { icon: 'pi-star', titulo: 'Usa títulos atractivos', desc: 'Captura la atención desde el primer momento.' },
    { icon: 'pi-star', titulo: 'Publica contenido exclusivo', desc: 'Ofrece algo único que solo tus suscriptores verán.' },
    { icon: 'pi-comments', titulo: 'Interactúa con tus suscriptores', desc: 'Responde comentarios y crea una comunidad fiel.' },
    { icon: 'pi-refresh', titulo: 'Mantén constancia', desc: 'Publicar regularmente aumenta tu crecimiento.' },
];

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

function validarPaso(paso) {
    switch (paso) {
        case 1:
            return validarCampo('nombreMostrar') && validarCategorias();
        case 2:
            if (!confirmacionEdad.value) {
                mostrarErrorEdad.value = true;
                return false;
            }
            mostrarErrorEdad.value = false;
            return verificacion.selfieSubida && verificacion.fotosIdentificacionUrls.length >= 2;
        case 3:
            if (!modeloSeleccionado.value) {
                window.showWarningToast('Selecciona un modelo de ingresos');
                return false;
            }
            if (modeloSeleccionado.value === 'exclusivo' && (!precioPersonalizado.value || precioPersonalizado.value < 0.99)) {
                window.showWarningToast('Ingresa un precio válido (mínimo $0.99)');
                return false;
            }
            return true;
        case 4:
            return validacionPublicacionCompleta.value;
        default:
            return true;
    }
}

function guardarPaso(paso) {
    isSubmitting.value = true;
    
    let routeName = '';
    let data = {};
    
    switch (paso) {
        case 1:
            routeName = 'creador.perfil.guardar';
            data = {
                nombreMostrar: form.nombreMostrar,
                descripcion: form.descripcion,
                categorias: form.categorias,
                tipoContenido: form.tipoContenido,
                perfilPremium: form.perfilPremium,
            };
            break;
        case 2:
            routeName = 'creador.verificacion.guardar';
            data = {
                confirmacionEdad: confirmacionEdad.value,
                selfieUrl: verificacion.selfieUrl,
                fotosVerificacionUrls: verificacion.fotosIdentificacionUrls,
            };
            break;
        case 3:
            routeName = 'creador.monetizacion.guardar';
            data = {
                modeloSeleccionado: modeloSeleccionado.value,
                precioPersonalizado: modeloSeleccionado.value === 'exclusivo' ? precioPersonalizado.value : null,
                pruebaGratuita: promociones.pruebaGratuita,
                descuentoLanzamiento: promociones.descuentoLanzamiento,
                paqueteVip: promociones.paqueteVip,
                frecuenciaPago: frecuenciaPago.value,
                tarjetaRegistrada: true,
                tarjetaUltimos4: '8123',
                soloSuscriptores: reglasAcceso.soloSuscriptores,
                aprobarManualmente: reglasAcceso.aprobarManualmente,
                permitirMensajesPremium: reglasAcceso.permitirMensajesPremium,
                mostrarVistaPrevia: reglasAcceso.mostrarVistaPrevia,
                permitirCompraIndividual: reglasAcceso.permitirCompraIndividual,
            };
            break;
        default:
            isSubmitting.value = false;
            return;
    }
    
    router.post(route(routeName), data, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSubmitting.value = false;
            if (paso < pasos.length) {
                irAlPaso(paso + 1);
            }
        },
        onError: (errors) => {
            isSubmitting.value = false;
            if (errors && typeof errors === 'object') {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    window.showErrorToast(Array.isArray(firstError) ? firstError[0] : firstError);
                }
            }
        }
    });
}

function siguientePaso() {
    if (!validarPaso(pasoActivo.value)) {
        if (pasoActivo.value === 2 && !confirmacionEdad.value) {
            window.showWarningToast('Confirmación requerida', 'Debes confirmar que eres mayor de edad');
        } else if (pasoActivo.value === 3) {
            window.showWarningToast('Campos incompletos', 'Completa todos los campos de monetización');
        } else if (pasoActivo.value === 4) {
            window.showWarningToast('Campos incompletos', 'Completa todos los campos de la publicación');
        } else {
            window.showWarningToast('Campos obligatorios', 'Completa todos los campos obligatorios');
        }
        return;
    }

    if (pasoActivo.value < pasos.length) {
        guardarPaso(pasoActivo.value);
    }
}

function publicarAhora() {
    if (!validacionPublicacionCompleta.value) {
        window.showWarningToast('Campos incompletos', 'Completa todos los campos obligatorios');
        return;
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append('tipoContenido', publicacion.tipoContenido);
    formData.append('titulo', publicacion.titulo);
    formData.append('descripcion', publicacion.descripcion);
    formData.append('etiquetas', JSON.stringify(publicacion.etiquetas));
    formData.append('visibilidad[soloSuscriptores]', publicacionVisibilidad.soloSuscriptores ? '1' : '0');
    formData.append('visibilidad[mostrarVistaPreviaBloqueada]', publicacionVisibilidad.mostrarVistaPreviaBloqueada ? '1' : '0');
    formData.append('visibilidad[permitirComentarios]', publicacionVisibilidad.permitirComentarios ? '1' : '0');
    
    archivosPublicacion.forEach((archivo, index) => {
        if (archivo.file) {
            formData.append(`archivos[${index}]`, archivo.file);
        }
    });

    router.post(route('creador.publicar'), formData, {
        preserveScroll: true,
        preserveState: true,
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-Requested-With': 'XMLHttpRequest'
        },
        onSuccess: () => {
            isSubmitting.value = false;
            window.showSuccessToast('¡Contenido publicado exitosamente!');
            setTimeout(() => {
                router.visit(route('creador.dashboard'));
            }, 2000);
        },
        onError: (errors) => {
            isSubmitting.value = false;
            if (errors && typeof errors === 'object') {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    window.showErrorToast(Array.isArray(firstError) ? firstError[0] : firstError);
                }
                Object.keys(errors).forEach(key => {
                    erroresPublicacion[key] = errors[key];
                });
            }
        }
    });
}
</script>

<style scoped>
/* =========================================================================
   VARIABLES Y RESET
   ========================================================================= */
.publicar-page {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-soft: #FBEAEC;
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
  --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.08);
  --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;
  font-family: var(--font-sans);
  color: var(--ink);
  background: #f0f2f5;
  -webkit-font-smoothing: antialiased;
}

.publicar-page {
  max-width: 1500px;
  margin: 0 auto;
  padding: 1.25rem 2rem 3rem;
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
  max-width: 1400px;
  margin: 1.5rem auto 0;
  padding: 0 2rem;
}

.hero__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 380px;
  background: var(--ink);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 2.5rem 2.5rem;
  color: #ffffff;
}

.hero__eyebrow { 
  font-size: 0.75rem; 
  color: rgba(255, 255, 255, 0.6); 
  margin: 0 0 0.6rem; 
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.hero__eyebrow strong { 
  color: var(--brand); 
  font-weight: 700;
}

.hero__verified {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: rgba(31, 191, 92, 0.2);
  color: #48BB78;
  padding: 0.15rem 0.6rem;
  border-radius: var(--radius-full);
  font-size: 0.6rem;
  font-weight: 600;
}

.hero__title {
  font-family: var(--font-serif);
  font-size: 2.2rem;
  font-weight: 500;
  line-height: 1.1;
  margin: 0;
}

.hero__title-highlight {
  color: var(--brand);
  font-style: italic;
}

.hero__text {
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.6;
  max-width: 440px;
  margin: 0.8rem 0 0;
  font-size: 0.85rem;
}

.hero__media {
  position: relative;
  min-height: 280px;
  overflow: hidden;
  background: var(--ink);
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero__img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: transform 0.6s ease;
  padding: 1.5rem;
}

.hero:hover .hero__img {
  transform: scale(1.03);
}

.hero__fade {
  position: absolute;
  inset: 0;
  width: 33%;
  background: linear-gradient(to right, var(--ink), rgba(23, 20, 18, 0.05));
}

/* =========================================================================
   QUICK STATS
   ========================================================================= */
.quick-stats {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-card {
    background: #ffffff; 
    border-radius: var(--radius-md);
    padding: 0.8rem 1.2rem; 
    display: flex; 
    align-items: center; 
    gap: 0.75rem;
    transition: all var(--transition);
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.stat-card__icon {
    width: 34px; 
    height: 34px; 
    border-radius: var(--radius-sm); 
    background: var(--brand-soft); 
    color: var(--brand);
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
    font-size: 0.9rem;
    transition: all var(--transition);
}

.stat-card:hover .stat-card__icon {
    background: var(--brand);
    color: var(--white);
    transform: scale(1.05);
}

.stat-card__body { 
    display: flex; 
    flex-direction: column; 
    gap: 0.15rem; 
    flex: 1; 
}

.stat-card__title { 
    font-weight: 600; 
    font-size: 0.8rem; 
}

.stat-card__desc { 
    font-size: 0.7rem; 
    color: var(--muted); 
    line-height: 1.3;
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 2rem 3rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
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
   STEPS INDICATOR
   ========================================================================= */
.steps-indicator {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1rem 2rem;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    position: relative;
}

.steps-indicator::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 3rem;
    right: 3rem;
    height: 2px;
    background: var(--line);
    transform: translateY(-50%);
    z-index: 0;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
    background: var(--white);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    border: 2px solid var(--line);
    transition: all var(--transition);
}

.step-item--active {
    border-color: var(--brand);
    background: var(--brand-soft);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.step-item--completed {
    border-color: #10B981;
    background: #ECFDF5;
}

.step-item--clickable {
    cursor: pointer;
}

.step-item--clickable:hover {
    transform: scale(1.05);
    border-color: var(--brand);
}

.step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--muted);
    background: var(--surface);
}

.step-item--active .step-number {
    background: var(--brand);
    color: var(--white);
}

.step-item--completed .step-number {
    background: #10B981;
    color: var(--white);
}

.step-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
    white-space: nowrap;
}

.step-item--active .step-label {
    color: var(--brand);
}

.step-item--completed .step-label {
    color: #10B981;
}

.step-check {
    color: #10B981;
    font-size: 0.7rem;
}

/* =========================================================================
   FORM CARDS
   ========================================================================= */
.form-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.75rem;
    box-shadow: var(--shadow);
    transition: all var(--transition);
    position: relative;
    overflow: hidden;
}

.form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}

.form-card--step1::before { background: linear-gradient(90deg, #7C3AED, #4F46E5); }
.form-card--step2::before { background: linear-gradient(90deg, var(--brand), #EC4899); }
.form-card--step3::before { background: linear-gradient(90deg, #14B8A6, #F59E0B); }
.form-card--step4::before { background: linear-gradient(90deg, #4F46E5, #7C3AED); }

.form-card:hover {
    box-shadow: var(--shadow-hover);
}

.form-card h2 {
    font-size: 1.1rem;
    margin: 0 0 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
}

.form-card h2 i {
    color: var(--brand);
    font-size: 1.2rem;
}

.step-badge {
    margin-left: auto;
    background: #F3F4F6;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6B7280;
}

/* =========================================================================
   FORM FIELDS
   ========================================================================= */
.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
}

@media (max-width: 700px) {
    .field-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    position: relative;
}

.field--full {
    grid-column: 1 / -1;
}

.field label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.field label .required {
    color: #EF4444;
    margin-left: 0.2rem;
}

.form-input,
.form-textarea {
    width: 100%;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--line);
    transition: all var(--transition);
    padding: 0.6rem 0.9rem;
    font-size: 0.95rem;
    font-family: inherit;
    background: var(--white);
    color: var(--ink);
}

.form-input:focus,
.form-textarea:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
    outline: none;
}

.form-input.input-error,
.form-textarea.input-error {
    border-color: #EF4444;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.char-count {
    align-self: flex-end;
    font-size: 0.7rem;
    color: var(--muted-light);
}

.error-message {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #EF4444;
    font-size: 0.8rem;
    margin-top: 0.2rem;
}

/* =========================================================================
   CATEGORIAS
   ========================================================================= */
.categoria-selector {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tag-input {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.4rem 2rem 0.4rem 0.6rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    min-height: 44px;
    align-items: center;
    position: relative;
    transition: all var(--transition);
    background: var(--white);
}

.tag-input:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.tag-chip {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 6px;
    padding: 0.25rem 0.6rem;
    font-size: 0.78rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.tag-chip button {
    border: none;
    background: none;
    color: var(--brand);
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    font-size: 0.7rem;
    opacity: 0.7;
    transition: all var(--transition);
}

.tag-chip button:hover {
    opacity: 1;
    transform: scale(1.2);
}

.tag-placeholder {
    color: var(--muted-light);
    font-size: 0.85rem;
}

.categoria-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.categoria-option {
    padding: 0.3rem 0.8rem;
    border: 1.5px solid var(--line);
    border-radius: 20px;
    background: var(--white);
    font-size: 0.78rem;
    cursor: pointer;
    transition: all var(--transition);
    color: var(--ink-soft);
}

.categoria-option:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.categoria-option.selected {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
}

/* =========================================================================
   CONTENT TYPE
   ========================================================================= */
.content-type-row {
    display: flex;
    gap: 0.6rem;
}

.content-type-pill {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    padding: 0.6rem 0.8rem;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink-soft);
    transition: all var(--transition);
}

.content-type-pill:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.content-type-pill.selected {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

/* =========================================================================
   PREMIUM TOGGLE
   ========================================================================= */
.premium-toggle {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    border-top: 1px solid var(--line);
    padding-top: 1.25rem;
    margin-top: 0.5rem;
}

.premium-toggle__icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--brand-soft), #F5F3FF);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
}

.premium-toggle__text {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.premium-toggle__text strong {
    font-size: 0.85rem;
}

.premium-toggle__text span {
    font-size: 0.78rem;
    color: var(--muted);
}

/* =========================================================================
   TOGGLE SWITCH
   ========================================================================= */
.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--line);
    border-radius: var(--radius-full);
    transition: all var(--transition);
    cursor: pointer;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--brand);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: var(--white);
    border-radius: 50%;
    transition: all var(--transition);
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(20px);
}

/* =========================================================================
   VERIFICATION - ESTILOS COMPLETOS
   ========================================================================= */
.verification-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 900px) {
    .verification-grid {
        grid-template-columns: 1fr;
    }
}

.verification-block {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.verification-block--full {
    grid-column: 1 / -1;
}

.verification-block__label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.verification-block__label i {
    color: var(--muted-light);
    cursor: help;
}

.verification-block__label .required {
    color: #EF4444;
    margin-left: 0.2rem;
}

.verification-photo {
    position: relative;
    width: 100%;
    max-width: 150px;
    aspect-ratio: 1/1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 3px solid var(--line);
}

.verification-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.verification-upload-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    background: var(--brand);
    color: var(--white);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 2px solid var(--white);
    transition: all var(--transition);
}

.verification-upload-btn:hover {
    transform: scale(1.1);
}

.verification-upload-btn input {
    display: none;
}

.upload-progress {
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 100%;
    height: 4px;
    background: #E5E7EB;
    border-radius: 2px;
    overflow: hidden;
}

.upload-progress__bar {
    height: 100%;
    background: var(--brand);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.verification-thumbs {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: flex-start;
}

.verification-thumb {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid var(--line);
    flex-shrink: 0;
    background: var(--surface);
}

.verification-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.verification-thumb__label {
    position: absolute;
    bottom: 4px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 0.55rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    white-space: nowrap;
}

.verification-thumb__delete {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    font-size: 0.6rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
    z-index: 2;
}

.verification-thumb__delete:hover {
    background: #EF4444;
    transform: scale(1.1);
}

.verification-thumb--add {
    width: 100px;
    height: 100px;
    border: 2px dashed var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--muted-light);
    cursor: pointer;
    transition: all var(--transition);
    flex-shrink: 0;
    gap: 0.25rem;
}

.verification-thumb--add:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.verification-thumb--add i {
    font-size: 1.5rem;
}

.verification-thumb--add span {
    font-size: 0.65rem;
    font-weight: 500;
    text-align: center;
}

.verification-thumb--add input {
    display: none;
}

.status-chip {
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.2rem 0.8rem;
    border-radius: var(--radius-full);
    width: fit-content;
}

.status-chip--ok {
    color: #10B981;
    background: #ECFDF5;
}

.status-chip--pending {
    color: #D69E2E;
    background: #FFF8E1;
}

.verification-hint {
    font-size: 0.7rem;
    color: var(--muted);
    margin-top: 0.1rem;
}

.age-confirmation {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}

.age-confirmation__checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.age-confirmation__checkbox input[type="checkbox"] {
    margin-top: 0.2rem;
    width: 18px;
    height: 18px;
    accent-color: var(--brand);
    cursor: pointer;
    flex-shrink: 0;
}

.age-confirmation__checkbox label {
    cursor: pointer;
}

.age-confirmation__checkbox label strong {
    display: block;
    font-size: 0.85rem;
    color: var(--ink);
}

.age-confirmation__checkbox label span {
    font-size: 0.78rem;
    color: var(--muted);
}

.age-confirmation__error {
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #FEF2F2;
    border: 1px solid #FCA5A5;
    border-radius: var(--radius-sm);
    color: #991B1B;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.age-confirmation__error i {
    font-size: 1rem;
}

.verification-info {
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    margin-top: 1.5rem;
}

.verification-info__icon {
    color: #3B82F6;
    font-size: 1.2rem;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.verification-info__content p {
    font-size: 0.82rem;
    color: #1E3A5F;
    margin: 0;
    line-height: 1.5;
}

.verification-info__content strong {
    color: #1E40AF;
}

/* =========================================================================
   MONETIZACION
   ========================================================================= */
.monetization-section,
.promociones-section,
.cobro-section,
.acceso-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--line);
}

.monetization-section:last-child,
.promociones-section:last-child,
.cobro-section:last-child,
.acceso-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.monetization-section h3,
.promociones-section h3,
.cobro-section h3,
.acceso-section h3 {
    font-size: 0.95rem;
    margin: 0 0 1rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.promo-subtitle {
    font-size: 0.82rem;
    color: var(--muted);
    margin: -0.5rem 0 1.25rem;
}

.monetization-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.1rem;
}

@media (max-width: 900px) {
    .monetization-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 500px) {
    .monetization-grid {
        grid-template-columns: 1fr;
    }
}

.monetization-card {
    text-align: left;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 1.25rem;
    background: var(--white);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    position: relative;
    transition: all var(--transition);
}

.monetization-card:hover {
    border-color: var(--muted-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.monetization-card.selected {
    border-color: var(--brand);
    background: var(--brand-soft);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15), var(--shadow);
}

.monetization-card.popular {
    border-color: #D69E2E;
}

.monetization-card__radio {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--line);
    position: absolute;
    top: 1rem;
    right: 1rem;
    transition: all var(--transition);
}

.monetization-card__radio.checked {
    border-color: var(--brand);
    background: var(--brand);
    box-shadow: inset 0 0 0 3px var(--white);
}

.popular-badge {
    position: absolute;
    top: -8px;
    right: 12px;
    background: #D69E2E;
    color: white;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.6rem;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.monetization-card i {
    font-size: 1.1rem;
    color: var(--ink-soft);
}

.monetization-card.selected i {
    color: var(--brand);
}

.monetization-card strong {
    font-size: 0.9rem;
    padding-right: 1.5rem;
}

.monetization-card p {
    font-size: 0.78rem;
    color: var(--muted);
    margin: 0;
    line-height: 1.5;
    min-height: 2.8em;
}

.monetization-card__price {
    display: flex;
    align-items: baseline;
    gap: 0.4rem;
    margin-top: 0.4rem;
}

.monetization-card__price .price {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--ink);
}

.monetization-card__price .price--custom {
    font-size: 0.85rem;
    color: var(--ink-soft);
    font-weight: 600;
}

.monetization-card__price .unit {
    font-size: 0.7rem;
    color: var(--muted);
}

.custom-price-field {
    margin: 1rem 0 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
}

.custom-price-field label {
    font-weight: 600;
    color: var(--ink-soft);
    white-space: nowrap;
}

.custom-price-field .form-input {
    max-width: 150px;
}

/* =========================================================================
   PROMOCIONES
   ========================================================================= */
.promo-grid-redesign {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 900px) {
    .promo-grid-redesign {
        grid-template-columns: 1fr;
    }
}

.promo-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all var(--transition);
    background: var(--white);
}

.promo-card:hover {
    border-color: var(--muted-light);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.promo-card.active {
    border-color: var(--brand);
    background: var(--brand-soft);
}

.promo-card__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--surface);
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all var(--transition);
}

.promo-card.active .promo-card__icon {
    background: var(--brand);
    color: var(--white);
}

.promo-card__content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.promo-card__content strong {
    font-size: 0.85rem;
}

.promo-card__content span {
    font-size: 0.72rem;
    color: var(--muted);
}

.promo-status {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
    background: #F3F4F6;
    color: #6B7280;
    transition: all var(--transition);
}

.promo-status.active {
    background: #10B981;
    color: var(--white);
}

.promo-card__toggle {
    flex-shrink: 0;
}

/* =========================================================================
   TARJETA FICTICIA
   ========================================================================= */
.api-message {
    display: flex;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: #FEFCE8;
    border: 1px solid #FDE68A;
    border-radius: var(--radius-sm);
    margin: 1rem 0;
}

.api-message__icon {
    color: #D97706;
    font-size: 1.2rem;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.api-message__content strong {
    display: block;
    font-size: 0.85rem;
    color: #92400E;
}

.api-message__content p {
    font-size: 0.78rem;
    color: #78350F;
    margin: 0.2rem 0 0;
    line-height: 1.5;
}

.tarjeta-ficticia {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    color: white;
    margin: 1rem 0;
    max-width: 400px;
    position: relative;
    overflow: hidden;
}

.tarjeta-ficticia::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}

.tarjeta-ficticia__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.tarjeta-ficticia__chip {
    display: flex;
    gap: 0.3rem;
}

.tarjeta-ficticia__chip i {
    font-size: 0.8rem;
    color: #d4af37;
}

.tarjeta-ficticia__brand {
    font-weight: 700;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.tarjeta-ficticia__number {
    display: flex;
    gap: 0.8rem;
    font-size: 1.1rem;
    letter-spacing: 0.1rem;
    margin-bottom: 1.5rem;
    font-family: 'Courier New', monospace;
}

.tarjeta-ficticia__number span {
    opacity: 0.8;
}

.tarjeta-ficticia__number span:last-child {
    opacity: 1;
}

.tarjeta-ficticia__footer {
    display: flex;
    gap: 2rem;
}

.tarjeta-ficticia__label {
    font-size: 0.6rem;
    text-transform: uppercase;
    opacity: 0.6;
    display: block;
}

.tarjeta-ficticia__value {
    font-size: 0.85rem;
    font-weight: 600;
}

.tarjeta-ficticia__badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    font-size: 0.6rem;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.payout-card--select {
    cursor: default;
    margin-top: 0.5rem;
}

.payout-card--select label {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.payout-card__label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.payout-card--select select {
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.6rem;
    font-size: 0.85rem;
    color: var(--ink);
    background: var(--white);
    font-family: inherit;
    transition: all var(--transition);
}

.payout-card--select select:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
    outline: none;
}

.protected-note {
    font-size: 0.78rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin: 0.5rem 0 0;
}

/* =========================================================================
   ACCESO
   ========================================================================= */
.access-rules-grid {
    display: flex;
    flex-direction: column;
}

.access-rule {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.9rem 0;
    border-top: 1px solid var(--line);
}

.access-rule:first-child {
    border-top: none;
    padding-top: 0;
}

.access-rule span {
    font-size: 0.85rem;
    color: var(--ink);
}

/* =========================================================================
   BUTTONS
   ========================================================================= */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1.4rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all var(--transition);
    font-family: inherit;
    text-decoration: none;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn--primary {
    background: var(--brand);
    color: var(--white);
}

.btn--primary:hover:not(:disabled) {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn--secondary {
    background: #F3F4F6;
    color: #374151;
}

.btn--secondary:hover:not(:disabled) {
    background: #E5E7EB;
}

.btn--success {
    background: #10B981;
    color: var(--white);
}

.btn--success:hover:not(:disabled) {
    background: #059669;
}

.btn--danger {
    background: #EF4444;
    color: var(--white);
}

.btn--danger:hover:not(:disabled) {
    background: #dc2626;
}

.btn--small {
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
}

.btn--pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(200, 30, 58, 0.4); }
    50% { box-shadow: 0 0 0 10px rgba(200, 30, 58, 0); }
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--line);
    justify-content: flex-end;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all var(--transition);
}

.sidebar-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.sidebar-card h3 {
    font-size: 1rem;
    margin: 0 0 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 700;
}

.sidebar-card h3 i {
    font-size: 0.9rem;
}

.sidebar-card--benefits {
    border-color: #F5F3FF;
}

.sidebar-card--benefits h3 i {
    color: #7C3AED;
}

.benefit-list {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.benefit-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.benefit-item__icon {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.9rem;
}

.benefit-item strong {
    display: block;
    font-size: 0.85rem;
}

.benefit-item span {
    font-size: 0.78rem;
    color: var(--muted);
}

.estimate-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem 0;
    font-size: 0.85rem;
    color: var(--ink-soft);
    border-top: 1px solid var(--line);
}

.estimate-row:first-of-type {
    border-top: none;
}

.estimate-row span {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.estimate-row span i {
    color: var(--muted-light);
    font-size: 0.78rem;
}

.estimate-row strong {
    color: var(--ink);
}

.estimate-row strong.accent {
    color: var(--brand);
    font-size: 1.05rem;
}

/* =========================================================================
   CHECKLIST
   ========================================================================= */
.checklist-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all var(--transition);
    overflow: hidden;
}

.checklist-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.checklist-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.checklist-header__icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.checklist-header__icon i {
    font-size: 1.2rem;
}

.checklist-header h3 {
    font-size: 0.95rem;
    margin: 0;
    font-weight: 700;
    color: var(--ink);
}

.checklist-header__subtitle {
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 500;
}

.checklist-progress {
    width: 100%;
    height: 4px;
    background: var(--line);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 0.25rem;
    flex-basis: 100%;
}

.checklist-progress__bar {
    height: 100%;
    background: linear-gradient(90deg, var(--brand), #10B981);
    border-radius: 2px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.checklist-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.9rem;
    border-radius: var(--radius-sm);
    background: var(--surface);
    transition: all var(--transition);
    border: 1px solid transparent;
}

.checklist-item:hover {
    background: var(--white);
    border-color: var(--line);
}

.checklist-item--completed {
    background: #ECFDF5;
    border-color: #D1FAE5;
}

.checklist-item--completed:hover {
    background: #D1FAE5;
    border-color: #6EE7B7;
}

.checklist-item__icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
    color: var(--muted-light);
    background: var(--white);
    transition: all var(--transition);
}

.checklist-item--completed .checklist-item__icon {
    color: #10B981;
    background: #D1FAE5;
}

.checklist-item__content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-width: 0;
    gap: 0.5rem;
}

.checklist-item__title {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--ink-soft);
}

.checklist-item--completed .checklist-item__title {
    color: var(--ink);
    font-weight: 600;
}

.checklist-item__status {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
    white-space: nowrap;
    transition: all var(--transition);
}

.status-pending {
    color: var(--muted);
    background: var(--line);
}

.status-ok {
    color: #065F46;
    background: #D1FAE5;
}

/* =========================================================================
   TIPS
   ========================================================================= */
.tips-list {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.tip-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.tip-item__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tip-item strong {
    display: block;
    font-size: 0.85rem;
}

.tip-item span {
    font-size: 0.76rem;
    color: var(--muted);
}

.error-summary {
    background: #FEF2F2;
    border: 1px solid #fca5a5;
    color: #991b1b;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* =========================================================================
   PUBLICACION - STEP 4
   ========================================================================= */
.publicacion-section,
.visibilidad-section,
.previa-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--line);
}

.publicacion-section:last-child,
.visibilidad-section:last-child,
.previa-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.publicacion-section h3,
.visibilidad-section h3,
.previa-section h3 {
    font-size: 0.95rem;
    margin: 0 0 1rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.mt {
    margin-top: 1.25rem;
}

.tag-hint {
    font-size: 0.7rem;
    color: var(--muted-light);
    margin-top: 0.2rem;
}

.upload-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.upload-thumbs {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

@media (max-width: 640px) {
    .upload-thumbs {
        grid-template-columns: repeat(2, 1fr);
    }
}

.upload-thumb {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 3/4;
    border: 2px solid var(--line);
}

.upload-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-thumb__badge {
    position: absolute;
    bottom: 4px;
    left: 4px;
    background: rgba(0,0,0,0.7);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: 10px;
}

.upload-thumb__delete {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
    z-index: 2;
}

.upload-thumb__delete:hover {
    background: #EF4444;
    transform: scale(1.1);
}

.upload-dropzone {
    border: 1.5px dashed var(--line);
    border-radius: var(--radius-sm);
    padding: 1.25rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    cursor: pointer;
    background: var(--surface);
    transition: all var(--transition);
}

.upload-dropzone:hover {
    border-color: var(--brand);
    background: var(--brand-soft);
}

.upload-dropzone i {
    font-size: 1.3rem;
    color: var(--muted-light);
}

.upload-dropzone span {
    font-size: 0.82rem;
    color: var(--ink-soft);
    font-weight: 600;
}

.upload-dropzone small {
    font-size: 0.72rem;
    color: var(--muted-light);
}

.visibility-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.9rem 0;
    border-top: 1px solid var(--line);
}

.visibility-item:first-child {
    border-top: none;
    padding-top: 0;
}

.visibility-item__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.visibility-item__text {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.visibility-item__text strong {
    font-size: 0.84rem;
}

.visibility-item__text span {
    font-size: 0.74rem;
    color: var(--muted);
}

.post-preview {
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 1.1rem;
    background: var(--surface);
}

.post-preview__header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.7rem;
}

.post-preview__avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.post-preview__author {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.post-preview__author strong {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.premium-chip {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.12rem 0.4rem;
    border-radius: var(--radius-full);
}

.post-preview__author span {
    font-size: 0.7rem;
    color: var(--muted-light);
}

.post-preview__more {
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    padding: 0.2rem;
}

.post-preview__title {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0 0 0.3rem;
}

.post-preview__desc {
    font-size: 0.8rem;
    color: var(--ink-soft);
    margin: 0 0 0.8rem;
    line-height: 1.6;
}

.post-preview__media {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 16/10;
    background: var(--ink);
}

.post-preview__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: blur(14px) brightness(0.65);
    transform: scale(1.05);
}

.post-preview__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--white);
    gap: 0.3rem;
    text-align: center;
}

.post-preview__lock {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    margin-bottom: 0.2rem;
}

.post-preview__overlay strong {
    font-size: 0.85rem;
}

.post-preview__overlay span {
    font-size: 0.72rem;
    color: var(--muted-light);
}

.post-preview__footer {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-top: 0.9rem;
    font-size: 0.8rem;
    color: var(--ink-soft);
}

.post-preview__footer i {
    color: var(--brand);
    margin-right: 0.25rem;
}

.post-preview__chip {
    margin-left: auto;
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.2rem 0.7rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.publicar-help {
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    margin-top: 1.5rem;
}

.publicar-help__icon {
    color: #3B82F6;
    font-size: 1.2rem;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.publicar-help__content p {
    font-size: 0.82rem;
    color: #1E3A5F;
    margin: 0;
    line-height: 1.5;
}

.publicar-help__content strong {
    color: #1E40AF;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .publicar-page {
        padding: 1rem 1rem 2rem;
    }
    .hero, .quick-stats, .content-grid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .hero__grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .hero__copy {
        padding: 2rem 1.5rem;
    }
    .hero__title {
        font-size: 1.8rem;
    }
    .hero__media {
        min-height: 200px;
        order: -1;
    }
    .hero__fade {
        display: none;
    }
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .steps-indicator {
        padding: 0.75rem 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }
    .steps-indicator::before {
        display: none;
    }
    .step-item {
        padding: 0.2rem 0.6rem;
        border-width: 1.5px;
    }
    .step-label {
        font-size: 0.7rem;
    }
    .step-number {
        width: 24px;
        height: 24px;
        font-size: 0.7rem;
    }
    .monetization-grid {
        grid-template-columns: 1fr 1fr;
    }
    .promo-grid-redesign {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .quick-stats {
        grid-template-columns: 1fr;
    }
    .hero__copy {
        padding: 1.5rem 1rem;
    }
    .hero__title {
        font-size: 1.4rem;
    }
    .hero__text {
        font-size: 0.8rem;
    }
    .hero__media {
        min-height: 160px;
    }
    .hero__img {
        padding: 1rem;
    }
    .form-card {
        padding: 1.25rem;
    }
    .content-grid {
        padding: 0 1rem 2rem;
    }
    .form-actions {
        flex-direction: column;
    }
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    .steps-indicator {
        flex-direction: column;
        align-items: stretch;
        padding: 0.75rem;
    }
    .step-item {
        width: 100%;
        justify-content: center;
    }
    .verification-grid {
        grid-template-columns: 1fr;
    }
    .verification-thumb {
        width: 80px;
        height: 80px;
    }
    .verification-thumb--add {
        width: 80px;
        height: 80px;
    }
    .monetization-grid {
        grid-template-columns: 1fr;
    }
    .publicar-page {
        padding: 0.75rem 0.75rem 1.5rem;
    }
    .field-grid {
        grid-template-columns: 1fr;
    }
    .upload-thumbs {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 0.6rem 0.8rem;
    }
    .sidebar-card {
        padding: 0.6rem;
    }
    .hero__title {
        font-size: 1.2rem;
    }
    .verification-photo {
        max-width: 120px;
    }
    .verification-thumb {
        width: 70px;
        height: 70px;
    }
    .verification-thumb--add {
        width: 70px;
        height: 70px;
    }
    .custom-price-field {
        flex-direction: column;
        align-items: stretch;
    }
    .custom-price-field .form-input {
        max-width: 100%;
    }
    .promo-card {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }
    .publicar-page {
        padding: 0.5rem 0.5rem 1rem;
    }
    .upload-thumbs {
        grid-template-columns: repeat(2, 1fr);
    }
    .content-type-row {
        flex-direction: column;
    }
    .content-type-pill {
        justify-content: center;
    }
    .post-preview__footer {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .post-preview__chip {
        margin-left: 0;
    }
}
</style>