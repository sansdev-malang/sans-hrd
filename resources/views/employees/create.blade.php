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
                    <p class="text-xs text-slate-500 dark:text-slate-400">Mohon perbaiki kolom yang bermasalah di bawah ini.</p>
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
            <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-6 text-xs">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Unit Sekolah -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Unit Sekolah Tujuan</label>
                        <select name="school_unit_id" id="school_unit_id" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('school_unit_id') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror cursor-pointer">
                            <option value="">-- Pilih Unit Sekolah --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('school_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('school_unit_id')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Drs. Eko Wibowo, M.Pd"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('name') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror">
                        @error('name')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: nama@sans.dev"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('email') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror">
                        @error('email')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- NIP / NUPTK / NIK -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NIP / NUPTK / NIK</label>
                        <input type="text" name="nuptk_nip_nik" value="{{ old('nuptk_nip_nik') }}" placeholder="Masukkan nomor identitas"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('nuptk_nip_nik') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror">
                        @error('nuptk_nip_nik')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Tipe Pegawai -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe Pegawai</label>
                        <select name="employee_type_code" id="employee_type_code" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('employee_type_code') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror cursor-pointer">
                            <option value="">-- Pilih Unit Sekolah Dahulu --</option>
                        </select>
                        @error('employee_type_code')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Jabatan / Mapel -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan / Bidang Studi</label>
                        <input type="text" name="subject_position" value="{{ old('subject_position') }}" placeholder="Contoh: Matematika, Pustakawan, Security"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('subject_position') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror">
                        @error('subject_position')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                        <select name="gender" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('gender') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror cursor-pointer">
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status Kepegawaian -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                        <select name="employment_status" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('employment_status') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror cursor-pointer">
                            <option value="PNS" {{ old('employment_status') == 'PNS' ? 'selected' : '' }}>PNS (Pegawai Negeri Sipil)</option>
                            <option value="Tetap Yayasan" {{ old('employment_status') == 'Tetap Yayasan' ? 'selected' : '' }}>Tetap Yayasan</option>
                            <option value="Honorer" {{ old('employment_status') == 'Honorer' ? 'selected' : '' }}>Honorer</option>
                            <option value="Kontrak" {{ old('employment_status') == 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                        </select>
                        @error('employment_status')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- UID ZKTeco -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">UID Fingerprint (ZKTeco)</label>
                        <input type="text" name="zkteco_uid" value="{{ old('zkteco_uid') }}" placeholder="Contoh: 101, 102"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('zkteco_uid') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror">
                        <p class="text-[10px] text-slate-450 dark:text-slate-500 mt-1 leading-normal">Opsional: Rekomendasi biarkan kosong. Admin unit sekolah akan mengisi ini setelah mendaftarkan sidik jari di mesin fisik.</p>
                        @error('zkteco_uid')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Keaktifan</label>
                        <select name="status" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 @error('status') border-rose-500 dark:border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-950/40 @else border-slate-200 dark:border-slate-800 focus:ring-slate-100 dark:focus:ring-slate-800 @enderror cursor-pointer">
                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Aktif (Dapat Melakukan Absen)</option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Non-Aktif (Ditangguhkan)</option>
                        </select>
                        @error('status')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Foto Pegawai -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Foto Pegawai</label>
                        <input type="file" name="photo" accept="image/*"
                            class="w-full text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-900 border rounded-lg file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 focus:outline-none @error('photo') border-rose-500 dark:border-rose-500 @else border-slate-200 dark:border-slate-800 @enderror">
                        <p class="text-[10px] text-slate-450 dark:text-slate-500 mt-1">Opsional: Format JPEG, PNG, JPG, GIF (Max. 2MB)</p>
                        @error('photo')
                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                    <a href="{{ route('employees.index') }}" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-750 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all flex items-center justify-center cursor-pointer">Batal</a>
                    <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">Simpan Data Pegawai</button>
                </div>
            </form>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const schoolUnitSelect = document.getElementById('school_unit_id');
            const employeeTypeSelect = document.getElementById('employee_type_code');

            function loadEmployeeTypes(unitId, selectedCode = null) {
                if (!unitId) {
                    employeeTypeSelect.innerHTML = '<option value="">-- Pilih Unit Sekolah Dahulu --</option>';
                    employeeTypeSelect.disabled = true;
                    return;
                }

                employeeTypeSelect.disabled = true;
                employeeTypeSelect.innerHTML = '<option value="">Memuat tipe pegawai...</option>';

                fetch(`/school-units/${unitId}/employee-types`)
                    .then(response => response.json())
                    .then(data => {
                        employeeTypeSelect.innerHTML = '';
                        if (data.length === 0) {
                            employeeTypeSelect.innerHTML = '<option value="">Tidak ada tipe pegawai tersedia</option>';
                            return;
                        }
                        data.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.code;
                            option.textContent = `${type.name} (${type.code})`;
                            if (selectedCode && type.code === selectedCode) {
                                option.selected = true;
                            }
                            employeeTypeSelect.appendChild(option);
                        });
                        employeeTypeSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading employee types:', error);
                        employeeTypeSelect.innerHTML = '<option value="">Gagal memuat data tipe</option>';
                    });
            }

            // Trigger on unit change
            schoolUnitSelect.addEventListener('change', (e) => {
                loadEmployeeTypes(e.target.value);
            });

            // Trigger on load if unit is old/preset
            if (schoolUnitSelect.value) {
                loadEmployeeTypes(schoolUnitSelect.value, "{{ old('employee_type_code') }}");
            } else {
                employeeTypeSelect.innerHTML = '<option value="">-- Pilih Unit Sekolah Dahulu --</option>';
                employeeTypeSelect.disabled = true;
            }
        });
    </script>
</x-admin-layout>
