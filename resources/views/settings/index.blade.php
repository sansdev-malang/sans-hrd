<x-admin-layout>
    <div class="p-6 space-y-6 text-left">
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2.5">
                    <span>Pengaturan Cut-off</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider shrink-0 font-sans">Settings</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasi tanggal cut-off untuk siklus penggajian dan laporan bulanan.</p>
            </div>
        </header>


        <form action="{{ route('cutoff-settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Card 1: Cut-off Global -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 w-full hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                                <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                            </span>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tanggal Cut-off Penggajian</h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                            Tentukan tanggal berapa siklus penggajian ditutup setiap bulannya. Jika Anda mengisi <strong>25</strong>, maka siklus gaji dan bonus bulan Oktober dihitung dari <strong>26 September</strong> hingga <strong>25 Oktober</strong>.
                        </p>
                        
                        <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 text-xs">Tanggal Cut-off <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="number" min="1" max="31" name="payroll_cutoff_date" value="{{ old('payroll_cutoff_date', $cutoffDate) }}" required class="text-xs w-32 h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 font-mono font-bold">
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Setiap bulannya</span>
                            </div>
                            @error('payroll_cutoff_date')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-lg border border-indigo-100 dark:border-indigo-900/30 text-[11px] text-indigo-800 dark:text-indigo-300 leading-relaxed">
                        <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1 text-indigo-500"></i>
                        Default periode berjalan siklus Oktober: <strong>{{ \Carbon\Carbon::parse($defaultPicketEffectiveDate)->format('d M Y') }}</strong> s/d <strong>25 Okt 2026</strong>.
                    </div>
                </div>

                <!-- Card 2: Tanggal Mulai Berlaku Piket Per Unit -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 w-full hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tanggal Mulai Berlaku Piket per Unit</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                        Jadwal piket & batas hadir 06:30 hanya akan memengaruhi perhitungan mulai dari tanggal yang ditentukan untuk masing-masing unit. Tanggal sebelum ini akan tetap dihitung menggunakan jam shift normal sehingga <strong>history periode-periode sebelumnya tidak berubah</strong>.
                    </p>

                    <div class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        @forelse($schoolUnits as $unit)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-950/60 border border-slate-200/70 dark:border-slate-800/80">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        {{ $unit->name }}
                                    </span>
                                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">Mulai Berlaku:</span>
                                </div>
                                <div class="w-full sm:w-auto">
                                    <input type="date" name="unit_picket_effective_dates[{{ $unit->id }}]" value="{{ old('unit_picket_effective_dates.' . $unit->id, $unit->picket_effective_date) }}" class="text-xs w-full sm:w-44 h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada data unit sekolah aktif.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-2">
                <button type="submit" class="h-10 px-5 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
