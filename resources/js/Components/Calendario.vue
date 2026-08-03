<script setup>
import { computed } from 'vue';

/**
 * Calendario mensual reutilizable, en español.
 * No hace peticiones al servidor por sí mismo — solo recibe los
 * datos del mes actual y avisa (emit) cuando el usuario quiere
 * cambiar de mes o volver a "Hoy". El componente que lo use
 * decide cómo pedir los datos del nuevo mes (Inertia, fetch, etc.)
 *
 * Uso típico (panel admin):
 *   <Calendario
 *       :mes="calendario.mes"
 *       :anio="calendario.anio"
 *       :nombre-mes="calendario.nombreMes"
 *       :dias="calendario.dias"
 *       @cambiar-mes="irMes"
 *       @hoy="irHoy"
 *   />
 *
 * Uso típico (parte pública, con v-model de fecha seleccionada):
 *   <Calendario :mes="mes" :anio="anio" :nombre-mes="nombreMes" :dias="diasConEventos"
 *       @cambiar-mes="delta => cambiarMes(delta)"
 *       @dia-click="dia => verEventosDelDia(dia)" />
 */
const props = defineProps({
    mes: { type: Number, required: true }, // 1-12
    anio: { type: Number, required: true },
    nombreMes: { type: String, required: true }, // ej. "Julio 2026"
    // Objeto { [dia]: ['programado', 'en_vivo', ...] } — los "estados" que tenga cada día
    dias: { type: Object, default: () => ({}) },
    // Mapea cada estado a un color de punto. Puedes pasar los tuyos si tu app usa otros nombres.
    colores: {
        type: Object,
        default: () => ({
            en_vivo: '#ef4444',
            programado: '#fb923c',
            completado: '#10b981',
            cancelado: '#9ca3af',
            borrador: '#facc15',
        }),
    },
    // Leyenda que se muestra debajo del calendario. Pon null si no la quieres.
    leyenda: {
        type: Array,
        default: () => [
            { estado: 'programado', label: 'Programado' },
            { estado: 'en_vivo', label: 'En vivo' },
            { estado: 'completado', label: 'Completado' },
        ],
    },
});

const emit = defineEmits(['cambiar-mes', 'hoy', 'dia-click']);

const diasSemana = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];

const celdas = computed(() => {
    const { mes, anio } = props;

    const primerDiaMes = new Date(anio, mes - 1, 1);
    const ultimoDiaMes = new Date(anio, mes, 0).getDate();
    const offsetInicio = primerDiaMes.getDay(); // 0 = domingo
    const diasMesAnterior = new Date(anio, mes - 1, 0).getDate();

    const hoy = new Date();
    const esHoy = (d) => d === hoy.getDate() && (mes - 1) === hoy.getMonth() && anio === hoy.getFullYear();

    const arr = [];

    // Días del mes anterior (en gris, de relleno)
    for (let i = offsetInicio - 1; i >= 0; i--) {
        arr.push({ dia: diasMesAnterior - i, actual: false, hoy: false, estados: [] });
    }

    // Días del mes actual
    for (let d = 1; d <= ultimoDiaMes; d++) {
        arr.push({ dia: d, actual: true, hoy: esHoy(d), estados: props.dias?.[d] || [] });
    }

    // Días del mes siguiente (relleno hasta completar semanas)
    let diaSiguiente = 1;
    while (arr.length % 7 !== 0) {
        arr.push({ dia: diaSiguiente++, actual: false, hoy: false, estados: [] });
    }

    return arr;
});

function irMes(delta) {
    emit('cambiar-mes', delta);
}

function irHoy() {
    emit('hoy');
}

function clickDia(celda) {
    if (celda.actual) emit('dia-click', celda.dia);
}
</script>

<template>
    <div>
        <!-- Navegación: < mes año > + Hoy -->
        <div style="display:flex;align-items:center;gap:4px;margin-bottom:16px">
            <button
                type="button"
                @click="irMes(-1)"
                style="width:30px;height:30px;flex:none;display:flex;align-items:center;justify-content:center"
                class="rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500"
            >
                <i class="pi pi-chevron-left text-xs"></i>
            </button>
            <span
                style="flex:1 1 0%;min-width:0;text-align:center;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                class="text-sm font-semibold text-gray-800 capitalize"
            >
                {{ nombreMes }}
            </span>
            <button
                type="button"
                @click="irMes(1)"
                style="width:30px;height:30px;flex:none;display:flex;align-items:center;justify-content:center"
                class="rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500"
            >
                <i class="pi pi-chevron-right text-xs"></i>
            </button>
            <button
                type="button"
                @click="irHoy"
                style="height:30px;padding:0 12px;flex:none;display:flex;align-items:center;justify-content:center"
                class="rounded-lg border border-gray-200 hover:bg-gray-50 text-xs font-medium text-gray-600"
            >
                Hoy
            </button>
        </div>

        <!-- Encabezado días de la semana -->
        <div style="display:grid;grid-template-columns:repeat(7,1fr);text-align:center;margin-bottom:8px" class="text-[10px] font-semibold text-gray-400">
            <span v-for="d in diasSemana" :key="d">{{ d }}</span>
        </div>

        <!-- Cuadrícula del mes -->
        <div style="display:grid;grid-template-columns:repeat(7,1fr);row-gap:6px">
            <div
                v-for="(c, i) in celdas"
                :key="i"
                style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2px 0"
                :style="c.actual ? 'cursor:pointer' : ''"
                @click="clickDia(c)"
            >
                <span
                    style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:9999px"
                    class="text-xs"
                    :class="[c.hoy ? 'bg-red-600 text-white font-bold' : (c.actual ? 'text-gray-700' : 'text-gray-300')]"
                >
                    {{ c.dia }}
                </span>
                <div style="display:flex;gap:2px;height:6px;margin-top:2px">
                    <span
                        v-for="est in c.estados.slice(0, 3)"
                        :key="est"
                        style="width:4px;height:4px;border-radius:9999px"
                        :style="{ backgroundColor: colores[est] || '#d1d5db' }"
                    ></span>
                </div>
            </div>
        </div>

        <!-- Leyenda -->
        <div
            v-if="leyenda?.length"
            style="display:flex;align-items:center;justify-content:center;gap:16px;margin-top:16px;padding-top:12px"
            class="border-t border-gray-100 text-[11px] text-gray-500"
        >
            <span v-for="item in leyenda" :key="item.estado" style="display:flex;align-items:center;gap:6px">
                <span style="width:8px;height:8px;border-radius:9999px;flex:none" :style="{ backgroundColor: colores[item.estado] || '#d1d5db' }"></span>
                {{ item.label }}
            </span>
        </div>
    </div>
</template>