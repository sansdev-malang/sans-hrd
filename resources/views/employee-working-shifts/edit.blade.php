<x-admin-layout>
    <style>
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar { 
            width: 14px !important; 
            display: block !important;
        }
        .custom-scrollbar::-webkit-scrollbar-track { 
            background: #f8fafc !important; 
            border-radius: 8px !important;
            border: 1px solid #e2e8f0;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb { 
            background-color: #64748b !important; 
            border-radius: 8px !important; 
            border: 3px solid #f8fafc !important; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { 
            background-color: #475569 !important; 
        }
        .dark .custom-scrollbar {
            scrollbar-color: #475569 #0f172a;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-track { 
            background: #0f172a !important; 
            border: 1px solid #1e293b;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { 
            border: 3px solid #0f172a !important; 
        }
    </style>
    <div class="p-6 max-w-4xl mx-auto space-y-6" x-data="{ 
        selectedUnit: '{{ $unit_id }}',
        employees: [],
        searchQuery: '',
        isLoadingEmployees: false,
        selectAll: false,
        initialEmployeeIds: {{ json_encode($employeeIds) }},
        
        async fetchEmployees(isInitial = false) {
            if (!this.selectedUnit) {
                this.employees = [];
                return;
            }
            this.isLoadingEmployees = true;
            try {
                let response = await fetch(`/employee-working-shifts/unit/${this.selectedUnit}/employees`);
                this.employees = await response.json();
                
                if (isInitial) {
                    // We wait for the DOM to update to check the boxes
                    this.$nextTick(() => {
                        let checkboxes = document.querySelectorAll('.employee-checkbox');
                        checkboxes.forEach(cb => {
                            if (this.initialEmployeeIds.includes(parseInt(cb.value))) {
                                cb.checked = true;
                            }
                        });
                    });
                } else {
                    this.selectAll = false;
                }
                this.searchQuery = '';
            } catch (e) {
                console.error(e);
            } finally {
                this.isLoadingEmployees = false;
            }
        },

        get filteredEmployees() {
            if (this.searchQuery === '') {
                return this.employees;
            }
            return this.employees.filter(emp => {
                const nameMatch = emp.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const nipMatch = emp.nuptk_nip_nik && String(emp.nuptk_nip_nik).toLowerCase().includes(this.searchQuery.toLowerCase());
                return nameMatch || nipMatch;
            });
        },

        toggleAll() {
            let checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(cb => {
                let label = cb.closest('label');
                if (label && label.style.display !== 'none') {
                    cb.checked = this.selectAll;
                }
            });
        },

        init() {
            if (this.selectedUnit) {
                this.fetchEmployees(true);
            }
        }
    }">

        <!-- HEADER -->
        <header class="flex flex-col gap-0.5 w-full text-left">
            <div class="flex items-center gap-3">
                <a href="{{ route('employee-working-shifts.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Edit Batch Penugasan Shift</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 ml-11">Ubah tanggal, shift, atau tambah/kurangi pegawai dalam grup jadwal ini.</p>
        </header>

        <!-- FORM -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <form method="POST" action="{{ route('employee-working-shifts.update-batch') }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="old_school_unit_id" value="{{ $unit_id }}">
                <input type="hidden" name="old_working_shift_id" value="{{ $shift_id }}">
                <input type="hidden" name="old_start_date" value="{{ $start }}">
                <input type="hidden" name="old_end_date" value="{{ $end }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-full">
                    <div class="space-y-6">
                        <!-- Unit Sekolah -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-sm">Pilih Unit Sekolah</label>
                            <select name="school_unit_id" required x-model="selectedUnit" @change="fetchEmployees(false)" class="text-xs w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Pilih Unit Sekolah --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Template Shift -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-sm">Pilih Template Shift Kerja</label>
                                <select name="working_shift_id" required class="text-xs w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="">-- Pilih Template Shift --</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ $shift_id == $shift->id ? 'selected' : '' }}>{{ $shift->name }} ({{ $shift->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Bonus Schema -->
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-sm">Skema Bonus (Opsional)</label>
                                <select name="bonus_schema_id" class="text-xs w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="">-- Ikuti Default/Aktif --</option>
                                    @if(isset($bonusSchemas))
                                        @foreach($bonusSchemas as $schema)
                                            <option value="{{ $schema->id }}" {{ (isset($bonus_schema_id) && $bonus_schema_id == $schema->id) ? 'selected' : '' }}>{{ $schema->name }} {{ $schema->is_active ? '(Aktif)' : '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-sm">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="{{ $start }}" required class="text-xs w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 text-sm">Tanggal Selesai (Opsional)</label>
                                <input type="date" name="end_date" value="{{ $end !== 'null' ? $end : '' }}" class="text-xs w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- Pegawai List -->
                    <div class="flex flex-col h-full">
                        <label class="font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex justify-between items-center text-sm shrink-0">
                            <span>
                                Pilih Pegawai 
                                <span x-show="isLoadingEmployees" class="text-xs text-indigo-500 ml-2 animate-pulse font-normal">Memuat data...</span>
                            </span>
                            <label x-show="employees.length > 0" class="flex items-center gap-1.5 cursor-pointer text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                Pilih Semua
                            </label>
                        </label>
                        
                        <div class="flex flex-col bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                            <!-- Search Bar -->
                            <div class="p-2 border-b border-slate-200 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900">
                                <div class="relative flex items-center">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none"></i>
                                    <input type="text" x-model="searchQuery" :disabled="employees.length === 0" placeholder="Cari nama atau NIK..." style="padding-left: 2.25rem;" class="text-xs w-full pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 disabled:opacity-50">
                                </div>
                            </div>

                            <!-- List -->
                            <div class="p-3 space-y-2 custom-scrollbar" style="height: 350px; overflow-y: scroll; overscroll-behavior: contain;">
                                <div x-show="employees.length === 0 && !isLoadingEmployees" class="flex items-center justify-center h-full text-slate-400 italic text-xs">
                                    Silakan pilih unit sekolah terlebih dahulu.
                                </div>
                                
                                <div x-show="employees.length > 0 && filteredEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 italic text-xs">
                                    Pegawai tidak ditemukan.
                                </div>

                                <template x-for="emp in employees" :key="emp.id">
                                    <label 
                                        x-show="searchQuery === '' || emp.name.toLowerCase().includes(searchQuery.toLowerCase()) || (emp.nuptk_nip_nik && String(emp.nuptk_nip_nik).toLowerCase().includes(searchQuery.toLowerCase()))"
                                        class="flex items-center gap-3 cursor-pointer p-3 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg transition-colors shadow-sm">
                                        <input type="checkbox" name="employee_ids[]" :value="emp.id" class="text-xs employee-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 shrink-0">
                                        <div class="flex flex-col">
                                            <span class="text-xs text-slate-900 dark:text-slate-100 font-semibold leading-snug" x-text="emp.name"></span>
                                            <span class="text-xs text-slate-500 mt-0.5" x-text="`NIP/NIK: ${emp.nuptk_nip_nik || '-'}`"></span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-6 border-t border-slate-100 dark:border-slate-900 justify-end">
                    <a href="{{ route('employee-working-shifts.index') }}" class="h-10 px-5 inline-flex items-center justify-center bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                        Batal
                    </a>
                    <button type="submit" class="h-10 px-6 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-admin-layout>
