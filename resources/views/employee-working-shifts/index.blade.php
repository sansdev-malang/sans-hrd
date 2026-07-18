<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        selectedUnit: '',
        employees: [],
        isLoadingEmployees: false,
        
        async fetchEmployees() {
            if (!this.selectedUnit) {
                this.employees = [];
                return;
            }
            this.isLoadingEmployees = true;
            try {
                let response = await fetch(`/employee-working-shifts/unit/${this.selectedUnit}/employees`);
                this.employees = await response.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.isLoadingEmployees = false;
            }
        }
    }">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Penjadwalan Shift Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur penugasan dan rotasi shift kerja masing-masing guru & karyawan di unit-unit sekolah.</p>
            </div>
            <div>
                <button @click="showAddModal = true" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    Tugaskan Shift Pegawai
                </button>
            </div>
        </header>

        <!-- FILTERS & LIST -->
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="p-4 border-b border-slate-100 dark:border-slate-900 flex flex-col sm:flex-row justify-between gap-4">
                <form method="GET" action="{{ route('employee-working-shifts.index') }}" class="flex items-center gap-3">
                    <select name="unit_id" class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:outline-none">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="h-8 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-250 text-xs font-semibold rounded-lg shadow-sm border border-slate-250/20 dark:border-slate-800 transition-all cursor-pointer">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-150 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3 text-left">Pegawai</th>
                            <th class="px-6 py-3 text-left">Unit</th>
                            <th class="px-6 py-3 text-left">Shift Ditugaskan</th>
                            <th class="px-6 py-3 text-center">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-center">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($assignments as $assignment)
                            <tr>
                                <td class="px-6 py-4 text-left">
                                    <div class="font-bold text-slate-900 dark:text-slate-50">{{ $assignment->employee_name }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-550 font-mono">NIP/NIK: {{ $assignment->employee_nip }}</div>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30 uppercase">
                                        {{ $assignment->schoolUnit ? $assignment->schoolUnit->name : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">
                                        {{ $assignment->workingShift ? $assignment->workingShift->name : '-' }}
                                    </span>
                                    <div class="text-[9px] text-slate-400 dark:text-slate-550 font-mono">CODE: {{ $assignment->workingShift ? $assignment->workingShift->code : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $assignment->start_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    @if($assignment->end_date)
                                        {{ $assignment->end_date->format('d M Y') }}
                                    @else
                                        <span class="text-emerald-500 font-bold uppercase text-[9px]">Aktif Seterusnya</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('employee-working-shifts.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penjadwalan shift pegawai ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="h-7 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-[10px] font-semibold rounded border border-rose-250/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="trash" class="w-3 h-3"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Tidak ada data penugasan shift pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ADD MODAL -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showAddModal = false" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-950 dark:text-slate-50">Tugaskan Shift Pegawai</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('employee-working-shifts.store') }}" class="space-y-4 text-xs">
                    @csrf
                    
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Pilih Unit Sekolah</label>
                        <select name="school_unit_id" required x-model="selectedUnit" @change="fetchEmployees()" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                            <option value="">-- Pilih Unit Sekolah --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">
                            Pilih Pegawai 
                            <span x-show="isLoadingEmployees" class="text-[10px] text-indigo-500 ml-2 animate-pulse">Memuat...</span>
                        </label>
                        <select name="employee_id" required :disabled="!selectedUnit || isLoadingEmployees" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 disabled:opacity-50">
                            <option value="">-- Pilih Pegawai --</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="`${emp.name} (${emp.nuptk_nip_nik || '-'})`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Pilih Template Shift Kerja</label>
                        <select name="working_shift_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                            <option value="">-- Pilih Template Shift --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Tanggal Mulai</label>
                            <input type="date" name="start_date" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Tanggal Selesai (Opsional)</label>
                            <input type="date" name="end_date" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
