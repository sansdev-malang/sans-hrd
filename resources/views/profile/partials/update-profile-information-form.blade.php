<section class="flex flex-col flex-1 justify-between h-full space-y-6">
    <div class="space-y-6">
        <header class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl text-indigo-600 dark:text-indigo-400">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Informasi Kredensial Akun</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Perbarui nama pengguna dan alamat email yang digunakan untuk masuk ke sistem.</p>
            </div>
        </header>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form id="profile-update-form" method="post" action="{{ route('profile.update') }}" class="space-y-4 max-w-xl text-xs">
            @csrf
            @method('patch')

            <!-- Nama Lengkap -->
            <div class="space-y-1.5">
                <label for="name" class="block font-bold text-slate-700 dark:text-slate-300">
                    Nama Lengkap Administrator <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" 
                        class="w-full text-xs h-10 px-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-medium">
                </div>
                @error('name')
                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Alamat Email -->
            <div class="space-y-1.5">
                <label for="email" class="block font-bold text-slate-700 dark:text-slate-300">
                    Alamat Email Login <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" 
                        class="w-full text-xs h-10 px-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono font-medium">
                </div>
                @error('email')
                    <p class="text-[11px] text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 rounded-xl">
                        <p class="text-[11px] text-amber-800 dark:text-amber-300 font-medium">
                            Alamat email Anda belum diverifikasi.
                            <button form="send-verification" class="underline text-xs text-amber-700 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 font-bold ml-1 cursor-pointer">
                                Klik di sini untuk mengirim ulang email verifikasi.
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Hak Akses / Role (Read-only badge info) -->
            <div class="space-y-1.5 pt-1">
                <label class="block font-bold text-slate-700 dark:text-slate-300">
                    Hak Akses &amp; Otoritas
                </label>
                <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 rounded-xl flex items-center justify-between">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono">{{ strtoupper($user->role ?? 'SUPER_ADMIN') }}</span>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Diberikan oleh sistem pengelola SANS</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-extrabold uppercase rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/40">
                        <i data-lucide="shield" class="w-3 h-3"></i>
                        Tingkat Penuh
                    </span>
                </div>
            </div>
        </form>
    </div>

    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
        <button type="submit" form="profile-update-form" class="h-10 px-6 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-bold text-xs rounded-xl shadow-sm transition-all cursor-pointer flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            Simpan Perubahan Profil
        </button>
    </div>
</section>
