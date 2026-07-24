<x-admin-layout>
    <div class="p-6 max-w-6xl mx-auto space-y-6">

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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
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
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: nama@sans.dev"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>

                    <!-- Tipe Pegawai -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe Pegawai</label>
                        <select name="employee_type_code" id="employee_type_code" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800 cursor-pointer">
                            <option value="">-- Pilih Unit Sekolah Dahulu --</option>
                        </select>
                    </div>

                    <!-- DATA DIRI -->
                    <div class="md:col-span-2 mt-4 mb-2 border-b pb-2"><h4 class="font-bold text-slate-700 dark:text-slate-300">Data Diri</h4></div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                        <select name="gender" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800 cursor-pointer">
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Alamat</label>
                        <input type="text" name="address" value="{{ old('address') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">No. HP / WA</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>

                    <!-- DATA KEPEGAWAIAN -->
                    <div class="md:col-span-2 mt-4 mb-2 border-b pb-2"><h4 class="font-bold text-slate-700 dark:text-slate-300">Data Kepegawaian & Identitas</h4></div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NIK</label>
                        <input type="text" name="nik" value="{{ old('nik') }}" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NIY</label>
                        <input type="text" name="niy" value="{{ old('niy') }}" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NUPTK</label>
                        <input type="text" name="nuptk" value="{{ old('nuptk') }}" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NO UKG</label>
                        <input type="text" name="no_ukg" value="{{ old('no_ukg') }}" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">NRG</label>
                        <input type="text" name="nrg" value="{{ old('nrg') }}" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Pangkat / Golongan</label>
                        <input type="text" name="pangkat_golongan" value="{{ old('pangkat_golongan') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Pendidikan Terakhir</label>
                        <input type="text" name="last_education" value="{{ old('last_education') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jurusan</label>
                        <input type="text" name="major" value="{{ old('major') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan Utama</label>
                        <input type="text" name="position" value="{{ old('position') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Jabatan Tambahan</label>
                        <input type="text" name="additional_position" value="{{ old('additional_position') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                        <input type="text" name="employment_status" value="{{ old('employment_status') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Mulai Tugas</label>
                        <input type="date" name="task_start_date" value="{{ old('task_start_date') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal Diangkat</label>
                        <input type="date" name="appointment_date" value="{{ old('appointment_date') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Tanggal SK Terakhir</label>
                        <input type="date" name="last_sk_date" value="{{ old('last_sk_date') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Nomor SK Terakhir</label>
                        <input type="text" name="last_sk_number" value="{{ old('last_sk_number') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Masa Kerja Golongan</label>
                        <input type="text" name="work_period" value="{{ old('work_period') }}" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>

                    <!-- SISTEM ABSENSI -->
                    <div class="md:col-span-2 mt-4 mb-2 border-b pb-2"><h4 class="font-bold text-slate-700 dark:text-slate-300">Sistem Absensi & Foto</h4></div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">ID ZKTeco / PIN Fingerprint</label>
                        <input type="text" name="zkteco_uid" value="{{ old('zkteco_uid') }}" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Status Keaktifan</label>
                        <select name="status" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 border-slate-200 dark:border-slate-800 cursor-pointer">
                            <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Aktif</option>
                            <option value="Leave" {{ old('status') == 'Leave' ? 'selected' : '' }}>Cuti</option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Foto Profil</label>
                        <input type="file" name="photo" accept="image/*"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-50 focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-slate-200 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-300 dark:hover:file:bg-slate-700 cursor-pointer">
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
