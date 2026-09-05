<section class="flex flex-col flex-1 justify-between h-full space-y-6">
    <div class="space-y-6">
        <header class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="p-2 bg-amber-50 dark:bg-amber-950/40 rounded-xl text-amber-600 dark:text-amber-400">
                <i data-lucide="key-round" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Perbarui Kata Sandi</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Gunakan kombinasi kata sandi yang kuat dan unik demi keamanan akses data SDM.</p>
            </div>
        </header>

        <form id="password-update-form" method="post" action="{{ route('password.update') }}" class="space-y-4 max-w-xl text-xs">
            @csrf
            @method('put')

            <!-- Kata Sandi Saat Ini -->
            <div class="space-y-1.5">
                <label for="update_password_current_password" class="block font-bold text-slate-700 dark:text-slate-300">
                    Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" 
                        placeholder="Masukkan kata sandi lama"
                        class="w-full text-xs h-10 px-3.5 pr-10 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono">
                    <button type="button" data-password-toggle data-password-target="update_password_current_password" aria-label="Tampilkan kata sandi" aria-pressed="false" 
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer border-0 bg-transparent">
                        <i data-password-eye data-lucide="eye" class="w-4 h-4" aria-hidden="true"></i>
                        <i data-password-eye-closed data-lucide="eye-off" class="hidden w-4 h-4" aria-hidden="true"></i>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kata Sandi Baru -->
            <div class="space-y-1.5">
                <label for="update_password_password" class="block font-bold text-slate-700 dark:text-slate-300">
                    Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password" 
                        placeholder="Minimal 8 karakter"
                        class="w-full text-xs h-10 px-3.5 pr-10 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono">
                    <button type="button" data-password-toggle data-password-target="update_password_password" aria-label="Tampilkan kata sandi" aria-pressed="false" 
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer border-0 bg-transparent">
                        <i data-password-eye data-lucide="eye" class="w-4 h-4" aria-hidden="true"></i>
                        <i data-password-eye-closed data-lucide="eye-off" class="hidden w-4 h-4" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password', 'updatePassword')
                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Konfirmasi Kata Sandi Baru -->
            <div class="space-y-1.5">
                <label for="update_password_password_confirmation" class="block font-bold text-slate-700 dark:text-slate-300">
                    Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" 
                        placeholder="Ulangi kata sandi baru"
                        class="w-full text-xs h-10 px-3.5 pr-10 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono">
                    <button type="button" data-password-toggle data-password-target="update_password_password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false" 
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors cursor-pointer border-0 bg-transparent">
                        <i data-password-eye data-lucide="eye" class="w-4 h-4" aria-hidden="true"></i>
                        <i data-password-eye-closed data-lucide="eye-off" class="hidden w-4 h-4" aria-hidden="true"></i>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Checklist Tips -->
            <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 rounded-xl space-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                <span class="font-bold text-slate-700 dark:text-slate-300 block text-[10px] uppercase tracking-wider">Tips Keamanan Sandi</span>
                <ul class="space-y-0.5 list-disc list-inside">
                    <li>Gunakan minimal 8 karakter dengan perpaduan huruf, angka, dan simbol.</li>
                    <li>Hindari menggunakan kata sandi yang sama dengan akun pribadi lainnya.</li>
                </ul>
            </div>
        </form>
    </div>

    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
        <button type="submit" form="password-update-form" class="h-10 px-6 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-bold text-xs rounded-xl shadow-sm transition-all cursor-pointer flex items-center gap-2">
            <i data-lucide="key" class="w-4 h-4"></i>
            Simpan Kata Sandi
        </button>
    </div>
</section>

<script>
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-password-toggle]');
        if (!button) return;

        const input = document.getElementById(button.dataset.passwordTarget);
        if (!input) return;

        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        button.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        button.setAttribute('aria-pressed', String(isHidden));
        
        const eyeOpen = button.querySelector('[data-password-eye]');
        const eyeClosed = button.querySelector('[data-password-eye-closed]');
        if (eyeOpen) eyeOpen.classList.toggle('hidden', isHidden);
        if (eyeClosed) eyeClosed.classList.toggle('hidden', !isHidden);
    });
</script>
