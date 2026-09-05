<x-admin-layout>
    <div class="p-6 space-y-6 w-full text-left">
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">Profil &amp; Keamanan Akun</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Kelola informasi kredensial administrator, kata sandi, dan parameter keamanan sistem Anda.</p>
            </div>
        </header>

        <!-- HERO PROFILE CARD WITH INTEGRATED AUTO-SAVE PHOTO CONTROLS -->
        @php
            $nameParts = explode(' ', $user->name);
            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super_admin');
            $hasPhoto = !empty($user->photo);
        @endphp
        <section class="bg-gradient-to-br from-white via-slate-50/50 to-slate-100/60 dark:from-slate-900 dark:via-slate-900/90 dark:to-slate-950 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 -mt-8 -mr-8 w-48 h-48 bg-indigo-500/5 dark:bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-start sm:items-center gap-4">
                    <!-- Big Avatar with Instant Update -->
                    <div class="relative shrink-0">
                        <img id="hero-avatar-img" 
                            src="{{ $hasPhoto ? asset('storage/' . $user->photo) : '' }}" 
                            alt="Avatar {{ $user->name }}" 
                            class="{{ $hasPhoto ? '' : 'hidden' }} w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shadow-md ring-4 ring-indigo-500/20 dark:ring-indigo-500/30 border border-slate-200 dark:border-slate-800 transition-all duration-300">
                        
                        <div id="hero-avatar-placeholder" 
                            class="{{ $hasPhoto ? 'hidden' : 'flex' }} w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-slate-900 text-white items-center justify-center font-bold font-mono text-xl sm:text-2xl shadow-md ring-4 ring-indigo-500/10 dark:ring-indigo-500/20 transition-all duration-300">
                            {{ $initials }}
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-black text-slate-900 dark:text-slate-50 tracking-tight">{{ $user->name }}</h3>
                            @if($isSuperAdmin)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-350 border border-indigo-200/60 dark:border-indigo-800/60">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                    Super Administrator
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-350 border border-emerald-200/60 dark:border-emerald-800/60">
                                    <i data-lucide="user-check" class="w-3 h-3"></i>
                                    {{ strtoupper($user->role ?? 'Admin') }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ $user->email }}
                        </p>

                        <!-- Integrated Instant Auto-Save Photo Controls -->
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <label for="photo_input" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs cursor-pointer transition-all">
                                <i data-lucide="camera" class="w-3.5 h-3.5 text-indigo-500"></i>
                                <span id="hero_photo_label_text">{{ $hasPhoto ? 'Ganti Foto' : 'Unggah Foto' }}</span>
                            </label>

                            <input type="file" id="photo_input" name="photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">

                            <button type="button" id="hero_btn_remove_photo" class="{{ $hasPhoto ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-xl border border-rose-200/60 dark:border-rose-900/40 transition-all cursor-pointer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Hapus Foto</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Meta Badges Right Side -->
                <div class="flex flex-wrap md:flex-col items-start md:items-end gap-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                    <div class="flex items-center gap-1.5 px-3 py-1 bg-white/80 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800 rounded-lg shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Status: Akun Aktif</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1 bg-white/80 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800 rounded-lg shadow-2xs font-mono text-[10.5px]">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Bergabung: {{ $user->created_at ? $user->created_at->translatedFormat('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- MAIN 2-COLUMN SECTION (EQUAL HEIGHT) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full items-stretch">
            <!-- COLUMN 1: INFORMASI PROFIL & AKUN -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 w-full flex flex-col h-full transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-700">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- COLUMN 2: PERBARUI KATA SANDI -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 w-full flex flex-col h-full transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-700">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- INFO SESI & KEAMANAN LOGIN CARD -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm w-full space-y-4">
            <header class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-300">
                    <i data-lucide="laptop" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Sesi &amp; Perangkat Login Saat Ini</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Informasi lingkungan perangkat dan koneksi yang sedang aktif untuk akun ini.</p>
                </div>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1 text-xs font-mono">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800/80 rounded-xl space-y-1">
                    <span class="text-[10px] font-sans font-bold text-slate-400 uppercase tracking-wider block">Alamat IP Klien</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ request()->ip() }}</span>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800/80 rounded-xl space-y-1">
                    <span class="text-[10px] font-sans font-bold text-slate-400 uppercase tracking-wider block">Waktu Akses</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ now()->translatedFormat('d M Y - H:i') }} WIB</span>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800/80 rounded-xl space-y-1">
                    <span class="text-[10px] font-sans font-bold text-slate-400 uppercase tracking-wider block">Protokol Keamanan</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        CSRF &amp; Session Guard Aktif
                    </span>
                </div>
            </div>
        </section>
    </div>
