<x-admin-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showAdjModal: false,
        selectedHolidayId: '',
        selectedHolidayName: ''
    }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2.5">
                    <span>Hari Libur & Pengalihan Libur</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider shrink-0">Kalender</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur hari libur nasional serta kebijakan pengalihan libur kerja operasional antar unit sekolah.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('holidays.sync') }}" data-no-loader="true" onclick="this.style.pointerEvents = 'none'; let icon = this.querySelector('svg'); if(icon) icon.classList.add('animate-spin');" class="h-9 px-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-350 text-xs font-bold rounded-xl shadow-3xs border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>Sync Ulang ke Unit</span>
                </a>
                <button @click="showAddModal = true" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-105 duration-150 flex items-center gap-1.5 border-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah Libur Baru</span>
                </button>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 text-left">
            <!-- Holidays List -->
            <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Daftar Hari Libur Resmi</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-6 py-3 text-left">Nama Libur</th>
                                    <th class="px-6 py-3 text-center">Tanggal Asli</th>
                                    <th class="px-6 py-3 text-center">Cakupan</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                                @forelse($holidays as $holiday)
                                    <tr>
                                        <td class="px-6 py-4 text-left">
                                            <div class="font-bold text-slate-900 dark:text-slate-50">{{ $holiday->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono">
                                            {{ $holiday->original_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($holiday->is_global)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30 uppercase">Nasional</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30 uppercase">Lokal Unit</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <button @click="selectedHolidayId = '{{ $holiday->id }}'; selectedHolidayName = '{{ $holiday->name }}'; showAdjModal = true" class="h-7 px-2.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50 text-indigo-650 dark:text-indigo-400 text-[10px] font-bold rounded-lg border border-indigo-100/30 dark:border-indigo-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="corner-down-right" class="w-3.5 h-3.5"></i>
                                                Alihkan Libur
                                            </button>
                                            <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="h-7 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 text-rose-650 dark:text-rose-400 text-[10px] font-bold rounded-lg border border-rose-100/30 dark:border-rose-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                                    <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                            Belum ada hari libur yang ditambahkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Adjustments / Reschedules -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                    <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Daftar Pengalihan Tanggal Libur</h4>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($holidays->flatMap->adjustments as $adj)
                        <div class="p-3.5 bg-slate-50/50 dark:bg-slate-900/30 rounded-xl border border-slate-200/50 dark:border-slate-800/40 text-xs flex flex-col justify-between space-y-2.5 hover:shadow-2xs transition-all duration-150">
                            <div>
                                <div class="flex justify-between items-start">
                                    <h5 class="font-bold text-slate-800 dark:text-slate-200">{{ $adj->holiday ? $adj->holiday->name : 'Hari Libur' }}</h5>
                                    <form action="{{ route('holidays.destroy-adjustment', $adj->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengalihan libur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors border-0 bg-transparent cursor-pointer">
                                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[8px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/20 dark:border-indigo-900/30 uppercase mt-1.5">
                                    {{ $adj->schoolUnit ? $adj->schoolUnit->name : 'Semua Unit' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-[10px] font-mono border-t border-slate-200/50 dark:border-slate-800 pt-2 text-slate-500 dark:text-slate-400">
                                <div>
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Asli</span>
                                    {{ $adj->original_date->format('d M Y') }}
                                </div>
                                <div class="text-right">
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Dialihkan Ke</span>
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $adj->adjusted_date->format('d M Y') }}</span>
                                </div>
                            </div>

                            @if($adj->reason)
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 leading-normal italic">Ket: {{ $adj->reason }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-6">Belum ada pengalihan hari libur yang aktif.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ADD HOLIDAY MODAL -->
        <div x-show="showAddModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAddModal" @click.away="showAddModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-sm p-6 text-left flex flex-col">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tambah Hari Libur Resmi</h3>
                            <button @click="showAddModal = false" class="text-slate-450 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4 text-xs">
                            @csrf
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Hari Libur</label>
                                <input type="text" name="name" required placeholder="Contoh: Tahun Baru Hijriah" class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Asli Libur</label>
                                <input type="date" name="original_date" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_global" name="is_global" value="1" checked class="rounded border-slate-305 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                <label for="is_global" class="ml-2 text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono">Libur Nasional (Berlaku Semua Unit)</label>
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Hari Libur
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESCHEDULE/ADJUST HOLIDAY MODAL -->
        <div x-show="showAdjModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showAdjModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAdjModal" @click.away="showAdjModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-sm p-6 text-left flex flex-col">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Pengalihan Hari Libur</h3>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5" x-text="selectedHolidayName"></p>
                            </div>
                            <button @click="showAdjModal = false" class="text-slate-450 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('holidays.store-adjustment') }}" class="space-y-4 text-xs">
                            @csrf
                            <input type="hidden" name="holiday_id" x-model="selectedHolidayId">

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alihkan Libur ke Tanggal</label>
                                <input type="date" name="adjusted_date" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-2">Unit Terkena Dampak (Kosongkan jika Semua Unit)</label>
                                <div class="space-y-2.5 bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200/50 dark:border-slate-800">
                                    @foreach($units as $unit)
                                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                            <input type="checkbox" name="school_unit_ids[]" value="{{ $unit->id }}" class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                            <span class="font-medium text-xs">{{ $unit->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alasan Pengalihan</label>
                                <input type="text" name="reason" placeholder="Contoh: Digeser bertepatan libur sabtu..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showAdjModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Pengalihan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
