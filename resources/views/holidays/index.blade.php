<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showAdjModal: false,
        selectedHolidayId: '',
        selectedHolidayName: ''
    }">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Hari Libur & Pengalihan Libur</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur hari libur nasional serta kebijakan pengalihan libur kerja operasional antar unit sekolah.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('holidays.sync') }}" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Sync Ulang ke Unit
                </a>
                <button @click="showAddModal = true" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Libur Baru
                </button>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 text-left">
            <!-- Holidays List -->
            <div class="xl:col-span-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Hari Libur Resmi</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-150 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
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
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30 uppercase">Nasional</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-250/30 dark:border-amber-900/30 uppercase">Lokal Unit</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <button @click="selectedHolidayId = '{{ $holiday->id }}'; selectedHolidayName = '{{ $holiday->name }}'; showAdjModal = true" class="h-7 px-2 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/20 dark:hover:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 text-[10px] font-semibold rounded border border-indigo-250/30 dark:border-indigo-900/30 transition-all cursor-pointer flex items-center gap-1">
                                                <i data-lucide="corner-down-right" class="w-3.5 h-3.5"></i>
                                                Alihkan Libur
                                            </button>
                                            <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus hari libur ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="h-7 px-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-[10px] font-semibold rounded border border-rose-250/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
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
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Pengalihan Tanggal Libur</h4>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($holidays->flatMap->adjustments as $adj)
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 text-xs flex flex-col justify-between space-y-2.5">
                            <div>
                                <div class="flex justify-between items-start">
                                    <h5 class="font-bold text-slate-800 dark:text-slate-250">{{ $adj->holiday ? $adj->holiday->name : 'Hari Libur' }}</h5>
                                    <form action="{{ route('holidays.destroy-adjustment', $adj->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengalihan libur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 cursor-pointer">
                                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-750 dark:text-indigo-400 border border-indigo-200/20 dark:border-indigo-900/30 uppercase mt-1">
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
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showAddModal = false" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-950 dark:text-slate-50">Tambah Hari Libur Resmi</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Nama Hari Libur</label>
                        <input type="text" name="name" required placeholder="Contoh: Tahun Baru Hijriah" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Tanggal Asli Libur</label>
                        <input type="date" name="original_date" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_global" name="is_global" value="1" checked class="rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 w-4 h-4">
                        <label for="is_global" class="ml-2 font-semibold text-slate-750 dark:text-slate-300 font-mono">Libur Nasional (Berlaku Semua Unit)</label>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Hari Libur
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RESCHEDULE/ADJUST HOLIDAY MODAL -->
        <div x-show="showAdjModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showAdjModal = false" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-950 dark:text-slate-50">Pengalihan Hari Libur</h3>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5" x-text="selectedHolidayName"></p>
                    </div>
                    <button @click="showAdjModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('holidays.store-adjustment') }}" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="holiday_id" x-model="selectedHolidayId">

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Alihkan Libur ke Tanggal</label>
                        <input type="date" name="adjusted_date" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Unit Terkena Dampak (Opsional)</label>
                        <select name="school_unit_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Alasan Pengalihan</label>
                        <input type="text" name="reason" placeholder="Contoh: Digeser bertepatan libur sabtu..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showAdjModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Pengalihan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
