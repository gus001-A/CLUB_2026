<script setup>
import { ref } from 'vue';

const emit = defineEmits(['seleccionar']);

const categorias = [
    {
        nombre: 'Sonrisas',
        icono: 'pi-face-smile',
        emojis: ['😀', '😄', '😁', '😊', '😍', '🥰', '😘', '😉', '😜', '🤗', '🤩', '😎', '🥳', '😏', '😌', '🙂'],
    },
    {
        nombre: 'Gestos',
        icono: 'pi-thumbs-up',
        emojis: ['👍', '👎', '👏', '🙌', '🤝', '🙏', '💪', '👌', '✌️', '🤙', '👋', '🫶', '💃', '🕺', '🤳', '🫡'],
    },
    {
        nombre: 'Amor',
        icono: 'pi-heart',
        emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '💕', '💞', '💓', '💗', '💖', '🔥', '✨', '💋'],
    },
    {
        nombre: 'Fiesta',
        icono: 'pi-star',
        emojis: ['🎉', '🎊', '🥂', '🍾', '🍷', '🍸', '🎶', '🎵', '💃', '🕶️', '💫', '🌙', '⭐', '🌹', '🍫', '🍓'],
    },
];

const categoriaActiva = ref(0);
</script>

<template>
    <div class="emoji-picker">
        <div class="emoji-picker__grid">
            <button
                v-for="(emoji, i) in categorias[categoriaActiva].emojis"
                :key="i"
                type="button"
                class="emoji-picker__item"
                @click="emit('seleccionar', emoji)"
            >
                {{ emoji }}
            </button>
        </div>
        <div class="emoji-picker__tabs">
            <button
                v-for="(cat, i) in categorias"
                :key="cat.nombre"
                type="button"
                class="emoji-picker__tab"
                :class="{ active: categoriaActiva === i }"
                :title="cat.nombre"
                @click="categoriaActiva = i"
            >
                <i class="pi" :class="cat.icono"></i>
            </button>
        </div>
    </div>
</template>

<style scoped>
.emoji-picker {
    width: 280px;
    background: #fff;
    border: 1px solid #ececee;
    border-radius: 14px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    overflow: hidden;
}
.emoji-picker__grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.15rem;
    padding: 0.6rem;
    max-height: 200px;
    overflow-y: auto;
}
.emoji-picker__item {
    border: none;
    background: none;
    font-size: 1.35rem;
    line-height: 1;
    padding: 0.4rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.emoji-picker__item:hover {
    background: #f7f7f8;
}
.emoji-picker__tabs {
    display: flex;
    border-top: 1px solid #f0f0f2;
    padding: 0.3rem;
    gap: 0.2rem;
}
.emoji-picker__tab {
    flex: 1;
    border: none;
    background: none;
    padding: 0.5rem 0;
    border-radius: 8px;
    color: #a5a5aa;
    cursor: pointer;
    transition: all 0.15s ease;
}
.emoji-picker__tab.active {
    background: #fdf1f2;
    color: #c81e3a;
}
</style>