</x-admin-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const photoInput = document.getElementById('photo_input');
        const heroAvatarImg = document.getElementById('hero-avatar-img');
        const heroAvatarPlaceholder = document.getElementById('hero-avatar-placeholder');
        const btnRemovePhoto = document.getElementById('hero_btn_remove_photo');
        const photoLabelText = document.getElementById('hero_photo_label_text');
        const photoLabel = document.querySelector('label[for="photo_input"]');

        function updateSidebarAvatar(photoUrl, initials) {
            const sidebarAvatarContainer = document.querySelector('.user-selector .flex.items-center.gap-2\\.5');
            if (!sidebarAvatarContainer) return;

            let img = sidebarAvatarContainer.querySelector('img');
            let initialDiv = sidebarAvatarContainer.querySelector('div.bg-indigo-900\\/30, div.bg-indigo-900');

            if (photoUrl) {
                if (!img) {
                    img = document.createElement('img');
                    img.className = 'w-7 h-7 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-800 shrink-0';
                    img.alt = 'Avatar';
                    sidebarAvatarContainer.prepend(img);
                }
                img.src = photoUrl;
                img.classList.remove('hidden');
                if (initialDiv) initialDiv.classList.add('hidden');
            } else {
                if (img) img.classList.add('hidden');
                if (!initialDiv) {
                    initialDiv = document.createElement('div');
                    initialDiv.className = 'w-7 h-7 rounded-lg bg-indigo-900/30 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0';
                    sidebarAvatarContainer.prepend(initialDiv);
                }
                initialDiv.textContent = initials;
                initialDiv.classList.remove('hidden');
            }
        }

        if (photoInput) {
            photoInput.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                // Client-side file size check (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    if (typeof showSessionToast === 'function') {
                        showSessionToast('Perhatian!', 'Ukuran file foto maksimal adalah 2MB.', 'error');
                    } else {
                        alert('Ukuran file foto maksimal adalah 2MB.');
                    }
                    photoInput.value = '';
                    return;
                }

                // Temporary loading feedback
                const originalLabel = photoLabelText.textContent;
                photoLabelText.textContent = 'Menyimpan...';
                if (photoLabel) photoLabel.classList.add('opacity-75', 'pointer-events-none');

                const formData = new FormData();
                formData.append('photo', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const response = await fetch('{{ route('profile.photo.update') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        heroAvatarImg.src = data.photo_url;
                        heroAvatarImg.classList.remove('hidden');
                        heroAvatarPlaceholder.classList.add('hidden');
                        heroAvatarPlaceholder.classList.remove('flex');

                        btnRemovePhoto.classList.remove('hidden');
                        btnRemovePhoto.classList.add('inline-flex');
                        photoLabelText.textContent = 'Ganti Foto';

                        updateSidebarAvatar(data.photo_url);

                        if (typeof showSessionToast === 'function') {
                            showSessionToast('Sukses!', data.message, 'success');
                        }
                    } else {
                        const errMsg = data.message || (data.errors && data.errors.photo ? data.errors.photo[0] : 'Gagal mengunggah foto.');
                        if (typeof showSessionToast === 'function') {
                            showSessionToast('Perhatian!', errMsg, 'error');
                        }
                        photoLabelText.textContent = originalLabel;
                    }
                } catch (err) {
                    console.error(err);
                    if (typeof showSessionToast === 'function') {
                        showSessionToast('Perhatian!', 'Terjadi kesalahan jaringan saat mengunggah foto.', 'error');
                    }
                    photoLabelText.textContent = originalLabel;
                } finally {
                    if (photoLabel) photoLabel.classList.remove('opacity-75', 'pointer-events-none');
                    photoInput.value = '';
                }
            });
        }

        if (btnRemovePhoto) {
            btnRemovePhoto.addEventListener('click', async () => {
                if (!confirm('Apakah Anda yakin ingin menghapus foto profil ini?')) {
                    return;
                }

                btnRemovePhoto.classList.add('opacity-75', 'pointer-events-none');

                try {
                    const response = await fetch('{{ route('profile.photo.destroy') }}', {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ _token: '{{ csrf_token() }}' })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        heroAvatarImg.src = '';
                        heroAvatarImg.classList.add('hidden');
                        heroAvatarPlaceholder.textContent = data.initials;
                        heroAvatarPlaceholder.classList.remove('hidden');
                        heroAvatarPlaceholder.classList.add('flex');

                        btnRemovePhoto.classList.add('hidden');
                        btnRemovePhoto.classList.remove('inline-flex');
                        photoLabelText.textContent = 'Unggah Foto';

                        updateSidebarAvatar(null, data.initials);

                        if (typeof showSessionToast === 'function') {
                            showSessionToast('Sukses!', data.message, 'success');
                        }
                    } else {
                        const errMsg = data.message || 'Gagal menghapus foto profil.';
                        if (typeof showSessionToast === 'function') {
                            showSessionToast('Perhatian!', errMsg, 'error');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    if (typeof showSessionToast === 'function') {
                        showSessionToast('Perhatian!', 'Terjadi kesalahan jaringan saat menghapus foto.', 'error');
                    }
                } finally {
                    btnRemovePhoto.classList.remove('opacity-75', 'pointer-events-none');
                }
            });
        }
    });
</script>
