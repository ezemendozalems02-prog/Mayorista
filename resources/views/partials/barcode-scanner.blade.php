{{--
    Input de escaneo reutilizable (Fase 7). No hace nada por si solo: al confirmar
    un codigo (Enter desde un lector fisico, o deteccion por camara) dispara el
    evento window "mito:barcode-scanned" con { code }. Cada pantalla que lo incluye
    escucha ese evento y decide que hacer (buscar producto, redirigir, sumar a una
    lista de conteo, etc.) con @mito:barcode-scanned.window="..." en su propio x-data.

    Props opcionales:
      $placeholder (string)
      $autofocus (bool, default true)
--}}
<div x-data="barcodeScanner()" x-init="{{ ($autofocus ?? true) ? '$nextTick(() => $refs.input.focus())' : '' }}"
    class="flex items-center gap-2">
    <div class="relative flex-1 group">
        <i data-lucide="scan-barcode"
            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
        <input type="text" x-ref="input" x-model="code" @keydown.enter.prevent="submitScan()"
            placeholder="{{ $placeholder ?? 'Escaneá o escribí el código de barras...' }}"
            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
    </div>
    <button type="button" @click="toggleCamera()"
        class="p-3 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm transition-all bg-white dark:bg-dark-alt hover:bg-primary hover:text-white"
        title="Escanear con cámara">
        <i data-lucide="camera" class="w-4 h-4"></i>
    </button>

    <!-- Camera Modal -->
    <div x-show="cameraOpen" x-cloak @click.self="closeCamera()"
        class="fixed inset-0 z-[200] bg-black/80 flex items-center justify-center p-6">
        <div class="bg-white dark:bg-dark-alt rounded-3xl p-6 max-w-md w-full space-y-4">
            <video x-ref="video" class="w-full rounded-2xl bg-black aspect-video object-cover" autoplay muted playsinline></video>
            <p class="text-xs text-gray-400 text-center font-bold uppercase tracking-widest">Apuntá la cámara al código de barras</p>
            <button type="button" @click="closeCamera()"
                class="w-full py-3 rounded-2xl bg-gray-100 dark:bg-dark text-gray-500 font-black uppercase text-xs tracking-widest">
                Cancelar
            </button>
        </div>
    </div>
</div>
