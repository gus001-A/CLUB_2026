<template>

    <Head title="Ganancias como creador" />

    <AppLayout active-nav="comunidad">
        <div class="ganancias-page">
            <!-- ============================================================ -->
            <!-- HERO MEJORADO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <div class="hero__badge">
                            <i class="pi pi-wallet"></i>
                            Panel de ganancias
                        </div>
                        <p class="hero__eyebrow">
                            Bienvenido a tus <strong>ganancias</strong>
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            Tus <span class="hero__title-highlight">ganancias</span> como creador
                        </h1>
                        <p class="hero__text">
                            Monitorea tus ingresos, suscripciones y el rendimiento de tu contenido en tiempo real.
                        </p>
                    </div>

                    <div class="hero__media">
                        <div class="hero__media-glow"></div>
                        <img src="/images/Ganancias.png" alt="Ganancias" class="hero__img" />
                        <div class="hero__fade"></div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- KPIS PRINCIPALES - SOLO UNA VEZ -->
            <!-- ============================================================ -->
            <section class="kpis-grid">
                <div v-for="m in metricas" :key="m.titulo" class="kpi-card">
                    <div class="kpi-card__icon-wrapper">
                        <span class="kpi-card__icon"><i class="pi" :class="m.icon"></i></span>
                    </div>
                    <div class="kpi-card__content">
                        <span class="kpi-card__title">{{ m.titulo }}</span>
                        <strong class="kpi-card__value">{{ m.valor }}</strong>
                        <span class="kpi-card__trend" :class="m.variacion.includes('+') ? 'trend-up' : 'trend-down'">
                            <i class="pi" :class="m.variacion.includes('+') ? 'pi-arrow-up' : 'pi-arrow-down'"></i>
                            {{ m.variacion }}
                            <span class="kpi-card__comparativa">{{ m.comparativa }}</span>
                        </span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL + SIDEBAR -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <div class="main-column">
                    <!-- Gráfica de ingresos -->
                    <section class="chart-card">
                        <div class="chart-card__header">
                            <div>
                                <h3>Ingresos de los últimos 6 meses</h3>
                                <span class="chart-card__subtitle">Evolución mensual de tus ganancias</span>
                            </div>
                            <div class="chart-card__actions">
                                <label class="chart-select">
                                    <select>
                                        <option>Últimos 6 meses</option>
                                        <option>Último año</option>
                                        <option>Últimos 12 meses</option>
                                    </select>
                                    <i class="pi pi-chevron-down"></i>
                                </label>
                            </div>
                        </div>
                        <div class="chart-legend">
                            <span class="legend-item">
                                <span class="legend-dot legend-dot--solid"></span> Ingresos (MXN)
                            </span>
                            <span class="legend-item legend-item--total">
                                <span class="legend-dot legend-dot--total"></span> Total: {{ totalIngresos }}
                            </span>
                        </div>

                        <div class="chart-container">
                            <svg :viewBox="`0 0 ${chartWidth} ${chartHeight + 30}`" class="chart-svg">
                                <defs>
                                    <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#c81e3a" stop-opacity="0.2" />
                                        <stop offset="100%" stop-color="#c81e3a" stop-opacity="0" />
                                    </linearGradient>
                                    <filter id="chartShadow">
                                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.1" />
                                    </filter>
                                </defs>

                                <line v-for="n in 4" :key="n" x1="0" :x2="chartWidth" :y1="(chartHeight / 4) * n"
                                    :y2="(chartHeight / 4) * n" stroke="#f0f0f2" stroke-width="1"
                                    stroke-dasharray="4,4" />

                                <path :d="areaPath" fill="url(#areaGradient)" />
                                <path :d="lineaPath" fill="none" stroke="#c81e3a" stroke-width="3"
                                    filter="url(#chartShadow)" />

                                <circle v-for="(p, i) in puntosLinea" :key="i" :cx="p.x" :cy="p.y" r="5" fill="#fff"
                                    stroke="#c81e3a" stroke-width="2.5"
                                    style="cursor: pointer; transition: all 0.3s ease;" @mouseenter="hoveredIndex = i"
                                    @mouseleave="hoveredIndex = null" />

                                <g v-if="hoveredIndex !== null">
                                    <rect :x="puntosLinea[hoveredIndex].x - 40" y="8" width="80" height="24" rx="6"
                                        fill="#1f2024" opacity="0.9" />
                                    <text :x="puntosLinea[hoveredIndex].x" y="25" text-anchor="middle" fill="#fff"
                                        font-size="11" font-weight="600">
                                        ${{ formatNumber(puntosLinea[hoveredIndex].valor) }}
                                    </text>
                                </g>

                                <text v-for="(m, i) in meses" :key="m" :x="puntosLinea[i].x" :y="chartHeight + 22"
                                    text-anchor="middle" class="chart-axis-label">
                                    {{ m }}
                                </text>
                            </svg>

                            <div class="chart-y-labels">
                                <span>$30K</span><span>$20K</span><span>$10K</span><span>$0</span>
                            </div>
                        </div>
                    </section>

                    <!-- Transacciones recientes -->
                    <section class="table-card">
                        <div class="table-card__header">
                            <div>
                                <h3>Transacciones recientes</h3>
                                <span class="table-card__subtitle">Últimas 10 transacciones realizadas</span>
                            </div>
                        </div>
                        <table class="transactions-table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="t in transacciones" :key="t.usuario + t.fecha" class="transaction-row">
                                    <td>
                                        <div class="user-cell">
                                            <img v-if="t.avatar" :src="t.avatar" class="user-avatar" />
                                            <span v-else class="avatar-placeholder"><i class="pi pi-user"></i></span>
                                            {{ t.usuario }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="type-tag" :class="`type-tag--${t.tipoColor}`">
                                            <i v-if="t.tipoColor === 'green'" class="pi pi-check-circle"></i>
                                            <i v-else-if="t.tipoColor === 'blue'" class="pi pi-shopping-cart"></i>
                                            <i v-else-if="t.tipoColor === 'orange'" class="pi pi-heart-fill"></i>
                                            <i v-else-if="t.tipoColor === 'red'" class="pi pi-arrow-right"></i>
                                            {{ t.tipo }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ t.descripcion }}</td>
                                    <td class="amount-cell">{{ t.monto }}</td>
                                    <td class="text-muted">{{ t.fecha }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <!-- Rendimiento de contenido -->
                    <section class="content-performance">
                        <div class="content-performance__header">
                            <div>
                                <h3>Rendimiento de tu contenido</h3>
                                <span class="content-performance__subtitle">Análisis de tus publicaciones más
                                    exitosas</span>
                            </div>
                        </div>
                        <div class="content-grid-cards">
                            <div v-for="c in contenido" :key="c.titulo" class="content-card">
                                <div class="content-card__image">
                                    <img :src="c.imagen" :alt="c.titulo" />
                                    <span v-if="c.tipo === 'video'" class="content-card__badge">
                                        <i class="pi pi-play"></i>
                                    </span>
                                    <span v-else-if="c.tipo === 'bloqueado'" class="content-card__badge">
                                        <i class="pi pi-lock"></i>
                                    </span>
                                    <span v-else class="content-card__badge">
                                        <i class="pi pi-image"></i>
                                    </span>
                                    <div class="content-card__overlay">
                                        <span class="content-card__conversion">{{ c.conversion }}</span>
                                    </div>
                                </div>
                                <div class="content-card__body">
                                    <strong>{{ c.titulo }}</strong>
                                    <span class="content-card__date">{{ c.fecha }}</span>
                                    <div class="content-card__stats">
                                        <div>
                                            <span>Vistas</span>
                                            <strong>{{ c.vistas }}</strong>
                                        </div>
                                        <div>
                                            <span>Compras</span>
                                            <strong>{{ c.compras }}</strong>
                                        </div>
                                        <div>
                                            <span>Ingresos</span>
                                            <strong>{{ c.ingresos }}</strong>
                                        </div>
                                        <div>
                                            <span>Conversión</span>
                                            <strong>{{ c.conversion }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Saldo disponible -->
                    <div class="sidebar-card sidebar-card--highlight">
                        <div class="sidebar-card__balance">
                            <span class="balance-label">Saldo disponible</span>
                            <strong class="balance-value">{{ saldoDisponible }}</strong>
                            <span class="balance-note">Disponible para retirar</span>
                        </div>
                        <div class="balance-actions">
                            <PvButton label="Retirar ganancias" icon="pi pi-arrow-right" iconPos="right"
                                class="withdraw-btn" @click="retirarGanancias" />
                        </div>
                    </div>

                    <!-- Próximo pago -->
                    <div class="sidebar-card">
                        <h3>
                            <i class="pi pi-calendar" style="color: var(--brand-red); margin-right: 0.5rem;"></i>
                            Próximo pago
                        </h3>
                        <div class="next-payment">
                            <div class="next-payment__icon-wrapper">
                                <span class="next-payment__icon"><i class="pi pi-calendar"></i></span>
                            </div>
                            <div class="next-payment__info">
                                <strong>{{ proximoPago ? proximoPago.fecha : 'No programado' }}</strong>
                                <span>{{ proximoPago ? 'Pago programado' : 'Sin pagos pendientes' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Método de cobro -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header-row">
                            <h3>
                                <i class="pi pi-credit-card" style="color: var(--brand-red); margin-right: 0.5rem;"></i>
                                Método de cobro
                            </h3>
                            <a href="#" @click.prevent="editarMetodoCobro" class="edit-link">
                                <i class="pi pi-pencil"></i> Editar
                            </a>
                        </div>
                        <div class="payout-method">
                            <div class="payout-method__icon-wrapper">
                                <span class="payout-method__icon"><i class="pi pi-paypal"></i></span>
                            </div>
                            <div class="payout-method__info">
                                <strong>{{ metodoCobro.nombre }}</strong>
                                <span>{{ metodoCobro.email }}</span>
                            </div>
                            <span class="payout-method__status status--active">
                                <i class="pi pi-check-circle"></i> Activo
                            </span>
                        </div>
                    </div>

                    <!-- Consejos -->
                    <div class="sidebar-card tips-card">
                        <h3>
                            <i class="pi pi-lightbulb" style="color: #f59e0b; margin-right: 0.5rem;"></i>
                            Cómo aumentar tus ganancias
                        </h3>
                        <div class="tips-list">
                            <div v-for="tip in consejos" :key="tip.titulo" class="tip-item">
                                <span class="tip-item__icon"><i class="pi" :class="tip.icon"></i></span>
                                <div>
                                    <strong>{{ tip.titulo }}</strong>
                                    <p>{{ tip.desc }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PvButton from 'primevue/button';

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
            nombre: 'Invitado',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false,
            rol: 'invitado',
        })
    },
    metricas: {
        type: Array,
        default: () => []
    },
    meses: {
        type: Array,
        default: () => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun']
    },
    ingresosMensuales: {
        type: Array,
        default: () => [0, 0, 0, 0, 0, 0]
    },
    transacciones: {
        type: Array,
        default: () => []
    },
    contenido: {
        type: Array,
        default: () => []
    },
    consejos: {
        type: Array,
        default: () => []
    },
    saldoDisponible: {
        type: String,
        default: '$0.00 MXN'
    },
    proximoPago: {
        type: Object,
        default: null
    },
    metodoCobro: {
        type: Object,
        default: () => ({
            nombre: 'Pendiente de configurar',
            email: 'Configura tu método de cobro'
        })
    }
});

