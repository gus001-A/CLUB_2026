// resources/js/composables/useChat.js
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

export function useChat(usuarioActualId) {
    const page = usePage();

    const chats = ref([...(page.props.conversaciones || [])]);
    const cargandoChats = ref(false);

    const chatActivoId = ref(null);
    const mensajes = ref([]);
    const cargandoMensajes = ref(false);
    const enviando = ref(false);
    const otroEstaEscribiendo = ref(false);

    const otroParticipante = computed(() => {
        const chat = chats.value.find((c) => c.id === chatActivoId.value);
        return chat ? chat.usuario : null;
    });

    // ---------------------------------------------------------------
    // Cargar conversaciones
    // ---------------------------------------------------------------
    async function cargarChats() {
        cargandoChats.value = true;
        try {
            const { data } = await axios.get('/chats');
            if (data && data.chats) {
                chats.value = data.chats;
            } else {
                chats.value = [...(page.props.conversaciones || [])];
            }
        } catch (error) {
            console.error('Error al cargar chats:', error);
            // Fallback a props
            chats.value = [...(page.props.conversaciones || [])];
        } finally {
            cargandoChats.value = false;
        }
    }

    // ---------------------------------------------------------------
    // Abrir una conversación
    // ---------------------------------------------------------------
    async function abrirChat(chatId) {
        if (!chatId) return;

        chatActivoId.value = chatId;
        cargandoMensajes.value = true;
        otroEstaEscribiendo.value = false;

        try {
            const { data } = await axios.get(`/chats/${chatId}/mensajes`);
            mensajes.value = data.mensajes || [];

            if (data.otro_participante) {
                const chat = chats.value.find(c => c.id === chatId);
                if (chat) {
                    chat.usuario = data.otro_participante;
                }
            }

            // Marcar como leídos
            await marcarComoLeidos(chatId);

        } catch (error) {
            console.error('Error al abrir la conversación:', error);
            mensajes.value = [];
        } finally {
            cargandoMensajes.value = false;
        }
    }

    // ---------------------------------------------------------------
    // Cargar mensajes anteriores (placeholder)
    // ---------------------------------------------------------------
    async function cargarMensajesAnteriores() {
        return;
    }

    // ---------------------------------------------------------------
    // Enviar mensaje de texto
    // ---------------------------------------------------------------
    async function enviarTexto(texto) {
        if (!texto?.trim() || !chatActivoId.value) return;

        enviando.value = true;
        try {
            const { data } = await axios.post(`/chats/${chatActivoId.value}/mensajes`, {
                texto: texto.trim(),
            });

            if (data.mensaje) {
                mensajes.value.push(data.mensaje);
                actualizarPreviewChat(chatActivoId.value, data.mensaje);
                return data.mensaje;
            }

            throw new Error(data.message || 'No se pudo enviar el mensaje.');
        } catch (error) {
            console.error('Error al enviar mensaje:', error);
            throw error;
        } finally {
            enviando.value = false;
        }
    }

    // ---------------------------------------------------------------
    // Enviar archivo (foto/video)
    // ---------------------------------------------------------------
    async function enviarArchivo(archivo, onProgress) {
        if (!archivo || !chatActivoId.value) return;

        const formData = new FormData();
        formData.append('archivo', archivo);

        enviando.value = true;
        try {
            const { data } = await axios.post(`/chats/${chatActivoId.value}/mensajes`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (onProgress && progressEvent.total) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        onProgress(percentCompleted);
                    }
                },
            });

            if (data.mensaje) {
                mensajes.value.push(data.mensaje);
                actualizarPreviewChat(chatActivoId.value, data.mensaje);
                return data.mensaje;
            }

            throw new Error(data.message || 'No se pudo enviar el archivo.');
        } catch (error) {
            console.error('Error al enviar archivo:', error);
            throw error;
        } finally {
            enviando.value = false;
        }
    }

    // ---------------------------------------------------------------
    // Borrar mensaje
    // ---------------------------------------------------------------
    async function borrarMensaje(mensajeId) {
        try {
            await axios.delete(`/mensajes/${mensajeId}`);
            const idx = mensajes.value.findIndex((m) => m.id === mensajeId);
            if (idx !== -1) mensajes.value.splice(idx, 1);
        } catch (error) {
            console.error('Error al borrar mensaje:', error);
            throw error;
        }
    }

    // ---------------------------------------------------------------
    // Marcar como leídos
    // ---------------------------------------------------------------
    async function marcarComoLeidos(chatId) {
        if (!chatId) return;
        try {
            await axios.post(`/chats/${chatId}/marcar-leido`);
            const chat = chats.value.find((c) => c.id === chatId);
            if (chat) chat.no_leidos = 0;
        } catch (error) {
            console.warn('Error al marcar como leídos:', error.message);
            // No hacemos throw para no interrumpir el flujo
        }
    }

    // ---------------------------------------------------------------
    // Notificar "escribiendo..."
    // ---------------------------------------------------------------
    let timeoutEscribiendo = null;

    function notificarEscribiendo() {
        if (!chatActivoId.value) return;

        axios.post(`/chats/${chatActivoId.value}/escribiendo`, { escribiendo: true }).catch(() => { });

        clearTimeout(timeoutEscribiendo);
        timeoutEscribiendo = setTimeout(() => {
            axios.post(`/chats/${chatActivoId.value}/escribiendo`, { escribiendo: false }).catch(() => { });
        }, 2000);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    function actualizarPreviewChat(chatId, mensaje) {
        const chat = chats.value.find((c) => c.id === chatId);
        if (!chat) return;
        chat.ultimo_mensaje = mensaje.texto || '📎 Archivo';
        chat.ultimo_mensaje_en = mensaje.created_at;
    }

    return {
        chats,
        cargandoChats,
        cargarChats,
        chatActivoId,
        mensajes,
        otroParticipante,
        cargandoMensajes,
        enviando,
        otroEstaEscribiendo,
        abrirChat,
        cargarMensajesAnteriores,
        enviarTexto,
        enviarArchivo,
        borrarMensaje,
        notificarEscribiendo,
        marcarComoLeidos,
    };
}