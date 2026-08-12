<template>
    <div 
        class="custom-avatar"
        :style="{
            width: size,
            height: size,
            borderRadius: '50%',
            backgroundImage: hasImage ? 'url(' + imageUrl + ')' : 'none',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundColor: hasImage ? 'transparent' : '#C81E3A',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: '#ffffff',
            fontWeight: '700',
            fontSize: fontSize,
            flexShrink: 0,
            overflow: 'hidden',
            border: hasImage ? '2px solid #f0f0f0' : 'none'
        }"
    >
        {{ hasImage ? '' : (label || '?') }}
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    image: {
        type: String,
        default: null
    },
    label: {
        type: String,
        default: '?'
    },
    size: {
        type: String,
        default: 'normal',
        validator: (value) => ['small', 'normal', 'large', 'xlarge'].includes(value)
    }
});

const sizeMap = {
    small: '32px',
    normal: '40px',
    large: '48px',
    xlarge: '64px'
};

const size = computed(() => sizeMap[props.size] || '40px');

const fontSize = computed(() => {
    const sizeValue = parseInt(size.value);
    if (sizeValue >= 64) return '24px';
    if (sizeValue >= 48) return '18px';
    if (sizeValue >= 40) return '14px';
    return '12px';
});

const hasImage = computed(() => {
    return props.image && props.image !== '/images/shared/avatar-default.jpg';
});

const imageUrl = computed(() => props.image || '/images/shared/avatar-default.jpg');
</script>

<style scoped>
.custom-avatar {
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.custom-avatar:hover {
    transform: scale(1.05);
}
</style>