// ============================================================
// STATE
// ============================================================
const hoveredIndex = ref(null);

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
    };
});

// ============================================================
// TOTAL DE INGRESOS PARA LA GRÁFICA
// ============================================================
const totalIngresos = computed(() => {
    const total = props.ingresosMensuales.reduce((a, b) => a + b, 0);
    return '$' + formatNumber(total);
});

const formatNumber = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

// ============================================================
// GRÁFICA DE INGRESOS - MESES EN ESPAÑOL
// ============================================================
// Los meses ahora vienen desde el controlador en español
const chartWidth = 620;
const chartHeight = 220;
const chartPadding = 24;

const chartMax = computed(() => {
    const max = Math.max(...props.ingresosMensuales, 1);
    return Math.max(max * 1.2, 30000);
});

const puntosLinea = computed(() => {
    const data = props.ingresosMensuales;
    const step = (chartWidth - chartPadding * 2) / (data.length - 1);
    return data.map((valor, i) => {
        const x = chartPadding + step * i;
        const y = chartHeight - (valor / chartMax.value) * chartHeight;
        return { x, y, valor };
    });
});

const lineaPath = computed(() =>
    puntosLinea.value.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ')
);

const areaPath = computed(() => {
    const puntos = puntosLinea.value;
    if (puntos.length === 0) return '';
    const primero = puntos[0];
    const ultimo = puntos[puntos.length - 1];
    return `${lineaPath.value} L ${ultimo.x} ${chartHeight} L ${primero.x} ${chartHeight} Z`;
});

