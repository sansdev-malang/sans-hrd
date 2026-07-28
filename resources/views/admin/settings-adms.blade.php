<x-admin-layout>
    <div class="p-6 space-y-6">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Sinkronisasi ZKTeco ADMS</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pengaturan untuk menerima data absen langsung dari mesin fisik ZKTeco via protokol ADMS secara real-time.</p>
            </div>
        </header>

        <!-- ADMS SETTINGS FORM -->
        <form action="{{ route('settings.update-adms') }}" method="POST" class="w-full">
            @csrf
            @method('PUT')
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-900 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <i data-lucide="radio" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Konfigurasi ADMS Server</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Aktifkan atau matikan penerimaan data, dan atur kunci keamanan.</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- ADMS Enabled Toggle -->
                        <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                            <div class="flex items-center h-5 mt-1">
                                <input id="adms_enabled" name="adms_enabled" type="checkbox" value="1" {{ setting('adms_enabled', '0') == '1' ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-slate-900 focus:ring-2 dark:bg-slate-800 dark:border-slate-600 cursor-pointer transition-all">
                            </div>
                            <div class="text-sm">
                                <label for="adms_enabled" class="font-bold text-slate-800 dark:text-slate-200 cursor-pointer text-base">Aktifkan Penerima ADMS</label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jika diaktifkan, aplikasi akan mengizinkan mesin ZKTeco untuk melakukan "push" data absensi secara real-time ke sistem HRD ini.</p>
                            </div>
                        </div>

                        <!-- ADMS Auth Token -->
                        <div class="space-y-2 p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200">Token Otorisasi ADMS <span class="text-slate-400 font-normal text-xs">(Opsional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="key" class="w-4 h-4 text-slate-400"></i>
                                </div>
                                <input type="text" name="adms_auth_token" value="{{ old('adms_auth_token', setting('adms_auth_token')) }}" class="w-full h-11 pl-10 pr-4 text-sm bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all shadow-sm" placeholder="Masukkan token rahasia...">
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">Gunakan token untuk mencegah mesin asing mengirim data. Pastikan token yang dimasukkan di sini <b>sama persis</b> dengan yang dimasukkan di menu Cloud Server mesin ZKTeco.</p>
                        </div>

                    </div>
                </div>
                
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-900 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-50 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-sm font-bold rounded-lg cursor-pointer transition-colors flex items-center gap-2 shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </form>

    </div>
</x-admin-layout>
