<x-admin-layout>
    <div class="p-6 max-w-4xl mx-auto space-y-6">

        <!-- ERROR ALERT -->
        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Terjadi Kesalahan!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col gap-0.5 text-left">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Tambah Pegawai Baru</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Tambahkan guru atau staf baru secara terpusat ke dalam unit sekolah terkait.</p>
        </header>

        <!-- FORM CARD -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 text-left">
            <form method="POST" action="{{ route('employees.store') }}" class="space-y-6 text-xs">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Unit Sekolah -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Unit Sekolah Tujuan</label>
                        <select name="school_unit_id" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                            <option value="">-- Pilih Unit Sekolah --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('school_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Drs. Eko Wibowo, M.Pd"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: nama@sans.dev"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <!-- NIP / NUPTK / NIK -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NIP / NUPTK / NIK</label>
                        <input type="text" name="nuptk_nip_nik" value="{{ old('nuptk_nip_nik') }}" required placeholder="Masukkan nomor identitas"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <!-- Tipe Pegawai -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe Pegawai</label>
                        <select name="employee_type_code" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                            <option value="teacher" {{ old('employee_type_code') == 'teacher' ? 'selected' : '' }}>Guru (Pendidik)</option>
                            <option value="employee" {{ old('employee_type_code') == 'employee' ? 'selected' : '' }}>Staf / Karyawan (Kependidikan)</option>
                        </select>
                    </div>

                    <!-- Jabatan / Mapel -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan / Bidang Studi</label>
                        <input type="text" name="subject_position" value="{{ old('subject_position') }}" required placeholder="Contoh: Matematika, Pustakawan, Security"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                        <select name="gender" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <!-- Status Kepegawaian -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                        <select name="employment_status" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                            <option value="PNS" {{ old('employment_status') == 'PNS' ? 'selected' : '' }}>PNS (Pegawai Negeri Sipil)</option>
                            <option value="Tetap Yayasan" {{ old('employment_status') == 'Tetap Yayasan' ? 'selected' : '' }}>Tetap Yayasan</option>
                            <option value="Honorer" {{ old('employment_status') == 'Honorer' ? 'selected' : '' }}>Honorer</option>
                            <option value="Kontrak" {{ old('employment_status') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                        </select>
                    </div>

                    <!-- UID ZKTeco -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">UID Fingerprint (ZKTeco)</label>
                        <input type="text" name="zkteco_uid" value="{{ old('zkteco_uid') }}" required placeholder="Contoh: 101, 102"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Keaktifan</label>
                        <select name="status" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Aktif (Dapat Melakukan Absen)</option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Non-Aktif (Ditangguhkan)</option>
                        </select>
                    </div>

                </div>

                <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                    <a href="{{ route('employees.index') }}" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-750 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all flex items-center justify-center cursor-pointer">Batal</a>
                    <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">Simpan Data Pegawai</button>
                </div>
            </form>
        </section>
    </div>
</x-admin-layout>