// ============================================================
// FUNCIONES
// ============================================================
function retirarGanancias() {
    console.log('Retirar ganancias');
}

function editarMetodoCobro() {
    console.log('Editar método de cobro');
}

// ============================================================
// DEPURACIÓN
// ============================================================
onMounted(() => {
    console.log('🔍 Usuario en GananciasCreador:', usuario.value);
    console.log('📊 Meses en español:', props.meses);
});
</script>

<style scoped>
:root {
    --brand-red: #c81e3a;
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
    --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-full: 999px;
}

.ganancias-page {
    font-family: var(--font-sans);
    color: var(--ink);
    background: #f0f2f5;
    -webkit-font-smoothing: antialiased;
    max-width: 1500px;
    margin: 0 auto;
    padding: 1.25rem 2rem 0;
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0;
}

.hero__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 380px;
    background: linear-gradient(145deg, #1a1a1a 0%, var(--ink) 100%);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    position: relative;
}

.hero__grid::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: radial-gradient(ellipse, rgba(200, 30, 58, 0.08) 0%, transparent 70%);
    pointer-events: none;
}

.hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 2.5rem 2.5rem;
    color: #ffffff;
    position: relative;
    z-index: 2;
}

.hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    padding: 0.35rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.7rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 0.8rem;
    width: fit-content;
}

