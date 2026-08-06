<x-admin-layout>
    <div class="p-6 space-y-6 w-full">
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <a href="{{ route('announcements.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 mb-2">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Kembali ke Daftar
                </a>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-500">Tulis Pengumuman Baru</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Terbitkan pengumuman baru untuk internal HRD atau distribusikan ke unit-unit sekolah.</p>
            </div>
        </header>

        <!-- FORM CARD -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 md:p-8">
            <form action="{{ route('announcements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 text-left">
                    <!-- Left Columns (Title, Content, School Units Target) -->
                    <div class="lg:col-span-3 space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Judul Pengumuman <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="Contoh: Pembaruan Sistem Kepegawaian Akademik" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Isi Pengumuman <span class="text-red-500">*</span></label>
                            <textarea name="content" id="content" rows="10" required class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('content') }}</textarea>
                            @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Dynamic Target Grid Selection -->
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Distribusi Target Unit Sekolah</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @forelse($units as $unit)
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-900/30 text-left space-y-4" 
                                         x-data="{ 
                                             enabled: false, 
                                             audiences: [], 
                                             toggle() { 
                                                 this.enabled = !this.enabled; 
                                                 if (this.enabled) { 
                                                     this.audiences = ['management', 'teacher', 'employee', 'student', 'parent']; 
                                                 } else { 
                                                     this.audiences = []; 
                                                 } 
                                             } 
                                         }">
                                        <!-- Unit Header with Toggle Switch -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                                <i data-lucide="school" class="w-4 h-4 text-indigo-500 dark:text-indigo-400"></i>
                                                {{ $unit->name }}
                                            </span>
                                             <div class="relative inline-flex items-center cursor-pointer select-none" @click="toggle()">
                                                 <input type="checkbox" name="units[{{ $unit->id }}][enabled]" value="1" :checked="enabled" class="hidden">
                                                 <!-- Switch Body -->
                                                 <div class="relative w-8 h-5 rounded-full transition-colors duration-200 flex items-center p-0.5"
                                                      :style="enabled ? 'background-color: #4f46e5;' : 'background-color: #475569;'">
                                                     <!-- Switch Dot -->
                                                     <div class="bg-white w-4 h-4 rounded-full transition-transform duration-200 shadow-xs"
                                                          :style="enabled ? 'transform: translateX(12px);' : 'transform: translateX(0);'"></div>
                                                 </div>
                                             </div>
                                         </div>
                                         <!-- Target Audience Checkboxes, disabled and muted if switch is off -->
                                         <div class="space-y-2 pt-3 border-t border-slate-200/50 dark:border-slate-800">
                                             <span class="block text-[9px] font-bold uppercase tracking-wider" :class="enabled ? 'text-slate-400 dark:text-slate-500' : 'text-slate-300 dark:text-slate-600'">Target Penerima</span>
                                             <div class="space-y-2">
                                                 <label class="flex items-center gap-2 cursor-pointer text-xs select-none" :class="enabled ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600'">
                                                     <input type="checkbox" name="units[{{ $unit->id }}][audiences][]" value="management" :disabled="!enabled" x-model="audiences"
                                                            class="rounded border-slate-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-indigo-500"
                                                            :class="enabled ? 'text-indigo-600' : 'text-slate-300 bg-slate-100 dark:bg-slate-800'">
                                                     <span>Manajemen Saja</span>
                                                 </label>
                                                 <label class="flex items-center gap-2 cursor-pointer text-xs select-none" :class="enabled ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600'">
                                                     <input type="checkbox" name="units[{{ $unit->id }}][audiences][]" value="teacher" :disabled="!enabled" x-model="audiences"
                                                            class="rounded border-slate-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-indigo-500"
                                                            :class="enabled ? 'text-indigo-600' : 'text-slate-300 bg-slate-100 dark:bg-slate-800'">
                                                     <span>Guru Saja</span>
                                                 </label>
                                                 <label class="flex items-center gap-2 cursor-pointer text-xs select-none" :class="enabled ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600'">
                                                     <input type="checkbox" name="units[{{ $unit->id }}][audiences][]" value="employee" :disabled="!enabled" x-model="audiences"
                                                            class="rounded border-slate-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-indigo-500"
                                                            :class="enabled ? 'text-indigo-600' : 'text-slate-300 bg-slate-100 dark:bg-slate-800'">
                                                     <span>Pegawai / Staf Saja (Non-Guru)</span>
                                                 </label>
                                                 <label class="flex items-center gap-2 cursor-pointer text-xs select-none" :class="enabled ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600'">
                                                     <input type="checkbox" name="units[{{ $unit->id }}][audiences][]" value="student" :disabled="!enabled" x-model="audiences"
                                                            class="rounded border-slate-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-indigo-500"
                                                            :class="enabled ? 'text-indigo-600' : 'text-slate-300 bg-slate-100 dark:bg-slate-800'">
                                                     <span>Siswa Saja (API)</span>
                                                 </label>
                                                 <label class="flex items-center gap-2 cursor-pointer text-xs select-none" :class="enabled ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600'">
                                                     <input type="checkbox" name="units[{{ $unit->id }}][audiences][]" value="parent" :disabled="!enabled" x-model="audiences"
                                                            class="rounded border-slate-300 dark:border-slate-800 dark:bg-slate-900 focus:ring-indigo-500"
                                                            :class="enabled ? 'text-indigo-600' : 'text-slate-300 bg-slate-100 dark:bg-slate-800'">
                                                     <span>Orang Tua Saja (API)</span>
                                                 </label>
                                             </div>
                                         </div>
                                    </div>
                                @empty
                                    <div class="md:col-span-3 text-center p-6 bg-slate-50 dark:bg-slate-900/10 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                                        Tidak ada unit sekolah aktif yang terdaftar untuk sinkronisasi.
                                    </div>
                                @endforelse
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2">Aktifkan switch unit sekolah di atas untuk menyinkronkan pengumuman ini secara otomatis ke unit tersebut.</p>
                        </div>
                    </div>

                    <!-- Right Column (Category, Dates, File Attachment) -->
                    <div class="space-y-6">
                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select name="category" id="category" required class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                <option value="umum" {{ old('category') == 'umum' ? 'selected' : '' }}>Umum / Info Sekolah</option>
                                <option value="akademik" {{ old('category') == 'akademik' ? 'selected' : '' }}>Kurikulum / Akademik</option>
                                <option value="kepegawaian" {{ old('category') == 'kepegawaian' ? 'selected' : '' }}>Kepegawaian / HRD</option>
                                <option value="penting" {{ old('category') == 'penting' ? 'selected' : '' }}>Penting / Urgent</option>
                            </select>
                            @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Publish Date -->
                        <div>
                            <label for="publish_date" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Tanggal Terbit</label>
                            <input type="datetime-local" name="publish_date" id="publish_date" value="{{ old('publish_date', now()->format('Y-m-d\TH:i')) }}" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            @error('publish_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="expiry_date" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Tanggal Berakhir</label>
                            <input type="datetime-local" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika pengumuman terus berlaku selamanya.</p>
                            @error('expiry_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Attachment -->
                        <div>
                            <label for="attachment" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">File Lampiran</label>
                             <input type="file" name="attachment" id="attachment" class="mt-1 block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 dark:file:bg-slate-900 dark:hover:file:bg-slate-800">
                            <p class="text-[10px] text-slate-400 mt-1">Format: PDF, Word, Excel, Gambar (Max 2MB)</p>
                            @error('attachment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Publish Immediately switch -->
                        <div class="flex items-center pt-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="is_active" class="ml-2 block text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Langsung Aktifkan & Terbitkan</label>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('announcements.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-705 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition cursor-pointer">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 dark:bg-slate-50 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-bold rounded-lg shadow-sm transition-all cursor-pointer">
                        Terbitkan Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <style>
        html.dark .cke_chrome {
            border-color: #1e293b !important;
        }
        html.dark .cke_top, html.dark .cke_bottom {
            background: #09090b !important;
            border-color: #1e293b !important;
        }
        html.dark .cke_toolgroup {
            background: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark .cke_button_icon {
            filter: invert(0.85) !important;
        }
        html.dark .cke_button:hover {
            background: #334155 !important;
        }
        html.dark .cke_resizer {
            border-color: transparent transparent #94a3b8 transparent !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            CKEDITOR.replace('content', {
                height: 320,
                versionCheck: false,
                removeButtons: 'PasteFromWord,Image,Table',
                on: {
                    instanceReady: function(evt) {
                        const body = evt.editor.document.getBody();
                        const isDark = document.documentElement.classList.contains('dark');
                        if (isDark) {
                            body.setStyle('background-color', '#09090b');
                            body.setStyle('color', '#cbd5e1');
                        } else {
                            body.setStyle('background-color', '#ffffff');
                            body.setStyle('color', '#333333');
                        }
                    }
                }
            });

            // Listen to theme changes dynamically
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        const isDark = document.documentElement.classList.contains('dark');
                        for (const instance in CKEDITOR.instances) {
                            const body = CKEDITOR.instances[instance].document.getBody();
                            if (isDark) {
                                body.setStyle('background-color', '#09090b');
                                body.setStyle('color', '#cbd5e1');
                            } else {
                                body.setStyle('background-color', '#ffffff');
                                body.setStyle('color', '#333333');
                            }
                        }
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true });
        });
    </script>
</x-admin-layout>
