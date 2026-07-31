<x-admin-layout>
    <div class="p-6 space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Pengaturan Cut-off</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Konfigurasi tanggal cut-off untuk siklus penggajian dan laporan bulanan.</p>
            </div>
        </section>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 w-full">
                <form action="{{ route('cutoff-settings.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">Tanggal Cut-off Penggajian</h3>
                        <p class="text-xs text-slate-500 mb-4">Tentukan tanggal berapa siklus penggajian ditutup setiap bulannya. Jika Anda mengisi 26, maka siklus gaji dan bonus bulan Juli dihitung dari 27 Juni hingga 26 Juli.</p>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 text-sm">Tanggal Cut-off (1 - 31) <span class="text-red-500">*</span></label>
                        <input type="number" min="1" max="31" name="payroll_cutoff_date" value="{{ old('payroll_cutoff_date', $cutoffDate) }}" required class="text-xs w-full max-w-xs h-10 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        @error('payroll_cutoff_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="submit" class="px-4 py-2 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm cursor-pointer">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
