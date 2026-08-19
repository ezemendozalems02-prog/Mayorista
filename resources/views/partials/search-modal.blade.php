<!-- Global Search Modal -->
<div x-data="{ 
        isOpen: false, 
        search: '', 
        results: [], 
        isLoading: false,
        selectedIndex: -1,
        
        init() {
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.openSearch();
                }
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeSearch();
                }
            });
        },
        
        openSearch() {
            this.isOpen = true;
            this.$nextTick(() => { this.$refs.searchInput.focus(); });
        },
        
        closeSearch() {
            this.isOpen = false;
            this.search = '';
            this.results = [];
        },
        
        async performSearch() {
            if (this.search.length < 2) {
                this.results = [];
                return;
            }
            
            this.isLoading = true;
            try {
                const response = await fetch(`{{ route('global.search') }}?q=${encodeURIComponent(this.search)}`);
                const data = await response.json();
                this.results = data.results;
                this.selectedIndex = this.results.length > 0 ? 0 : -1;
                
                // Re-init icons after Alpine renders the results
                this.$nextTick(() => { 
                    if (window.lucide) lucide.createIcons();
                });
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        navigate(direction) {
            if (this.results.length === 0) return;
            
            if (direction === 'down') {
                this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
            } else {
                this.selectedIndex = (this.selectedIndex - 1 + this.results.length) % this.results.length;
            }
            
            this.$nextTick(() => {
                const activeEl = this.$refs.resultsList.querySelector(`[data-index='${this.selectedIndex}']`);
                if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
            });
        },
        
        selectResult() {
            if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                window.location.href = this.results[this.selectedIndex].url;
            }
        }
    }" 
    x-init="init()"
    @open-search.window="openSearch()"
    @close-search.window="closeSearch()"
    x-show="isOpen" 
    class="fixed inset-0 z-[100] overflow-y-auto p-4 sm:p-6 md:p-20" 
    style="display: none;"
    role="dialog" 
    aria-modal="true">
    
    <!-- Background backdrop -->
    <div x-show="isOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeSearch()"
        class="fixed inset-0 bg-dark/60 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal panel -->
    <div x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="mx-auto max-w-2xl transform divide-y divide-gray-100 dark:divide-white/5 overflow-hidden rounded-2xl bg-white dark:bg-dark-alt shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
        
        <div class="relative flex items-center px-4">
            <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
            <input type="text"
                x-ref="searchInput"
                x-model="search"
                @input.debounce.300ms="performSearch()"
                @keydown.down.prevent="navigate('down')"
                @keydown.up.prevent="navigate('up')"
                @keydown.enter.prevent="selectResult()"
                class="h-14 w-full border-0 bg-transparent pl-4 pr-4 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-0 sm:text-sm outline-none" 
                placeholder="Buscar clientes, equipos, ventas, reparaciones..." 
                role="combobox" 
                aria-expanded="false" 
                aria-controls="options">
            
            <button @click="closeSearch()" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <span class="text-[10px] font-black uppercase tracking-widest bg-gray-100 dark:bg-dark py-1 px-2 rounded-lg border border-gray-200 dark:border-white/5 shadow-sm">Esc</span>
            </button>
        </div>

        <!-- Results -->
        <div x-show="results.length > 0" class="max-h-96 scroll-py-2 overflow-y-auto" x-ref="resultsList">
            <ul class="p-2 text-sm text-gray-700 dark:text-gray-300" id="options" role="listbox">
                <template x-for="(result, index) in results" :key="index">
                    <li :id="'option-' + index"
                        :data-index="index"
                        @click="window.location.href = result.url"
                        @mouseenter="selectedIndex = index"
                        class="cursor-pointer select-none rounded-xl px-4 py-3 flex items-center transition-all duration-200"
                        :class="selectedIndex === index ? 'bg-primary/10 text-primary shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-white/5'"
                        role="option" 
                        tabindex="-1">
                        
                        <!-- Icon mapping based on result.icon (using Lucide) -->
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-100 dark:bg-dark flex items-center justify-center mr-4 transition-colors"
                            :class="selectedIndex === index ? 'bg-primary/20 text-primary' : 'text-gray-400'">
                            <i :data-lucide="result.icon" class="w-5 h-5"></i>
                        </div>
                        
                        <div class="flex-auto truncate">
                            <p class="font-black text-xs uppercase tracking-widest italic" :class="selectedIndex === index ? 'text-primary' : 'text-gray-900 dark:text-gray-100'" x-text="result.title"></p>
                            <p class="text-[10px] font-medium opacity-60" x-text="result.subtitle"></p>
                        </div>
                        
                        <div class="shrink-0 ml-3">
                            <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg border" 
                                :class="selectedIndex === index ? 'border-primary/30 bg-primary/10 text-primary' : 'border-gray-100 dark:border-white/5 text-gray-400'"
                                x-text="result.type"></span>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        <!-- Empty state / Feedback -->
        <div x-show="search.length >= 2 && results.length === 0 && !isLoading" 
            class="px-6 py-14 text-center sm:px-14 flex flex-col items-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-dark rounded-full flex items-center justify-center mb-4">
                <i data-lucide="search-x" class="h-8 w-8 text-gray-400"></i>
            </div>
            <p class="text-[11px] font-black uppercase tracking-widest text-gray-900 dark:text-gray-100 italic">No encontramos nada relacionado</p>
            <p class="mt-2 text-[10px] text-gray-400 font-medium">Probá buscando por IMEI, nombre de cliente o número de orden.</p>
        </div>

        <!-- Loading state -->
        <div x-show="isLoading" class="px-6 py-14 text-center sm:px-14 flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-4"></div>
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 italic animate-pulse">Buscando...</p>
        </div>

        <!-- Search Footer -->
        <div class="flex items-center justify-between px-6 py-3 bg-gray-50 dark:bg-white/5 text-[9px] font-black uppercase tracking-[0.2em] text-gray-400">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1"><kbd class="bg-gray-100 dark:bg-dark p-1 rounded min-w-[18px] text-center shadow-sm">⏎</kbd> Seleccionar</span>
                <span class="flex items-center gap-1"><kbd class="bg-gray-100 dark:bg-dark p-1 rounded min-w-[18px] text-center shadow-sm">↓↑</kbd> Navegar</span>
            </div>
            <div class="flex items-center gap-1">
                <i data-lucide="zap" class="w-3 h-3 text-primary"></i> Mito Search
            </div>
        </div>
    </div>
</div>