.hero__badge i {
    color: var(--brand-red);
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
    color: var(--brand-red);
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
    font-size: 2.4rem;
    font-weight: 500;
    line-height: 1.1;
    letter-spacing: -0.01em;
    margin: 0;
}

.hero__title-highlight {
    color: var(--brand-red);
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

.hero__media-glow {
    position: absolute;
    width: 80%;
    height: 80%;
    background: radial-gradient(circle, rgba(200, 30, 58, 0.15), transparent 70%);
    pointer-events: none;
    z-index: 1;
}

.hero__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
    position: relative;
    z-index: 2;
}

.hero:hover .hero__img {
    transform: scale(1.03);
}

.hero__fade {
    position: absolute;
    inset: 0;
    width: 35%;
    background: linear-gradient(to right, var(--ink), rgba(23, 20, 18, 0.05));
    z-index: 3;
}

/* =========================================================================
   KPIS GRID - SOLO UNA VEZ
   ========================================================================= */
.kpis-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
    margin: 1.25rem auto 0;
    max-width: 1400px;
}

@media (max-width: 1300px) {
    .kpis-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 700px) {
    .kpis-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .kpis-grid {
        grid-template-columns: 1fr;
    }
}

.kpi-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.kpi-card__icon-wrapper {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.kpi-card__icon {
    color: var(--brand-red);
    font-size: 1.1rem;
}

.kpi-card__content {
    flex: 1;
    min-width: 0;
}

.kpi-card__title {
    font-size: 0.7rem;
    color: var(--muted);
    display: block;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.kpi-card__value {
    font-size: 1.1rem;
    font-weight: 800;
    display: block;
    margin: 0.1rem 0;
}

.kpi-card__trend {
    font-size: 0.65rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-full);
}

.kpi-card__trend.trend-up {
    color: #22c55e;
    background: rgba(34, 197, 94, 0.08);
}

.kpi-card__trend.trend-down {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.08);
}

