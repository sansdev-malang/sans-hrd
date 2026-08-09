<header id="header"
    class="sticky top-0 z-30 flex items-center justify-between px-6 py-3 bg-white/85 dark:bg-[#09090b]/85 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors duration-200">
    <!-- Sidebar Toggle, Close internally, and Breadcrumb -->
    <div class="flex items-center gap-3">
        <button id="sidebar-toggle"
            class="p-1.5 text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md cursor-pointer transition-colors"
            title="Toggle Sidebar">
            <i data-lucide="panel-left" class="w-4 h-4"></i>
        </button>
        <!-- Breadcrumbs display (sidebar-07 look) -->
        <nav
            class="hidden sm:flex items-center space-x-1.5 text-xs font-medium text-slate-400 dark:text-slate-500 select-none">
            <span class="hover:text-slate-700 dark:hover:text-slate-300 cursor-pointer">{{ setting('app_name', 'SANS HRD') }}</span>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="hover:text-slate-700 dark:hover:text-slate-300 cursor-pointer font-bold">@yield('title', $title ?? 'HRD Pusat')</span>
        </nav>
    </div>

    <!-- Action items -->
    <div class="flex items-center gap-2">
        <!-- Light / Dark Switch Button -->
        <button id="theme-toggle"
            class="p-1.5 text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md cursor-pointer transition-colors"
            title="Toggle Tema">
            <i data-lucide="sun" class="w-4 h-4 hidden dark:block"></i>
            <i data-lucide="moon" class="w-4 h-4 block dark:hidden"></i>
        </button>

        <!-- Notification container with Alpine dropdown -->
        <div class="relative" x-data="{ 
            open: false, 
            pendingCount: {{ $pendingLeavesCount ?? 0 }},
            clearAllNotifications() {
                fetch('{{ route('leave-approvals.index', ['clear_all' => 1]) }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.pendingCount = 0;
                        const listContainer = this.$refs.notificationList;
                        if (listContainer) {
                            listContainer.innerHTML = `
                                <div class='p-6 text-center text-slate-400'>
                                    <i data-lucide='bell-off' class='w-6 h-6 mx-auto mb-1 text-slate-300 dark:text-slate-750'></i>
                                    <p class='text-[10px]'>Tidak ada izin baru baru-baru ini.</p>
                                </div>
                            `;
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        }
                    }
                })
                .catch(err => console.error(err));
            }
        }">
            <button @click="open = !open" id="notify-btn"
                class="relative p-1.5 text-slate-500 hover:text-slate-900 dark:hover:text-slate-205 hover:bg-slate-100 dark:hover:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md cursor-pointer transition-colors"
                title="Notifikasi">
                <i data-lucide="bell" class="w-4 h-4"></i>
                
                <!-- Display badge if there are notifications -->
                <span x-show="pendingCount > 0" style="display: none;" class="absolute top-1 right-1 w-2 h-2 bg-rose-600 dark:bg-rose-400 rounded-full ring-2 ring-white dark:ring-[#09090b]"></span>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open" @click.outside="open = false" 
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 text-left text-xs"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                style="display: none;">
                
                <div class="px-4 py-1.5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/20">
                    <span class="font-bold text-slate-900 dark:text-slate-100">Notifikasi</span>
                    <template x-if="pendingCount > 0">
                        <span x-text="pendingCount + ' Baru'" class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40"></span>
                    </template>
                </div>
                
                <div x-ref="notificationList" class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60">
                    @if(isset($pendingLeaves) && count($pendingLeaves) > 0)
                        @foreach($pendingLeaves as $item)
                            <a href="{{ route('leave-approvals.index', ['read_id' => $item->id]) }}" class="flex items-start gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-900/40 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-amber-50/60 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-100/40 dark:border-amber-900/20">
                                    <i data-lucide="file-signature" class="w-4 h-4"></i>
                                </div>
                                <div class="space-y-0.5 overflow-hidden">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $item->employee_name ?? 'Pegawai' }}</p>
                                    <p class="text-slate-500 dark:text-slate-400 text-[10px]">Menginput {{ $item->type }} ({{ $item->start_date->format('d M Y') }})</p>
                                </div>
                            </a>
                        @endforeach
                    @else
                        <div class="p-6 text-center text-slate-400">
                            <i data-lucide="bell-off" class="w-6 h-6 mx-auto mb-1 text-slate-300 dark:text-slate-750"></i>
                            <p class="text-[10px]">Tidak ada izin baru baru-baru ini.</p>
                        </div>
                    @endif
                </div>
                <div x-show="pendingCount > 0" style="display: none;" class="px-4 py-2 border-t border-slate-100 dark:border-slate-800 text-center bg-slate-50/30 dark:bg-slate-900/10">
                    <button type="button" @click="clearAllNotifications()" class="text-[10px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold hover:underline cursor-pointer bg-transparent border-0">
                        Tandai Semua Sudah Dibaca
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
