<x-admin-layout>
    <div class="p-6 max-w-4xl mx-auto space-y-6" x-data="{ 
        selectedUnit: '',
        employees: [],
        searchQuery: '',
        isLoadingEmployees: false,
        selectAll: false,
        
        async fetchEmployees() {
            if (!this.selectedUnit) {
                this.employees = [];
                return;
            }
            this.isLoadingEmployees = true;
            try {
                let response = await fetch(`/employee-working-shifts/unit/${this.selectedUnit}/employees`);
                this.employees = await response.json();
                this.selectAll = false;
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
        }
    }">

        <!-- HEADER -->
        <header class="flex flex-col gap-0.5 w-full text-left">
            <div class="flex items-center gap-3">
                <a href="{{ route('employee-working-shifts.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Tugaskan Shift Pegawai</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 ml-11">Pilih unit, cari dan tandai pegawai yang ingin ditugaskan, lalu pilih shift.</p>
        </header>

        <!-- FORM -->
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <form method="POST" action="{{ route('employee-working-shifts.store') }}" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-full">
                    <div class="space-y-6">
                        <!-- Unit Sekolah -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5 text-sm">Pilih Unit Sekolah</label>
                            <select name="school_unit_id" required x-model="selectedUnit" @change="fetchEmployees()" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Pilih Unit Sekolah --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Template Shift -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5 text-sm">Pilih Template Shift Kerja</label>
                            <select name="working_shift_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="">-- Pilih Template Shift --</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5 text-sm">Tanggal Mulai</label>
                                <input type="date" name="start_date" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5 text-sm">Tanggal Selesai (Opsional)</label>
                                <input type="date" name="end_date" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- Pegawai List -->
                    <div class="flex flex-col h-full">
                        <label class="font-semibold text-slate-700 dark:text-slate-350 mb-1.5 flex justify-between items-center text-sm shrink-0">
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
                            <div class="p-2 border-b border-slate-200 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-950">
                                <div class="relative flex items-center">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none"></i>
                                    <input type="text" x-model="searchQuery" :disabled="employees.length === 0" placeholder="Cari nama atau NIK..." style="padding-left: 2.25rem;" class="w-full pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 disabled:opacity-50">
                                </div>
                            </div>

                            <!-- List -->
                            <div class="p-3 space-y-2" style="height: 350px; overflow-y: auto; overscroll-behavior: contain;">
                                <div x-show="employees.length === 0 && !isLoadingEmployees" class="flex items-center justify-center h-full text-slate-400 italic text-sm">
                                    Silakan pilih unit sekolah terlebih dahulu.
                                </div>
                                
                                <div x-show="employees.length > 0 && filteredEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 italic text-sm">
                                    Pegawai tidak ditemukan.
                                </div>

                                <template x-for="emp in employees" :key="emp.id">
                                    <label 
                                        x-show="searchQuery === '' || emp.name.toLowerCase().includes(searchQuery.toLowerCase()) || (emp.nuptk_nip_nik && String(emp.nuptk_nip_nik).toLowerCase().includes(searchQuery.toLowerCase()))"
                                        class="flex items-center gap-3 cursor-pointer p-3 bg-white dark:bg-slate-950 hover:bg-slate-100 dark:hover:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg transition-colors shadow-sm">
                                        <input type="checkbox" name="employee_ids[]" :value="emp.id" class="employee-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 shrink-0">
                                        <div class="flex flex-col">
                                            <span class="text-slate-900 dark:text-slate-100 font-semibold leading-snug" x-text="emp.name"></span>
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
                        Simpan Penugasan Shift
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-admin-layout>