.kpi-card__comparativa {
    color: var(--muted-light);
    font-weight: 400;
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 1.75rem auto 0;
    padding: 0 0 2.5rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
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
   CHART CARD
   ========================================================================= */
.chart-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.chart-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.9rem;
}

.chart-card__header h3 {
    font-size: 0.98rem;
    margin: 0;
}

.chart-card__subtitle {
    font-size: 0.75rem;
    color: var(--muted);
}

.chart-card__actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-select {
    position: relative;
    display: inline-block;
}

.chart-select select {
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 0.4rem 2rem 0.4rem 0.7rem;
    font-size: 0.75rem;
    color: var(--ink);
    background: var(--surface);
    appearance: none;
    cursor: pointer;
    font-weight: 500;
}

.chart-select select:focus {
    border-color: var(--brand-red);
    outline: none;
}

.chart-select i {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted-light);
    font-size: 0.6rem;
    pointer-events: none;
}

.chart-legend {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
    font-size: 0.75rem;
    color: #55555a;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.legend-item--total {
    margin-left: auto;
    font-weight: 600;
    color: var(--ink);
}

.legend-dot {
    width: 14px;
    height: 3px;
    border-radius: 2px;
    display: inline-block;
}

.legend-dot--solid {
    background: var(--brand-red);
}

.legend-dot--total {
    background: #6366f1;
}

.chart-container {
    position: relative;
}

.chart-svg {
    width: 100%;
    height: auto;
    display: block;
}

.chart-axis-label {
    font-size: 10px;
    fill: #a5a5aa;
}

.chart-y-labels {
    display: none;
}

/* =========================================================================
   TABLE CARD
   ========================================================================= */
.table-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.table-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.table-card__header h3 {
    font-size: 0.98rem;
    margin: 0;
}

.table-card__subtitle {
    font-size: 0.75rem;
    color: var(--muted);
}

.transactions-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}

.transactions-table th {
    text-align: left;
    color: #a5a5aa;
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 0.5rem 0.5rem;
    border-bottom: 1px solid #f0f0f2;
}

.transactions-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid #f5f5f6;
    color: #2a2a2e;
}

.transaction-row:hover {
    background: var(--surface);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-weight: 600;
}

.user-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f0f0f2;
    color: #a5a5aa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.type-tag {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.type-tag--green {
    background: #eefaf1;
    color: #1c7a3c;
}

.type-tag--blue {
    background: #eaf2fd;
    color: #2563eb;
}

.type-tag--orange {
    background: #fef3e2;
    color: #b5690a;
}

.type-tag--red {
    background: #fee2e2;
    color: #dc2626;
}

.type-tag--gray {
    background: #f3f4f6;
    color: #6b7280;
}

.text-muted {
    color: #8a8a90;
}

.amount-cell {
    font-weight: 700;
}

/* =========================================================================
   CONTENT PERFORMANCE
   ========================================================================= */
.content-performance {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.content-performance__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.content-performance__header h3 {
    font-size: 0.98rem;
    margin: 0;
}

.content-performance__subtitle {
    font-size: 0.75rem;
    color: var(--muted);
}

.content-grid-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.1rem;
}

@media (max-width: 900px) {
    .content-grid-cards {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .content-grid-cards {
        grid-template-columns: 1fr;
    }
}

.content-card {
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.content-card__image {
    position: relative;
    aspect-ratio: 4/5;
}

.content-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.content-card__badge {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.55);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.content-card__overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem 0.5rem 0.5rem;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
}

.content-card__conversion {
    background: rgba(34, 197, 94, 0.9);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-full);
}

.content-card__body {
    padding: 0.9rem;
}

.content-card__body strong {
    display: block;
    font-size: 0.82rem;
    margin-bottom: 0.15rem;
}

