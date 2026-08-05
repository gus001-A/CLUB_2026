/**
 * Mapas de tipo/estado/visibilidad de Contenido, usados tal cual en
 * Contenido/Index.vue y Contenido/Show.vue (antes estaban duplicados
 * en los dos archivos).
 *
 * Uso:
 *   import { useContenidoMeta } from '@/composables/useContenidoMeta';
 *   const { tipoLabel, tipoIcono, estadoColores, estadoLabel } = useContenidoMeta();
 */
export function useContenidoMeta() {
    const tipoLabel = {
        foto: 'Foto', video: 'Video', galeria: 'Galería', audio: 'Audio',
        articulo: 'Artículo', documento: 'Documento', exclusivo: 'Exclusivo',
    };
    const tipoIcono = {
        foto: 'pi-image', video: 'pi-video', galeria: 'pi-images', audio: 'pi-volume-up',
        articulo: 'pi-file-edit', documento: 'pi-file', exclusivo: 'pi-star',
    };
    const tipoColor = {
        video: 'bg-red-50 text-red-600', articulo: 'bg-blue-50 text-blue-600', galeria: 'bg-purple-50 text-purple-600',
        audio: 'bg-amber-50 text-amber-600', documento: 'bg-gray-100 text-gray-600', foto: 'bg-pink-50 text-pink-600',
        exclusivo: 'bg-brand/10 text-brand',
    };

    const estadoColores = {
        publicado: 'bg-green-100 text-green-700',
        borrador: 'bg-amber-100 text-amber-700',
        programado: 'bg-blue-100 text-blue-700',
        archivado: 'bg-gray-200 text-gray-600',
    };
    const estadoLabel = { publicado: 'Publicado', borrador: 'Borrador', programado: 'Programado', archivado: 'Archivado' };

    const visibilidadLabel = { publico: 'Público', suscriptores: 'Solo suscriptores', individual: 'Compra individual' };
    const visibilidadIcono = { publico: 'pi-globe', suscriptores: 'pi-users', individual: 'pi-ticket' };

    return { tipoLabel, tipoIcono, tipoColor, estadoColores, estadoLabel, visibilidadLabel, visibilidadIcono };
}