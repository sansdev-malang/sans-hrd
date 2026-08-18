<x-admin-layout>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showAdjModal: false,
        showEditModal: false,
        isDrawerOpen: false,
        selectedHolidayId: '',
        selectedHolidayName: '',
        selectedHolidayDates: [],
        appliesTo: 'global',
        editHolidayId: '',
        editHolidayName: '',
        editHolidayStartDate: '',
        editHolidayEndDate: '',
        editHolidayAppliesTo: 'global',
        editHolidayUnitIds: [],
        editHolidayOldIds: [],
        drawerHolidayName: '',
        drawerHolidayRange: '',
        drawerAdjustments: []
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

        <!-- PANDUAN MEKANISME & KONSEP HARI LIBUR -->
        <div class="bg-indigo-50/40 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900/40 rounded-xl p-5 text-left space-y-4">
            <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-650 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
                <h4 class="text-xs font-black uppercase tracking-wider font-mono">Panduan & Mekanisme Kalender Hari Libur</h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-slate-600 dark:text-slate-400">
                <div class="space-y-1.5">
                    <h5 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Cakupan Libur (Global vs Lokal)
                    </h5>
                    <p class="leading-relaxed pl-3 text-[11px]">
                        <strong>Libur Nasional:</strong> Berlaku global untuk seluruh unit sekolah (PAUD, SD, SMP).<br>
                        <strong>Libur Lokal:</strong> Hanya berlaku untuk unit sekolah yang Anda pilih saat penambahan libur.
                    </p>
                </div>
                <div class="space-y-1.5">
                    <h5 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Pengalihan Libur (Reschedule)
                    </h5>
                    <p class="leading-relaxed pl-3 text-[11px]">
                        Gunakan fitur <strong>"Alihkan Libur"</strong> untuk memindahkan libur nasional ke tanggal pengganti spesifik per unit sekolah tanpa memengaruhi tanggal libur unit sekolah lainnya.
                    </p>
                </div>
                <div class="space-y-1.5">
                    <h5 class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Pegawai Roster / Shift
                    </h5>
                    <p class="leading-relaxed pl-3 text-[11px]">
                        Semua pegawai dengan jadwal roster / shift <strong>tidak terpengaruh otomatis</strong> oleh hari libur. Mereka tetap wajib masuk, absen, dan berhak atas bonus kehadiran harian sesuai dengan jadwal roster masing-masing.
                    </p>
                </div>
            </div>
        </div>

        <div class="w-full text-left">
            <!-- Holidays List -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Daftar Hari Libur Resmi</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="px-6 py-3 text-left">Nama Libur</th>
                                    <th class="px-6 py-3 text-center">Tanggal Mulai</th>
                                    <th class="px-6 py-3 text-center">Tanggal Selesai</th>
                                    <th class="px-6 py-3 text-center">Cakupan</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                                @forelse($groupedHolidays as $group)
                                    <tr>
                                        <td class="px-6 py-4 text-left">
                                            <div class="font-bold text-slate-900 dark:text-slate-50">{{ $group['name'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono">
                                            {{ $group['start_date']->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono">
                                            {{ $group['end_date']->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($group['is_global'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30 uppercase">Nasional</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30 uppercase">Lokal Unit</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <button 
                                                data-name="{{ $group['name'] }}"
                                                data-range="{{ $group['start_date']->format('d M Y') }} - {{ $group['end_date']->format('d M Y') }}"
                                                data-adjustments="{{ json_encode($group['adjustments_data']) }}"
                                                @click="
                                                    drawerHolidayName = $event.currentTarget.getAttribute('data-name');
                                                    drawerHolidayRange = $event.currentTarget.getAttribute('data-range');
                                                    drawerAdjustments = JSON.parse($event.currentTarget.getAttribute('data-adjustments'));
                                                    isDrawerOpen = true;
                                                " class="h-7 px-2.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-955/30 dark:hover:bg-emerald-955/50 text-emerald-650 dark:text-emerald-400 text-[10px] font-bold rounded-lg border border-emerald-100/30 dark:border-emerald-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span class="pointer-events-none">Detail Pengalihan</span>
                                            </button>
                                            <button 
                                                data-id="{{ $group['ids'][0] }}"
                                                data-name="{{ $group['name'] }}"
                                                data-dates="{{ json_encode($group['items_data']) }}"
                                                @click="
                                                    selectedHolidayId = $event.currentTarget.getAttribute('data-id');
                                                    selectedHolidayName = $event.currentTarget.getAttribute('data-name');
                                                    selectedHolidayDates = JSON.parse($event.currentTarget.getAttribute('data-dates'));
                                                    showAdjModal = true;
                                                " class="h-7 px-2.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50 text-indigo-650 dark:text-indigo-400 text-[10px] font-bold rounded-lg border border-indigo-100/30 dark:border-indigo-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="corner-down-right" class="w-3.5 h-3.5 pointer-events-none"></i>
                                                <span class="pointer-events-none">Alihkan Libur</span>
                                            </button>
                                            <button 
                                                data-id="{{ $group['ids'][0] }}"
                                                data-name="{{ $group['name'] }}"
                                                data-start="{{ $group['start_date']->format('Y-m-d') }}"
                                                data-end="{{ $group['end_date']->format('Y-m-d') }}"
                                                data-applies-to="{{ $group['is_global'] ? 'global' : 'custom' }}"
                                                data-unit-ids="{{ json_encode($group['adjustments']->pluck('school_unit_id')->filter()->unique()->values()->toArray()) }}"
                                                data-old-ids="{{ json_encode($group['ids']) }}"
                                                @click="
                                                    editHolidayId = $event.currentTarget.getAttribute('data-id');
                                                    editHolidayName = $event.currentTarget.getAttribute('data-name');
                                                    editHolidayStartDate = $event.currentTarget.getAttribute('data-start');
                                                    editHolidayEndDate = $event.currentTarget.getAttribute('data-end');
                                                    editHolidayAppliesTo = $event.currentTarget.getAttribute('data-applies-to');
                                                    editHolidayUnitIds = JSON.parse($event.currentTarget.getAttribute('data-unit-ids'));
                                                    editHolidayOldIds = JSON.parse($event.currentTarget.getAttribute('data-old-ids'));
                                                    showEditModal = true;
                                                " class="h-7 px-2.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-955/30 dark:hover:bg-amber-955/50 text-amber-600 dark:text-amber-400 text-[10px] font-bold rounded-lg border border-amber-200/30 dark:border-amber-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="edit" class="w-3.5 h-3.5 pointer-events-none"></i>
                                                <span class="pointer-events-none">Edit</span>
                                            </button>
                                            <form action="{{ route('holidays.destroy', $group['ids'][0]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
                                                @csrf
                                                @method('DELETE')
                                                @foreach($group['ids'] as $id)
                                                    <input type="hidden" name="ids[]" value="{{ $id }}">
                                                @endforeach
                                                <button type="submit" class="h-7 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-955/30 dark:hover:bg-rose-955/50 text-rose-650 dark:text-rose-400 text-[10px] font-bold rounded-lg border border-rose-100/30 dark:border-rose-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1 cursor-pointer">
                                                    <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                            Belum ada hari libur yang ditambahkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ADD HOLIDAY MODAL -->
        <div x-show="showAddModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAddModal" @click.away="showAddModal = false; appliesTo = 'global';" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-md p-6 text-left flex flex-col">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tambah Hari Libur Resmi</h3>
                            <button @click="showAddModal = false; appliesTo = 'global';" class="text-slate-450 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4 text-xs">
                            @csrf
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Hari Libur</label>
                                <input type="text" name="name" required placeholder="Contoh: Tahun Baru Hijriah" class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Mulai</label>
                                    <input type="date" name="start_date" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Selesai</label>
                                    <input type="date" name="end_date" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cakupan Hari Libur</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                        <input type="radio" name="applies_to" value="global" x-model="appliesTo" class="border-slate-300 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                        <span>Semua Unit (Nasional)</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                        <input type="radio" name="applies_to" value="custom" x-model="appliesTo" class="border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4">
                                        <span>Hanya Unit Tertentu</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="appliesTo === 'custom'" x-transition class="space-y-2.5 bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200/50 dark:border-slate-800 mt-2">
                                <label class="block font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Pilih Unit Terkena Dampak</label>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($units as $unit)
                                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                            <input type="checkbox" name="school_unit_ids[]" value="{{ $unit->id }}" class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                            <span class="font-medium text-xs">{{ $unit->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showAddModal = false; appliesTo = 'global';" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
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

        <!-- EDIT HOLIDAY MODAL -->
        <div x-show="showEditModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showEditModal" @click.away="showEditModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-md p-6 text-left flex flex-col">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Edit Hari Libur</h3>
                            <button @click="showEditModal = false" class="text-slate-450 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
 
                        <form method="POST" :action="`{{ url('holidays') }}/${editHolidayId}`" class="space-y-4 text-xs">
                            @csrf
                            @method('PUT')
                            <template x-for="id in editHolidayOldIds">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Hari Libur</label>
                                <input type="text" name="name" x-model="editHolidayName" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-sans text-xs">
                            </div>
 
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Mulai</label>
                                    <input type="date" name="start_date" x-model="editHolidayStartDate" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Selesai</label>
                                    <input type="date" name="end_date" x-model="editHolidayEndDate" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                                </div>
                            </div>
 
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cakupan Hari Libur</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                        <input type="radio" name="applies_to" value="global" x-model="editHolidayAppliesTo" class="border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4">
                                        <span>Semua Unit (Nasional)</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                        <input type="radio" name="applies_to" value="custom" x-model="editHolidayAppliesTo" class="border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4">
                                        <span>Hanya Unit Tertentu</span>
                                    </label>
                                </div>
                            </div>
 
                            <div x-show="editHolidayAppliesTo === 'custom'" x-transition class="space-y-2.5 bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200/50 dark:border-slate-800 mt-2">
                                <label class="block font-bold text-slate-400 uppercase tracking-wider text-[9px] mb-1">Pilih Unit Terkena Dampak</label>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($units as $unit)
                                        <label class="flex items-center gap-2 cursor-pointer text-slate-700 dark:text-slate-300 select-none">
                                            <input type="checkbox" name="school_unit_ids[]" value="{{ $unit->id }}" :checked="editHolidayUnitIds.includes({{ $unit->id }})" class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                            <span class="font-medium text-xs">{{ $unit->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
 
                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Perubahan
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
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Tanggal Libur yang Dialihkan</label>
                                <select name="holiday_id" x-model="selectedHolidayId" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <template x-for="item in selectedHolidayDates" :key="item.id">
                                        <option :value="item.id" x-text="item.date_formatted"></option>
                                    </template>
                                </select>
                            </div>
 
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

        <!-- SLIDE-OVER DRAWER (LACI DETAIL PENGALIHAN) -->
        <div x-cloak x-show="isDrawerOpen" class="fixed inset-0 z-[9999] overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Backdrop overlay -->
                <div x-show="isDrawerOpen" 
                     x-transition:enter="ease-in-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in-out duration-300" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     @click="isDrawerOpen = false"
                     class="fixed inset-0 transition-opacity z-[9999]" 
                     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);" 
                     aria-hidden="true"></div>
 
                <!-- Content Panel -->
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex z-[9999]">
                    <div x-show="isDrawerOpen" 
                         x-transition:enter="transform transition ease-in-out duration-300 sm:duration-300" 
                         x-transition:enter-start="translate-x-full" 
                         x-transition:enter-end="translate-x-0" 
                         x-transition:leave="transform transition ease-in-out duration-300 sm:duration-300" 
                         x-transition:leave-start="translate-x-0" 
                         x-transition:leave-end="translate-x-full" 
                         class="w-screen max-w-md bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800 flex flex-col justify-between text-left font-sans">
                        
                        <!-- Header -->
                        <div class="px-6 py-5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex justify-between items-start">
                            <div class="space-y-1">
                                <h3 class="text-sm font-black uppercase tracking-wider font-mono text-slate-900 dark:text-slate-50 leading-tight">Pengalihan Hari Libur</h3>
                                <p class="text-xs text-slate-650 dark:text-slate-350 font-bold" x-text="drawerHolidayName"></p>
                                <span class="inline-flex items-center text-[10px] font-mono text-indigo-650 dark:text-indigo-400 font-semibold" x-text="drawerHolidayRange"></span>
                            </div>
                            <button @click="isDrawerOpen = false" class="p-1 rounded-lg text-slate-450 hover:bg-slate-100 dark:hover:bg-slate-850 hover:text-slate-650 cursor-pointer border-0 bg-transparent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
 
                        <!-- Body (Adjustments List) -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Daftar Pengalihan Unit</h4>
                            
                            <template x-if="drawerAdjustments.length === 0">
                                <div class="py-12 text-center text-slate-450 dark:text-slate-500 space-y-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mx-auto text-slate-350 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z"/></svg>
                                    <p class="text-xs font-semibold">Belum ada pengalihan libur.</p>
                                    <p class="text-[10px] leading-relaxed">Gunakan fitur "Alihkan Libur" pada baris tabel untuk memindahkan libur unit.</p>
                                </div>
                            </template>
 
                            <div class="space-y-3">
                                <template x-for="adj in drawerAdjustments" :key="adj.id">
                                    <div class="p-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-xl border border-slate-200/50 dark:border-slate-800/40 text-xs flex flex-col justify-between space-y-2.5 hover:shadow-2xs transition-all duration-150">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/20 dark:border-indigo-900/30 uppercase" x-text="adj.school_unit_name"></span>
                                            </div>
                                            <button @click="
                                                if(confirm('Apakah Anda yakin ingin menghapus pengalihan libur ini?')) {
                                                    let form = document.getElementById('delete-adj-form');
                                                    form.action = adj.destroy_url;
                                                    form.submit();
                                                }
                                            " class="text-slate-400 hover:text-rose-600 transition-colors border-0 bg-transparent cursor-pointer">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
 
                                        <div class="grid grid-cols-2 gap-2 text-[10px] font-mono border-t border-slate-200/50 dark:border-slate-800 pt-2 text-slate-500 dark:text-slate-400">
                                            <div>
                                                <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Asli</span>
                                                <span x-text="adj.original_date_formatted"></span>
                                            </div>
                                            <div class="text-right">
                                                <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider">Dialihkan Ke</span>
                                                <span class="text-amber-600 dark:text-amber-400 font-bold" x-text="adj.adjusted_date_formatted"></span>
                                            </div>
                                        </div>
 
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 bg-slate-100/50 dark:bg-slate-950 p-2 rounded-lg border border-slate-200/20 dark:border-slate-900/50">
                                            <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Alasan</span>
                                            <span class="font-sans font-semibold text-slate-650 dark:text-slate-350" x-text="adj.reason"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
 
                        <!-- Footer -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                            <button @click="isDrawerOpen = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- HIDDEN DELETE FORM FOR DRAWER -->
        <form id="delete-adj-form" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-admin-layout>