.content-card__date {
    font-size: 0.7rem;
    color: #a5a5aa;
    display: block;
    margin-bottom: 0.7rem;
}

.content-card__stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
}

.content-card__stats div {
    display: flex;
    flex-direction: column;
}

.content-card__stats span {
    font-size: 0.65rem;
    color: #a5a5aa;
}

.content-card__stats strong {
    font-size: 0.82rem;
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
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.sidebar-card:hover {
    box-shadow: var(--shadow-hover);
}

.sidebar-card--highlight {
    background: linear-gradient(145deg, #1a1a1a, var(--ink));
    border-color: transparent;
}

.sidebar-card--highlight *:not(.balance-actions *) {
    color: #fff;
}

.sidebar-card h3 {
    font-size: 0.95rem;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
}

.sidebar-card__header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.sidebar-card__header-row h3 {
    margin: 0;
}

.edit-link {
    color: var(--brand-red);
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.edit-link:hover {
    background: var(--brand-soft);
}

/* Balance */
.sidebar-card__balance {
    text-align: center;
    padding: 0.5rem 0 0.8rem;
}

.balance-label {
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.6);
    display: block;
}

.balance-value {
    font-size: 1.8rem;
    font-weight: 800;
    display: block;
    margin: 0.2rem 0;
}

.balance-note {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.4);
    display: block;
}

.balance-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.withdraw-btn {
    width: 100%;
    font-weight: 700;
    border-radius: 8px;
}

/* Next Payment */
.next-payment {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.next-payment__icon-wrapper {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #fdf1f2;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.next-payment__icon {
    color: var(--brand-red);
    font-size: 0.9rem;
}

.next-payment__info {
    flex: 1;
}

.next-payment__info strong {
    display: block;
    font-size: 0.85rem;
}

.next-payment__info span {
    font-size: 0.75rem;
    color: var(--muted);
}

/* Payout Method */
.payout-method {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.payout-method__icon-wrapper {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #f2f2f4;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.payout-method__icon {
    font-size: 1rem;
}

.payout-method__info {
    flex: 1;
}

.payout-method__info strong {
    display: block;
    font-size: 0.85rem;
}

.payout-method__info span {
    font-size: 0.75rem;
    color: var(--muted);
}

.payout-method__status {
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.status--active {
    color: #22c55e;
    background: rgba(34, 197, 94, 0.1);
}

/* Tips */
.tips-card h3 {
    color: var(--ink);
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tip-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.tip-item__icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #fdf1f2;
    color: var(--brand-red);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tip-item strong {
    display: block;
    font-size: 0.82rem;
    margin-bottom: 0.1rem;
}

.tip-item p {
    font-size: 0.75rem;
    color: var(--muted);
    margin: 0;
    line-height: 1.4;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .ganancias-page {
        padding: 1rem 1rem 0;
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
}

@media (max-width: 768px) {
    .ganancias-page {
        padding: 0.75rem 0.75rem 0;
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

    .content-grid {
        padding: 0 0 2rem;
    }

    .chart-card__header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }

    .chart-legend {
        flex-wrap: wrap;
    }

    .legend-item--total {
        margin-left: 0;
    }

    .transactions-table {
        font-size: 0.7rem;
    }

    .transactions-table th,
    .transactions-table td {
        padding: 0.4rem 0.3rem;
    }

    .sidebar-card {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .ganancias-page {
        padding: 0.5rem 0.5rem 0;
    }

    .hero__title {
        font-size: 1.2rem;
    }

    .hero__copy {
        padding: 1rem;
    }

    .hero__media {
        min-height: 120px;
    }

    .user-cell {
        font-size: 0.7rem;
    }

    .type-tag {
        font-size: 0.6rem;
        padding: 0.15rem 0.4rem;
    }

    .balance-value {
        font-size: 1.2rem;
    }

    .kpi-card {
        padding: 0.8rem;
    }

    .kpi-card__icon-wrapper {
        width: 36px;
        height: 36px;
    }
}
</style>