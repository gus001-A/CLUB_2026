// resources/js/composables/useLlamada.js
import { ref, shallowRef } from 'vue';
import axios from 'axios';

const RTC_CONFIG = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
    ],
};

export function useLlamada(usuarioActualId) {
    const llamadaActiva = ref(null);
    const estadoLocal = ref('inactiva');
    const streamLocal = shallowRef(null);
    const streamRemoto = shallowRef(null);
    const microfonoActivo = ref(true);
    const camaraActiva = ref(true);
    const duracionSegundos = ref(0);

    let peerConnection = null;
    let canalWhisper = null;
    let intervaloDuracion = null;
    let ofertaPendiente = null;

    function esVideo() {
        return llamadaActiva.value?.tipo === 'video';
    }

    function esLlamante() {
        return llamadaActiva.value?.llamante_id === usuarioActualId;
    }

    // ---------------------------------------------------------------
    // Escuchar llamadas entrantes
    // ---------------------------------------------------------------
    function escucharLlamadas(chatId) {
        if (!window.Echo) {
            console.warn('Echo no disponible');
            return;
        }

        try {
            canalWhisper = window.Echo.private(`chat.${chatId}`)
                .listen('.llamada.actualizada', async (e) => {
                    llamadaActiva.value = e.llamada;

                    if (e.llamada.estado === 'sonando' && e.llamada.receptor_id === usuarioActualId) {
                        estadoLocal.value = 'sonando_entrante';
                    }

                    if (e.llamada.estado === 'en_curso' && estadoLocal.value !== 'en_curso') {
                        estadoLocal.value = 'en_curso';
                        iniciarContadorDuracion();
                    }

                    if (['finalizada', 'rechazada', 'perdida'].includes(e.llamada.estado)) {
                        limpiarLlamada();
                    }
                })
                .listenForWhisper('webrtc-oferta', async (payload) => {
                    ofertaPendiente = payload.sdp;
                    if (peerConnection) {
                        await aceptarOferta(payload.sdp);
                    }
                })
                .listenForWhisper('webrtc-respuesta', async (payload) => {
                    if (peerConnection) {
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(payload.sdp));
                    }
                })
                .listenForWhisper('webrtc-candidato', async (payload) => {
                    if (peerConnection && payload.candidate) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(payload.candidate));
                        } catch (err) {
                            console.warn('No se pudo agregar el ICE candidate:', err);
                        }
                    }
                });
        } catch (error) {
            console.error('Error al escuchar llamadas:', error);
        }
    }

    function dejarDeEscuchar(chatId) {
        try {
            if (window.Echo) {
                window.Echo.leave(`chat.${chatId}`);
            }
            canalWhisper = null;
        } catch (error) {
            console.warn('Error al dejar de escuchar:', error);
        }
    }

    // ---------------------------------------------------------------
    // Iniciar / contestar / colgar
    // ---------------------------------------------------------------
    async function iniciarLlamada(chatId, tipo) {
        try {
            const { data } = await axios.post(`/chats/${chatId}/llamadas`, { tipo });
            llamadaActiva.value = data.llamada;
            estadoLocal.value = 'sonando_saliente';

            await prepararMedios(tipo === 'video');
            await crearOferta();
        } catch (error) {
            console.error('Error al iniciar llamada:', error);
            limpiarLlamada();
            throw error;
        }
    }

    async function contestarLlamada() {
        if (!llamadaActiva.value) return;

        try {
            await axios.post(`/llamadas/${llamadaActiva.value.id}/contestar`);
            await prepararMedios(esVideo());
            estadoLocal.value = 'en_curso';
            iniciarContadorDuracion();

            // Si hay oferta pendiente, procesarla
            if (ofertaPendiente) {
                await aceptarOferta(ofertaPendiente);
                ofertaPendiente = null;
            }
        } catch (error) {
            console.error('Error al contestar llamada:', error);
            limpiarLlamada();
            throw error;
        }
    }

    async function colgarLlamada(motivo = 'colgada') {
        try {
            if (llamadaActiva.value) {
                await axios.post(`/llamadas/${llamadaActiva.value.id}/colgar`, { motivo }).catch(() => { });
            }
        } catch (error) {
            console.warn('Error al colgar llamada:', error);
        } finally {
            limpiarLlamada();
        }
    }

    function rechazarLlamada() {
        return colgarLlamada('rechazada');
    }

    // ---------------------------------------------------------------
    // WebRTC
    // ---------------------------------------------------------------
    async function prepararMedios(conVideo) {
        try {
            streamLocal.value = await navigator.mediaDevices.getUserMedia({
                audio: true,
                video: conVideo ? { width: 640, height: 480 } : false,
            });

            peerConnection = new RTCPeerConnection(RTC_CONFIG);

            streamLocal.value.getTracks().forEach((track) => {
                peerConnection.addTrack(track, streamLocal.value);
            });

            const remoto = new MediaStream();
            streamRemoto.value = remoto;

            peerConnection.ontrack = (event) => {
                event.streams[0].getTracks().forEach((track) => remoto.addTrack(track));
            };

            peerConnection.onicecandidate = (event) => {
                if (event.candidate && canalWhisper) {
                    canalWhisper.whisper('webrtc-candidato', { candidate: event.candidate });
                }
            };

            peerConnection.onconnectionstatechange = () => {
                if (peerConnection?.connectionState === 'failed') {
                    colgarLlamada('error');
                }
            };
        } catch (error) {
            console.error('Error al preparar medios:', error);
            throw error;
        }
    }

    async function crearOferta() {
        if (!peerConnection) return;

        const oferta = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(oferta);
        if (canalWhisper) {
            canalWhisper.whisper('webrtc-oferta', { sdp: oferta });
        }
    }

    async function aceptarOferta(sdp) {
        if (!peerConnection) {
            // Guardar la oferta para procesarla después
            ofertaPendiente = sdp;
            return;
        }

        try {
            await peerConnection.setRemoteDescription(new RTCSessionDescription(sdp));
            const respuesta = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(respuesta);
            if (canalWhisper) {
                canalWhisper.whisper('webrtc-respuesta', { sdp: respuesta });
            }
        } catch (error) {
            console.error('Error al aceptar oferta:', error);
            throw error;
        }
    }

    // ---------------------------------------------------------------
    // Controles en llamada
    // ---------------------------------------------------------------
    function alternarMicrofono() {
        microfonoActivo.value = !microfonoActivo.value;
        streamLocal.value?.getAudioTracks().forEach((t) => (t.enabled = microfonoActivo.value));
    }

    function alternarCamara() {
        camaraActiva.value = !camaraActiva.value;
        streamLocal.value?.getVideoTracks().forEach((t) => (t.enabled = camaraActiva.value));
    }

    function iniciarContadorDuracion() {
        duracionSegundos.value = 0;
        clearInterval(intervaloDuracion);
        intervaloDuracion = setInterval(() => duracionSegundos.value++, 1000);
    }

    function limpiarLlamada() {
        clearInterval(intervaloDuracion);
        try {
            streamLocal.value?.getTracks().forEach((t) => t.stop());
            streamRemoto.value?.getTracks().forEach((t) => t.stop());
            peerConnection?.close();
        } catch (error) {
            console.warn('Error al limpiar llamada:', error);
        }

        peerConnection = null;
        streamLocal.value = null;
        streamRemoto.value = null;
        llamadaActiva.value = null;
        estadoLocal.value = 'inactiva';
        duracionSegundos.value = 0;
        microfonoActivo.value = true;
        camaraActiva.value = true;
        ofertaPendiente = null;
    }

    return {
        llamadaActiva,
        estadoLocal,
        streamLocal,
        streamRemoto,
        microfonoActivo,
        camaraActiva,
        duracionSegundos,
        esVideo,
        esLlamante,

        escucharLlamadas,
        dejarDeEscuchar,
        iniciarLlamada,
        contestarLlamada,
        colgarLlamada,
        rechazarLlamada,
        alternarMicrofono,
        alternarCamara,
    };
}