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
    <div class="p-6 space-y-6" x-data="{
        showModal: false,
        selectedBatch: null,
        searchModal: '',
    
        showCreateModal: false,
        createUnitId: '{{ $units->first()->id ?? '' }}',
        empList: [],
        loadingEmp: false,
        empSearch: '',
        selectedEmps: [],
        selectAllEmp: false,
        showAssignmentModal: false,
    
        openModal(batch) {
            this.selectedBatch = batch;
            this.searchModal = '';
            this.showModal = true;
        },
    
        async loadEmployeesForUnit() {
            if (!this.createUnitId) return;
            this.loadingEmp = true;
            try {
                const response = await fetch(`/employee-working-shifts/unit/${this.createUnitId}/employees`);
                if (response.ok) {
                    this.empList = await response.json();
                } else {
                    this.empList = [];
                }
            } catch (e) {
                console.error(e);
                this.empList = [];
            }
            this.loadingEmp = false;
        },
    
        init() {
            this.$watch('showModal', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
            this.$watch('showAssignmentModal', value => { document.body.style.overflow = value ? 'hidden' : ''; });
            this.$watch('showCreateModal', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
            this.$watch('selectAllEmp', value => {
                if (value) {
                    this.selectedEmps = this.empList.map(e => e.id);
                } else {
                    this.selectedEmps = [];
                }
            });
        }
    }">



        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">Penjadwalan Shift Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur penugasan dan rotasi shift kerja secara kolektif per unit sekolah.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showCreateModal = true; loadEmployeesForUnit()"
                    class="h-9 px-4 inline-flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-650 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-all cursor-pointer gap-2">
                    <i data-lucide="calendar-plus" class="w-4.5 h-4.5"></i>
                    Buat Roster Bulanan (Grid)
                </button>
                <button type="button" @click="showAssignmentModal = true"
                    class="h-9 px-4 inline-flex items-center justify-center bg-slate-900 dark:bg-slate-100 hover:bg-slate-855 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer gap-2">
                    <i data-lucide="plus" class="w-4.5 h-4.5"></i>
                    Tugaskan Shift Baru
                </button>
            </div>
        </header>

        <!-- FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('employee-working-shifts.index') }}" class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <select name="unit_id" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-48 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>

                    @if ($selectedUnitId || request()->filled('per_page') && request('per_page') != 50)
                        <a href="{{ route('employee-working-shifts.index') }}" 
                           class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" 
                           title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- Right Side: Per Page Options -->
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 self-end md:self-auto justify-end">
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-24 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                        <option value="500" {{ request('per_page') == '500' ? 'selected' : '' }}>500 baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </section>

        <!-- TABLE GRID -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left w-full">
            <div class="overflow-x-auto custom-scrollbar w-full">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-4 text-left w-[32%] whitespace-nowrap">Grup Penugasan</th>
                            <th class="px-6 py-4 text-left w-[18%] whitespace-nowrap">Unit Sekolah</th>
                            <th class="px-6 py-4 text-center w-[25%] whitespace-nowrap">Periode Jadwal</th>
                            <th class="px-6 py-4 text-center w-[13%] whitespace-nowrap">Jumlah Pegawai</th>
                            <th class="px-6 py-4 text-right w-[12%] whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($batches as $batch)
                            @if (isset($batch['type']) && $batch['type'] == 'roster')
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 text-left">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100/20 dark:border-indigo-900/10">
                                                <i data-lucide="calendar-range" class="w-4 h-4"></i>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ !empty($batch['roster_name']) ? $batch['roster_name'] : 'Roster Shift Bulanan' }}</span>
                                                <span class="inline-flex w-max px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wide">Bulanan / Roster</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-left whitespace-nowrap">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300 text-xs">{{ $batch['unit_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="text-xs font-semibold text-slate-900 dark:text-slate-200">{{ \Carbon\Carbon::create($batch['year'], $batch['month'], 1)->translatedFormat('F Y') }}</span>
                                            <span class="px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[9px] font-bold uppercase tracking-wider border border-blue-100/30 dark:border-blue-900/30">Bulanan</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <button @click="openModal({{ json_encode($batch) }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors font-bold text-xs border border-indigo-100 dark:border-indigo-800/30 cursor-pointer shadow-2xs whitespace-nowrap">
                                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                            <span>{{ count($batch['employees']) }} Orang</span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('employee-working-shifts.detail-roster', [
                                                'unit_id' => $batch['school_unit_id'],
                                                'month' => $batch['month'],
                                                'year' => $batch['year'],
                                                'roster_name' => $batch['roster_name'] ?? '',
                                            ]) }}"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 rounded-lg border border-indigo-200/30 dark:border-indigo-900/30 transition-all cursor-pointer"
                                                title="Lihat Detail Roster">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route('employee-working-shifts.roster', [
                                                'unit_id' => $batch['school_unit_id'],
                                                'month' => $batch['month'],
                                                'year' => $batch['year'],
                                                'roster_name' => $batch['roster_name'] ?? '',
                                            ]) }}"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all"
                                                title="Edit Roster">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
                                            <form action="{{ route('employee-working-shifts.destroy-roster') }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus roster bulanan ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="unit_id"
                                                    value="{{ $batch['school_unit_id'] }}">
                                                <input type="hidden" name="month" value="{{ $batch['month'] }}">
                                                <input type="hidden" name="year" value="{{ $batch['year'] }}">
                                                <input type="hidden" name="roster_name"
                                                    value="{{ $batch['roster_name'] ?? '' }}">
                                                <button type="submit"
                                                    class="h-8 w-8 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-450 rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer"
                                                    title="Hapus Roster">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4 text-left">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-400 flex items-center justify-center shrink-0 border border-slate-100/40 dark:border-slate-800/60">
                                                <i data-lucide="clock-3" class="w-4 h-4"></i>
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs">{{ $batch['shift_name'] }}</span>
                                                <span class="inline-flex w-max px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-slate-900/40 text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-800 uppercase tracking-wide">Kode: {{ $batch['shift_code'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-left whitespace-nowrap">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300 text-xs">{{ $batch['unit_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-900 dark:text-slate-200">
                                            <span>{{ \Carbon\Carbon::parse($batch['start_date'])->translatedFormat('d M Y') }}</span>
                                            <span class="text-slate-400 dark:text-slate-500 font-normal text-[10px]">s/d</span>
                                            @if ($batch['end_date'])
                                                <span>{{ \Carbon\Carbon::parse($batch['end_date'])->translatedFormat('d M Y') }}</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-wider border border-emerald-100/30 dark:border-emerald-900/30 whitespace-nowrap">Seterusnya</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <button @click="openModal({{ json_encode($batch) }})"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors font-bold text-xs border border-indigo-100 dark:border-indigo-800/30 cursor-pointer shadow-2xs whitespace-nowrap">
                                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                            <span>{{ count($batch['employees']) }} Orang</span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('employee-working-shifts.edit-batch', [
                                                'unit_id' => $batch['school_unit_id'],
                                                'shift_id' => $batch['working_shift_id'],
                                                'start_date' => \Carbon\Carbon::parse($batch['start_date'])->format('Y-m-d'),
                                                'end_date' => $batch['end_date'] ? \Carbon\Carbon::parse($batch['end_date'])->format('Y-m-d') : 'null',
                                            ]) }}"
                                                class="h-8 w-8 inline-flex items-center justify-center bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all"
                                                title="Edit Penugasan">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </a>
 
                                            <form action="{{ route('employee-working-shifts.destroy-batch') }}"
                                                method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus seluruh penugasan shift pada grup ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="unit_id"
                                                    value="{{ $batch['school_unit_id'] }}">
                                                <input type="hidden" name="shift_id"
                                                    value="{{ $batch['working_shift_id'] }}">
                                                <input type="hidden" name="start_date"
                                                    value="{{ \Carbon\Carbon::parse($batch['start_date'])->format('Y-m-d') }}">
                                                <input type="hidden" name="end_date"
                                                    value="{{ $batch['end_date'] ? \Carbon\Carbon::parse($batch['end_date'])->format('Y-m-d') : 'null' }}">
 
                                                <button type="submit"
                                                    class="h-8 w-8 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-450 rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer"
                                                    title="Hapus Penugasan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3 text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                                            <i data-lucide="calendar-x" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum ada jadwal shift</h4>
                                            <p class="text-xs mt-1">Silakan tugaskan shift baru untuk pegawai Anda.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($batches instanceof \Illuminate\Pagination\LengthAwarePaginator && $batches->total() > 0)
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $batches->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $batches->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $batches->total() }}</span>
                        data penjadwalan shift
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if ($batches->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none">Sebelumnya</span>
                        @else
                            <a href="{{ $batches->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Sebelumnya</a>
                        @endif

                        <span class="px-2 font-semibold text-slate-600 dark:text-slate-400">
                            Halaman {{ $batches->currentPage() }} dari {{ $batches->lastPage() }}
                        </span>

                        @if ($batches->hasMorePages())
                            <a href="{{ $batches->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- MODAL DETAIL PEGAWAI -->
        <template x-teleport="body">
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm text-left" style="z-index: 9999; display: none;">
                
                <div @click.outside="showModal = false"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden text-left text-xs">
                    
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                Daftar Pegawai
                            </h3>
                             <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                 Jadwal: <span class="font-bold text-slate-600 dark:text-slate-300" x-text="selectedBatch?.type === 'roster' ? (selectedBatch?.roster_name || 'Roster Shift Bulanan') : (selectedBatch?.shift_name || '-')"></span> -
                                 <span class="font-bold text-slate-600 dark:text-slate-300" x-text="selectedBatch?.unit_name"></span>
                             </p>
                        </div>
                        <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto p-5 space-y-4">
                        <!-- Search within Modal -->
                        <div class="relative flex items-center shrink-0">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none"></i>
                            <input type="text" x-model="searchModal" placeholder="Cari nama pegawai..."
                                class="w-full text-xs px-3.5 py-2 pl-9 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all">
                        </div>

                        <div class="custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <template x-for="emp in (selectedBatch?.employees || []).filter(e => e.name.toLowerCase().includes(searchModal.toLowerCase()) || (e.nip && e.nip.toLowerCase().includes(searchModal.toLowerCase())))" :key="emp.id">
                                    <div class="flex items-center gap-3 p-3 bg-slate-50/50 dark:bg-slate-900/30 border border-slate-200/60 dark:border-slate-800/80 rounded-xl transition-all hover:border-slate-300 dark:hover:border-slate-700">
                                         <div class="w-8 h-8 rounded-xl overflow-hidden bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-indigo-100/20 dark:border-indigo-900/10 shadow-3xs">
                                             <template x-if="emp.photo && emp.unit_url">
                                                 <img :src="emp.photo.includes('photos/') ? emp.unit_url + '/storage/' + emp.photo : emp.unit_url + '/storage/photos/' + emp.photo" 
                                                      class="w-full h-full object-cover">
                                             </template>
                                             <template x-if="!emp.photo || !emp.unit_url">
                                                 <span x-text="emp.name.substring(0, 2)"></span>
                                             </template>
                                         </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="emp.name"></span>
                                            <span class="text-[10px] font-mono text-slate-400 mt-0.5 truncate" x-text="emp.nip ? `NIP/NIK: ${emp.nip}` : 'NIP/NIK: -'"></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="(selectedBatch?.employees || []).filter(e => e.name.toLowerCase().includes(searchModal.toLowerCase()) || (e.nip && e.nip.toLowerCase().includes(searchModal.toLowerCase()))).length === 0"
                                     class="col-span-full py-8 text-center text-xs text-slate-400 dark:text-slate-500 italic">
                                    Pegawai tidak ditemukan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-5 border-t border-slate-100 dark:border-slate-850 flex items-center justify-between bg-slate-50 dark:bg-slate-900/40 shrink-0">
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500" x-text="`Total: ${selectedBatch?.employees?.length || 0} Pegawai`"></span>
                        <button type="button"
                            class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer"
                            @click="showModal = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- MODAL TUGASKAN SHIFT BARU -->
        <template x-teleport="body">
            <div x-show="showAssignmentModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showAssignmentModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm text-left" style="z-index: 9999; display: none;">
                
                <div @click.outside="showAssignmentModal = false"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full sm:max-w-4xl rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden text-left text-xs"
                     x-data="{ selectedUnit: '', employees: [], searchQuery: '', isLoadingEmployees: false, selectAll: false, async fetchEmployees() { if (!this.selectedUnit) { this.employees = []; return } this.isLoadingEmployees = true; try { let r = await fetch('/employee-working-shifts/unit/' + this.selectedUnit + '/employees');
                                 this.employees = await r.json();
                                 this.selectAll = false;
                                 this.searchQuery = '' } finally { this.isLoadingEmployees = false } }, get filteredEmployees() { return this.employees.filter(e => !this.searchQuery || e.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || (e.nuptk_nip_nik && String(e.nuptk_nip_nik).toLowerCase().includes(this.searchQuery.toLowerCase()))) }, toggleAll() { document.querySelectorAll('.employee-checkbox').forEach(c => c.checked = this.selectAll) } }">
                    
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                <i data-lucide="plus-circle" class="w-5 h-5 text-indigo-650 dark:text-indigo-400 shrink-0"></i>
                                <span>Tugaskan Shift Pegawai Baru</span>
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Pilih unit, pegawai, dan template shift yang akan ditugaskan.</p>
                        </div>
                        <button type="button" @click="showAssignmentModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('employee-working-shifts.store') }}" class="flex flex-col flex-1 overflow-hidden">
                        @csrf

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-4">
                                    <!-- Unit Sekolah -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Unit Sekolah</label>
                                        <select name="school_unit_id" required x-model="selectedUnit" @change="fetchEmployees()"
                                            class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                            <option value="">-- Pilih Unit Sekolah --</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Template Shift -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pilih Template Shift Kerja</label>
                                            <select name="working_shift_id" required
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                                <option value="">-- Pilih Template Shift --</option>
                                                @foreach ($shifts as $shift)
                                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Bonus Schema -->
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Skema Bonus (Opsional)</label>
                                            <select name="bonus_schema_id"
                                                class="text-xs w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                                <option value="">-- Ikuti Default/Aktif --</option>
                                                @if (isset($bonusSchemas))
                                                    @foreach ($bonusSchemas as $schema)
                                                        <option value="{{ $schema->id }}">{{ $schema->name }} {{ $schema->is_active ? '(Aktif)' : '' }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Mulai</label>
                                            <input type="date" name="start_date" required
                                                class="text-xs w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono dark:[color-scheme:dark]">
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Selesai (Opsional)</label>
                                            <input type="date" name="end_date"
                                                class="text-xs w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono dark:[color-scheme:dark]">
                                        </div>
                                    </div>
                                </div>

                                <!-- Pegawai List -->
                                <div class="flex flex-col h-full">
                                    <label class="font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex justify-between items-center shrink-0">
                                        <span>
                                            Pilih Pegawai
                                            <span x-show="isLoadingEmployees" class="text-[10px] text-indigo-500 ml-2 animate-pulse font-normal">Memuat data...</span>
                                        </span>
                                        <label x-show="employees.length > 0" class="flex items-center gap-1.5 cursor-pointer text-[11px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 w-3.5 h-3.5 cursor-pointer">
                                            Pilih Semua
                                        </label>
                                    </label>

                                    <div class="flex flex-col bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex-1 min-h-[220px]">
                                        <!-- Search Bar -->
                                        <div class="p-2 border-b border-slate-200/60 dark:border-slate-800/80 shrink-0 bg-white dark:bg-slate-900/40">
                                            <div class="relative flex items-center">
                                                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 pointer-events-none"></i>
                                                <input type="text" x-model="searchQuery" :disabled="employees.length === 0" placeholder="Cari nama atau NIP..."
                                                    class="text-xs w-full px-3.5 py-1.5 pl-8 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all disabled:opacity-50 font-medium">
                                            </div>
                                        </div>

                                        <!-- List -->
                                        <div class="p-3 space-y-2 custom-scrollbar overflow-y-auto max-h-[190px]">
                                            <div x-show="employees.length === 0 && !isLoadingEmployees" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-500 italic text-[11px] py-12">
                                                Silakan pilih unit sekolah terlebih dahulu.
                                            </div>

                                            <div x-show="employees.length > 0 && filteredEmployees.length === 0" class="flex items-center justify-center h-full text-slate-400 dark:text-slate-500 italic text-[11px] py-12">
                                                Pegawai tidak ditemukan.
                                            </div>
                                            
                                            <template x-for="emp in employees" :key="emp.id">
                                                <label x-show="searchQuery === '' || emp.name.toLowerCase().includes(searchQuery.toLowerCase()) || (emp.nuptk_nip_nik && String(emp.nuptk_nip_nik).toLowerCase().includes(searchQuery.toLowerCase()))"
                                                    class="flex items-center gap-3 cursor-pointer p-2.5 bg-white dark:bg-slate-950 hover:bg-slate-50 dark:hover:bg-slate-900/80 border border-slate-200 dark:border-slate-900 rounded-xl transition-all shadow-2xs hover:border-slate-300 dark:hover:border-slate-800">
                                                    <input type="checkbox" name="employee_ids[]" :value="emp.id"
                                                        class="employee-checkbox w-4.5 h-4.5 rounded border-slate-300 text-indigo-650 shadow-sm focus:ring-indigo-500 shrink-0 cursor-pointer">
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="text-xs text-slate-900 dark:text-slate-100 font-bold leading-snug truncate" x-text="emp.name"></span>
                                                        <span class="text-[10px] text-slate-400 mt-0.5 truncate" x-text="`NIP/NIK: ${emp.nuptk_nip_nik || '-'}`"></span>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-5 border-t border-slate-100 dark:border-slate-850 flex gap-2.5 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button" @click="showAssignmentModal = false"
                                class="h-9 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-950 hover:bg-slate-50 dark:hover:bg-slate-900 font-bold rounded-lg cursor-pointer transition-colors shadow-2xs hover:shadow-xs">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-855 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-bold rounded-lg shadow-sm transition-all cursor-pointer">
                                Simpan Penugasan Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
        <!-- MODAL BUAT ROSTER BARU -->
        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-hidden"
            aria-labelledby="modal-create-title" role="dialog" aria-modal="true">

            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                @click="showCreateModal = false"></div>

        <!-- MODAL BUAT ROSTER BARU -->
        <template x-teleport="body">
            <div x-show="showCreateModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showCreateModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm text-left" style="z-index: 9999; display: none;">
                
                <div @click.outside="showCreateModal = false"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full sm:max-w-2xl rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 max-h-[85vh] flex flex-col overflow-hidden text-left text-xs">
                    
                    <form action="{{ route('employee-working-shifts.roster') }}" method="GET" class="flex flex-col flex-1 overflow-hidden">
                        <!-- Header -->
                        <div class="p-5 border-b border-slate-100 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                                    <i data-lucide="calendar" class="w-5 h-5 text-indigo-650 dark:text-indigo-400 shrink-0"></i>
                                    <span>Buat Roster Bulanan Baru</span>
                                </h3>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Pilih grup pegawai yang akan dibuatkan jadwal.</p>
                            </div>
                            <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unit Sekolah</label>
                                    <select x-model="createUnitId" name="unit_id" @change="loadEmployeesForUnit()"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Roster <span class="text-rose-500">*</span></label>
                                    <input type="text" name="roster_name" required placeholder="Misal: Roster Satpam"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bulan</label>
                                    <select name="month"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all cursor-pointer">
                                        @php
                                            $bulanIndo = [
                                                '',
                                                'Januari',
                                                'Februari',
                                                'Maret',
                                                'April',
                                                'Mei',
                                                'Juni',
                                                'Juli',
                                                'Agustus',
                                                'September',
                                                'Oktober',
                                                'November',
                                                'Desember',
                                            ];
                                        @endphp
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ date('m') == $i ? 'selected' : '' }}>{{ $bulanIndo[$i] }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tahun</label>
                                    <input type="number" name="year" value="{{ date('Y') }}"
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all font-mono">
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300">Pilih Pegawai</label>
                                    <div class="flex items-center gap-2">
                                        <div class="relative flex items-center">
                                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 pointer-events-none"></i>
                                            <input type="text" x-model="empSearch" placeholder="Cari..."
                                                class="w-32 pl-7 pr-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-[11px] focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-900/30 transition-all">
                                        </div>
                                        <button type="button" @click="selectAllEmp = !selectAllEmp"
                                            class="text-[11px] text-indigo-650 dark:text-indigo-400 font-bold hover:underline bg-transparent border-0 cursor-pointer">Pilih Semua</button>
                                    </div>
                                </div>
                                <div class="border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900 h-44 overflow-y-auto p-2.5 custom-scrollbar">
                                    <div x-show="loadingEmp" class="text-center py-8 text-xs text-slate-400 animate-pulse">
                                        Memuat data pegawai...
                                    </div>
                                    <div x-show="!loadingEmp && empList.length === 0" class="text-center py-8 text-xs text-slate-400 italic">
                                        Tidak ada pegawai di unit ini.
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <template x-for="emp in empList" :key="emp.id">
                                            <label x-show="empSearch === '' || emp.name.toLowerCase().includes(empSearch.toLowerCase())"
                                                class="flex items-center gap-2.5 p-2 hover:bg-slate-100 dark:hover:bg-slate-800/80 rounded-xl cursor-pointer transition-all border border-transparent hover:border-slate-200/50 dark:hover:border-slate-850">
                                                <input type="checkbox" name="emp_ids[]" :value="emp.id" x-model="selectedEmps"
                                                    class="w-4.5 h-4.5 text-indigo-650 border-slate-350 rounded focus:ring-indigo-500 dark:focus:ring-offset-slate-900 cursor-pointer">
                                                <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold truncate" x-text="emp.name"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-5 border-t border-slate-100 dark:border-slate-850 flex items-center justify-end gap-2.5 bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button"
                                class="h-9 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-950 hover:bg-slate-50 dark:hover:bg-slate-900 font-bold rounded-lg cursor-pointer transition-colors shadow-2xs"
                                @click="showCreateModal = false">
                                Batal
                            </button>
                            <button type="submit"
                                class="h-9 px-4 bg-indigo-650 text-white font-bold rounded-lg shadow-sm hover:bg-indigo-700 transition-colors cursor-pointer">
                                Lanjutkan ke Grid
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
        </div>

    </div>
</x-admin-layout>
