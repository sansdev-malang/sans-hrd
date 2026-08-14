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


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 w-full hover:shadow-md transition-all duration-200">
                <form action="{{ route('cutoff-settings.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <h3 class="text-sm font-bold text-indigo-650 dark:text-indigo-400 mb-1">Tanggal Cut-off Penggajian</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">Tentukan tanggal berapa siklus penggajian ditutup setiap bulannya. Jika Anda mengisi 26, maka siklus gaji dan bonus bulan Juli dihitung dari 27 Juni hingga 26 Juli.</p>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 text-xs">Tanggal Cut-off (1 - 31) <span class="text-red-500">*</span></label>
                        <input type="number" min="1" max="31" name="payroll_cutoff_date" value="{{ old('payroll_cutoff_date', $cutoffDate) }}" required class="text-xs w-full max-w-xs h-10 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 font-mono font-bold">
                        @error('payroll_cutoff_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                        <button type="submit" class="h-9 px-4 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
