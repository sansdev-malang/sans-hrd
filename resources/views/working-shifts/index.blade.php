<x-admin-layout>
    @php
        $activeShifts = isset($activeShifts) ? $activeShifts : (isset($shifts) ? $shifts->filter(fn($s) => $s->is_active) : collect());
        $inactiveShifts = isset($inactiveShifts) ? $inactiveShifts : (isset($shifts) ? $shifts->filter(fn($s) => !$s->is_active) : collect());
        $schoolUnits = isset($schoolUnits) ? $schoolUnits : \App\Models\SchoolUnit::where('is_active', true)->orderBy('name')->get();
    @endphp

    <div class="p-3.5 sm:p-5 lg:p-6 space-y-3.5 sm:space-y-5 lg:space-y-6 text-left" x-data="{ 
        activeTab: '{{ request('tab', request('page_inactive') ? 'inactive' : 'active') }}',
        showAddModal: {{ isset($errors) && $errors->any() && !old('_method') ? 'true' : 'false' }},
        showEditModal: {{ isset($errors) && $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
        showDeleteModal: false,
        addSchoolUnitId: '{{ old('school_unit_id') && !old('_method') ? old('school_unit_id') : (request('unit_id') && request('unit_id') !== 'global' ? request('unit_id') : '') }}',
        addName: '{{ old('name') && !old('_method') ? old('name') : '' }}',
        addCode: '{{ old('code') && !old('_method') ? old('code') : '' }}',
        addShortCode: '{{ old('short_code') && !old('_method') ? old('short_code') : '' }}',
        addIsShift: {{ old('is_shift') && !old('_method') ? 'true' : 'false' }},
        addIsActive: {{ old('is_active', '1') && !old('_method') ? 'true' : 'false' }},
        addDescription: '{{ old('description') && !old('_method') ? old('description') : '' }}',
        isCodeManuallyEdited: false,
        editId: {{ old('edit_id') ? old('edit_id') : 'null' }},
        editSchoolUnitId: '{{ old('school_unit_id') && old('_method') === 'PUT' ? old('school_unit_id') : '' }}',
        editName: '{{ old('name') && old('_method') === 'PUT' ? old('name') : '' }}',
        editCode: '{{ old('code') && old('_method') === 'PUT' ? old('code') : '' }}',
        editShortCode: '{{ old('short_code') && old('_method') === 'PUT' ? old('short_code') : '' }}',
        editIsShift: {{ old('is_shift') && old('_method') === 'PUT' ? 'true' : 'false' }},
        editIsActive: {{ old('is_active') && old('_method') === 'PUT' ? 'true' : 'false' }},
        editDescription: '{{ old('description') && old('_method') === 'PUT' ? old('description') : '' }}',
        deleteShift: null,
        showToggleModal: false,
        toggleShift: null,
        toggleIsActiveTarget: false,
        confirmToggle(shift, targetIsActive) {
            this.toggleShift = shift;
            this.toggleIsActiveTarget = targetIsActive;
            this.showToggleModal = true;
        },
        confirmDelete(shift) {
            this.deleteShift = shift;
            this.showDeleteModal = true;
        },
        applySeninToAll() {
            let senin = this.days[1];
            if (senin && senin.start_time && senin.end_time) {
                for (let i = 2; i <= 5; i++) {
                    if (!this.days[i].is_off) {
                        this.days[i].start_time = senin.start_time;
                        this.days[i].end_time = senin.end_time;
                    }
                }
            }
        },
        days: [
            { name: 'Minggu', start_time: '', end_time: '', is_off: true },
            { name: 'Senin', start_time: '', end_time: '', is_off: false },
            { name: 'Selasa', start_time: '', end_time: '', is_off: false },
            { name: 'Rabu', start_time: '', end_time: '', is_off: false },
            { name: 'Kamis', start_time: '', end_time: '', is_off: false },
            { name: 'Jumat', start_time: '', end_time: '', is_off: false },
            { name: 'Sabtu', start_time: '', end_time: '', is_off: false }
        ],
        init() {
            this.$watch('addName', value => {
                this.addCode = value.toLowerCase()
                                    .replace(/[^a-z0-9_]/g, '_')
                                    .replace(/_+/g, '_')
                                    .replace(/^_+|_+$/g, '');
            });

            @if(isset($errors) && $errors->any() && old('days'))
                let oldDays = @json(old('days'));
                this.days = Object.keys(oldDays).map(key => {
                    let d = oldDays[key];
                    return {
                        name: d.name || '',
                        start_time: d.start_time || '',
                        end_time: d.end_time || '',
                        is_off: d.is_off == '1' || d.is_off === true
                    };
                });
            @endif
        },
        openEdit(shift) {
            this.editId = shift.id;
            this.editSchoolUnitId = shift.school_unit_id ? String(shift.school_unit_id) : '';
            this.editName = shift.name;
            this.editCode = shift.code;
            this.editShortCode = shift.short_code || '';
            this.editIsShift = !!shift.is_shift;
            this.editIsActive = !!shift.is_active;
            this.editDescription = shift.description || '';
            
            this.days = [
                { name: 'Minggu', start_time: '', end_time: '', is_off: true },
                { name: 'Senin', start_time: '', end_time: '', is_off: true },
                { name: 'Selasa', start_time: '', end_time: '', is_off: true },
                { name: 'Rabu', start_time: '', end_time: '', is_off: true },
                { name: 'Kamis', start_time: '', end_time: '', is_off: true },
                { name: 'Jumat', start_time: '', end_time: '', is_off: true },
                { name: 'Sabtu', start_time: '', end_time: '', is_off: true }
            ];
            
            shift.details.forEach(d => {
                let idx = d.day_of_week;
                if (this.days[idx]) {
                    this.days[idx].start_time = d.start_time ? d.start_time.substring(0, 5) : '';
                    this.days[idx].end_time = d.end_time ? d.end_time.substring(0, 5) : '';
                    this.days[idx].is_off = !!d.is_off;
                }
            });
            
            this.showEditModal = true;
        },
        resetAdd() {
            this.addSchoolUnitId = '{{ request('unit_id') && request('unit_id') !== 'global' ? request('unit_id') : '' }}';
            this.addName = '';
            this.addCode = '';
            this.addShortCode = '';
            this.addIsShift = false;
            this.addIsActive = true;
            this.addDescription = '';
            this.isCodeManuallyEdited = false;
            this.days = [
                { name: 'Minggu', start_time: '', end_time: '', is_off: true },
                { name: 'Senin', start_time: '', end_time: '', is_off: false },
                { name: 'Selasa', start_time: '', end_time: '', is_off: false },
                { name: 'Rabu', start_time: '', end_time: '', is_off: false },
                { name: 'Kamis', start_time: '', end_time: '', is_off: false },
                { name: 'Jumat', start_time: '', end_time: '', is_off: false },
                { name: 'Sabtu', start_time: '', end_time: '', is_off: false }
            ];
            this.showAddModal = true;
        }
    }">

        <!-- HEADER (MATCHING SANS-PAUD EXACT STANDARDS) -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 sm:gap-3 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">Pengaturan Shift & Jam Kerja</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Atur template shift kerja dan jam kehadiran per unit sekolah maupun Global.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
                <a href="{{ route('working-shifts.sync') }}" data-no-loader="true" onclick="this.style.pointerEvents = 'none'; let icon = this.querySelector('i, svg'); if(icon) icon.classList.add('animate-spin');" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] sm:text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-100 cursor-pointer w-full sm:w-auto">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Sync ke Unit</span>
                </a>
                <button @click="resetAdd()" class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 bg-slate-900 dark:bg-slate-50 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-[11px] sm:text-xs font-semibold rounded-lg shadow-sm transition-all duration-100 cursor-pointer w-full sm:w-auto">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Shift</span>
                </button>
            </div>
        </section>

        <!-- TAB SWITCHER -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 gap-1.5">
            <button type="button" 
                @click="activeTab = 'active'" 
                :class="activeTab === 'active' 
                    ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' 
                    : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900/50'" 
                class="px-3.5 py-2 text-xs font-bold rounded-t-lg border-b-2 flex items-center gap-2 transition-all cursor-pointer">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Shift Aktif</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full bg-slate-200 dark:bg-slate-800 text-[10px] font-mono">{{ method_exists($activeShifts, 'total') ? $activeShifts->total() : $activeShifts->count() }}</span>
            </button>

            <button type="button" 
                @click="activeTab = 'inactive'" 
                :class="activeTab === 'inactive' 
                    ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/30' 
                    : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-900/50'" 
                class="px-3.5 py-2 text-xs font-bold rounded-t-lg border-b-2 flex items-center gap-2 transition-all cursor-pointer">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Riwayat / Arsip</span>
                <span class="ml-1 px-1.5 py-0.2 rounded-full bg-slate-200 dark:bg-slate-800 text-[10px] font-mono">{{ method_exists($inactiveShifts, 'total') ? $inactiveShifts->total() : $inactiveShifts->count() }}</span>
            </button>
        </div>

        <!-- FILTERS & SEARCH (MATCHING SANS-PAUD EXACT TOOLBAR) -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-3 sm:p-4 shadow-sm w-full text-left">
            <form id="filter-shift-form" method="GET" action="{{ route('working-shifts.index') }}" class="flex flex-col md:flex-row gap-3 sm:gap-4 items-stretch md:items-center justify-between">
                <input type="hidden" name="tab" :value="activeTab">

                <!-- Search Box Welded with Cari Button (Exact sans-paud style) -->
                <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center w-full md:max-w-md search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                    <input type="text" name="search" x-model="searchVal" placeholder="Cari berdasarkan nama atau kode shift..."
                        style="border: none !important; outline: none !important; box-shadow: none !important;"
                        class="w-full h-9 px-3 text-xs bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-0">
                    
                    <!-- Clear Button (x) -->
                    <button type="button" x-show="searchVal.trim() !== ''" @click="searchVal = ''; $el.closest('.search-container').querySelector('input').focus();" class="h-9 px-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors cursor-pointer bg-transparent border-0 flex items-center justify-center" title="Bersihkan pencarian">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>

                    <button type="submit" 
                        :class="searchVal.trim() !== '' ? 'bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white' : 'bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300'"
                        class="h-9 px-4 font-bold text-xs transition-all duration-150 cursor-pointer whitespace-nowrap flex items-center justify-center border-l border-slate-200 dark:border-slate-800">
                        Cari
                    </button>
                </div>

                <!-- Filters & Per Page -->
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 justify-end">
                    <!-- Filter Unit -->
                    <select name="unit_id" onchange="this.form.submit()"
                        class="h-9 px-3 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="global" {{ request('unit_id') === 'global' ? 'selected' : '' }}>Global</option>
                        @foreach($schoolUnits as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>

                    <!-- Reset Filter Button -->
                    @if(request()->filled('search') || (request()->filled('unit_id') && request('unit_id') !== '') || (request()->filled('per_page') && request('per_page') != 10))
                        <a href="{{ route('working-shifts.index', array_filter(['tab' => request('tab')])) }}" class="h-9 px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center transition-all cursor-pointer" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif

                    <!-- Per Page Dropdown -->
                    <select name="per_page" onchange="this.form.submit()"
                        class="h-9 px-3 flex-1 sm:flex-initial sm:w-28 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10 baris</option>
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                </div>
            </form>
        </section>

        <!-- ========================================================================= -->
        <!-- TAB 1: SHIFT KERJA AKTIF -->
        <!-- ========================================================================= -->
        <div x-cloak x-show="activeTab === 'active'" x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden w-full text-left">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="px-3 py-2.5 w-10 text-center">No</th>
                                <th class="px-3.5 py-2.5 min-w-[180px]">Nama Shift</th>
                                <th class="px-3 py-2.5 w-24">Kode</th>
                                <th class="px-3 py-2.5 w-20">Unit</th>
                                <th class="px-3 py-2.5 w-20">Tipe</th>
                                <th class="px-3.5 py-2.5 min-w-[280px]">Jadwal Hari & Jam Kerja</th>
                                <th class="px-3 py-2.5 w-20 text-center">Status</th>
                                <th class="px-3.5 py-2.5 w-36 text-right pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($activeShifts as $shift)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-3 py-2.5 text-center font-semibold text-slate-400 dark:text-slate-500 align-top">
                                        {{ method_exists($activeShifts, 'firstItem') && $activeShifts->firstItem() ? $activeShifts->firstItem() + $loop->index : $loop->iteration }}
                                    </td>
                                    <td class="px-3.5 py-2.5 align-top">
                                        <div class="space-y-0.5">
                                            <div class="font-semibold text-slate-900 dark:text-slate-100 text-xs">
                                                {{ $shift->name }}
                                            </div>
                                            @if($shift->description)
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-snug line-clamp-2 max-w-sm">
                                                    {{ $shift->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 text-xs">
                                                {{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 dark:text-slate-500 font-mono">
                                                {{ $shift->code }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                        @if($shift->schoolUnit)
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400 text-xs">{{ $shift->schoolUnit->name }}</span>
                                        @else
                                            <span class="font-medium text-slate-500 dark:text-slate-400 text-xs">Global</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                        <span class="text-slate-600 dark:text-slate-400 font-medium text-xs">
                                            {{ $shift->is_shift ? 'Shift' : 'Non-Shift' }}
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-2.5 align-top">
                                        @php
                                            $daysName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                            $groupedSchedules = [];
                                            foreach($shift->details->sortBy('day_of_week') as $detail) {
                                                $dayName = $daysName[$detail->day_of_week] ?? '';
                                                $timeKey = $detail->is_off ? 'Libur' : substr($detail->start_time, 0, 5) . ' - ' . substr($detail->end_time, 0, 5);
                                                $groupedSchedules[$timeKey][] = $dayName;
                                            }
                                        @endphp
                                        <div class="space-y-0.5">
                                            @foreach($groupedSchedules as $timeKey => $days)
                                                <div class="flex items-center gap-1.5 text-[11px] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400 font-medium">
                                                        {{ implode(', ', $days) }} :
                                                    </span>
                                                    @if($timeKey === 'Libur')
                                                        <span class="text-rose-600 dark:text-rose-400 font-semibold">
                                                            Libur
                                                        </span>
                                                    @else
                                                        <span class="font-mono font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                                            {{ $timeKey }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-2.5 align-top text-right pr-4 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" @click="confirmToggle({{ json_encode($shift) }}, false)" class="h-8 px-2.5 bg-white dark:bg-slate-900 hover:bg-amber-50 dark:hover:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-900/50 shadow-2xs transition-colors cursor-pointer flex items-center gap-1.5" title="Arsipkan / Nonaktifkan shift ini">
                                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                                <span>Arsip</span>
                                            </button>
                                            <button @click="openEdit({{ json_encode($shift) }})" class="h-8 px-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-2xs transition-colors cursor-pointer flex items-center gap-1.5" title="Edit Shift">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" @click="confirmDelete({{ json_encode($shift) }})" class="h-8 w-8 bg-white dark:bg-slate-900 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 hover:border-rose-200 dark:hover:border-rose-900/50 shadow-2xs transition-colors cursor-pointer flex items-center justify-center" title="Hapus Shift">
                                                <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center">
                                        <i data-lucide="clock" class="w-7 h-7 mx-auto text-slate-300 dark:text-slate-600 mb-1.5"></i>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada template shift kerja yang sesuai filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION FOOTER TAB 1 -->
                @if(method_exists($activeShifts, 'total') && $activeShifts->total() > 0)
                    <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-900/30 flex flex-col sm:flex-row justify-between items-center gap-2.5 text-xs text-slate-500 dark:text-slate-400">
                        <div>
                            Menampilkan
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $activeShifts->firstItem() ?? 1 }}</span>
                            -
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $activeShifts->lastItem() ?? $activeShifts->total() }}</span>
                            dari
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $activeShifts->total() }}</span>
                            shift aktif
                        </div>
                        @if($activeShifts->hasPages())
                            <div class="flex items-center gap-1 font-semibold">
                                @if ($activeShifts->onFirstPage())
                                    <span class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20 text-xs">Sebelumnya</span>
                                @else
                                    <a href="{{ $activeShifts->previousPageUrl() }}" class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 text-xs">Sebelumnya</a>
                                @endif

                                <span class="px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $activeShifts->currentPage() }} / {{ $activeShifts->lastPage() }}
                                </span>

                                @if ($activeShifts->hasMorePages())
                                    <a href="{{ $activeShifts->nextPageUrl() }}" class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 text-xs">Berikutnya</a>
                                @else
                                    <span class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20 text-xs">Berikutnya</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: SHIFT KERJA NONAKTIF (RIWAYAT / ARSIP LAMA) -->
        <!-- ========================================================================= -->
        <div x-cloak x-show="activeTab === 'inactive'" x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <!-- Accordion Info Tab 2 -->
            <div class="mb-3.5 rounded-xl border border-amber-200/60 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20 overflow-hidden text-xs" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full px-3.5 py-2.5 flex items-center justify-between text-left font-bold text-amber-900 dark:text-amber-300 hover:bg-amber-100/50 dark:hover:bg-amber-950/30 transition-colors cursor-pointer border-0 bg-transparent">
                    <div class="flex items-center gap-2">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                        <span class="text-xs">Informasi Shift Kerja Riwayat / Arsip</span>
                    </div>
                    <div class="flex items-center gap-1 text-[11px] text-amber-700 dark:text-amber-400 font-semibold">
                        <span x-text="open ? 'Tutup Info' : 'Lihat Aturan'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transform transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </button>
                <div x-cloak x-show="open" class="px-3.5 pb-3 pt-1 text-slate-700 dark:text-slate-300 border-t border-amber-200/40 dark:border-amber-900/30 space-y-1.5 text-[11px] leading-relaxed">
                    <p>Shift kerja pada tab ini adalah <strong>kebijakan lama yang dinonaktifkan/diarsipkan</strong>. Pengaturannya dipertahankan agar riwayat absensi dan bonus lampau tetap konsisten. Anda dapat mengaktifkannya kembali kapan saja.</p>
                </div>
            </div>

            <!-- TABEL SHIFT NONAKTIF -->
            <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden w-full text-left">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="px-3 py-2.5 w-10 text-center">No</th>
                                <th class="px-3.5 py-2.5 min-w-[180px]">Nama Shift</th>
                                <th class="px-3 py-2.5 w-24">Kode</th>
                                <th class="px-3 py-2.5 w-20">Unit</th>
                                <th class="px-3.5 py-2.5 w-20">Tipe</th>
                                <th class="px-3.5 py-2.5 min-w-[280px]">Jadwal Hari & Jam Kerja</th>
                                <th class="px-3 py-2.5 w-20 text-center">Status</th>
                                <th class="px-3.5 py-2.5 w-36 text-right pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($inactiveShifts as $shift)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors opacity-85 hover:opacity-100">
                                    <td class="px-3 py-2.5 text-center font-semibold text-slate-400 dark:text-slate-500 align-top">
                                        {{ method_exists($inactiveShifts, 'firstItem') && $inactiveShifts->firstItem() ? $inactiveShifts->firstItem() + $loop->index : $loop->iteration }}
                                    </td>
                                    <td class="px-3.5 py-2.5 align-top">
                                        <div class="space-y-0.5">
                                            <div class="font-semibold text-slate-700 dark:text-slate-300 text-xs">
                                                {{ $shift->name }}
                                            </div>
                                            @if($shift->description)
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-snug line-clamp-2 max-w-sm">
                                                    {{ $shift->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-mono font-bold text-slate-600 dark:text-slate-400 text-xs">
                                                {{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}
                                            </span>
                                            <span class="text-[9px] text-slate-400 dark:text-slate-500 font-mono">
                                                {{ $shift->code }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                        @if($shift->schoolUnit)
                                            <span class="font-semibold text-indigo-600/80 dark:text-indigo-400/80 text-xs">{{ $shift->schoolUnit->name }}</span>
                                        @else
                                            <span class="font-medium text-slate-500 dark:text-slate-400 text-xs">Global</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 align-top whitespace-nowrap">
                                        <span class="text-slate-500 dark:text-slate-400 font-medium text-xs">
                                            {{ $shift->is_shift ? 'Shift' : 'Non-Shift' }}
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-2.5 align-top">
                                        @php
                                            $daysName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                            $groupedSchedules = [];
                                            foreach($shift->details->sortBy('day_of_week') as $detail) {
                                                $dayName = $daysName[$detail->day_of_week] ?? '';
                                                $timeKey = $detail->is_off ? 'Libur' : substr($detail->start_time, 0, 5) . ' - ' . substr($detail->end_time, 0, 5);
                                                $groupedSchedules[$timeKey][] = $dayName;
                                            }
                                        @endphp
                                        <div class="space-y-0.5">
                                            @foreach($groupedSchedules as $timeKey => $days)
                                                <div class="flex items-center gap-1.5 text-[11px] leading-tight">
                                                    <span class="text-slate-500 dark:text-slate-400 font-semibold">
                                                        {{ implode(', ', $days) }} :
                                                    </span>
                                                    @if($timeKey === 'Libur')
                                                        <span class="text-rose-600 dark:text-rose-400 font-semibold">
                                                            Libur
                                                        </span>
                                                    @else
                                                        <span class="font-mono font-semibold text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                            {{ $timeKey }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 align-top text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Arsip
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-2.5 align-top text-right pr-4 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" @click="confirmToggle({{ json_encode($shift) }}, true)" class="h-8 px-2.5 bg-white dark:bg-slate-900 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 hover:border-emerald-200 dark:border-emerald-900/50 shadow-2xs transition-colors cursor-pointer flex items-center gap-1.5" title="Aktifkan kembali shift ini">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                <span>Aktifkan</span>
                                            </button>
                                            <button @click="openEdit({{ json_encode($shift) }})" class="h-8 px-2.5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-2xs transition-colors cursor-pointer flex items-center gap-1.5" title="Edit Shift">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" @click="confirmDelete({{ json_encode($shift) }})" class="h-8 w-8 bg-white dark:bg-slate-900 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 hover:border-rose-200 dark:hover:border-rose-900/50 shadow-2xs transition-colors cursor-pointer flex items-center justify-center" title="Hapus Shift">
                                                <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center">
                                        <i data-lucide="archive" class="w-7 h-7 mx-auto text-slate-300 dark:text-slate-600 mb-1.5"></i>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada template shift kerja yang dinonaktifkan / diarsipkan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION FOOTER TAB 2 -->
                @if(method_exists($inactiveShifts, 'total') && $inactiveShifts->total() > 0)
                    <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-900/30 flex flex-col sm:flex-row justify-between items-center gap-2.5 text-xs text-slate-500 dark:text-slate-400">
                        <div>
                            Menampilkan
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $inactiveShifts->firstItem() ?? 1 }}</span>
                            -
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $inactiveShifts->lastItem() ?? $inactiveShifts->total() }}</span>
                            dari
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $inactiveShifts->total() }}</span>
                            shift nonaktif
                        </div>
                        @if($inactiveShifts->hasPages())
                            <div class="flex items-center gap-1 font-semibold">
                                @if ($inactiveShifts->onFirstPage())
                                    <span class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20 text-xs">Sebelumnya</span>
                                @else
                                    <a href="{{ $inactiveShifts->previousPageUrl() }}" class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 text-xs">Sebelumnya</a>
                                @endif

                                <span class="px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $inactiveShifts->currentPage() }} / {{ $inactiveShifts->lastPage() }}
                                </span>

                                @if ($inactiveShifts->hasMorePages())
                                    <a href="{{ $inactiveShifts->nextPageUrl() }}" class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900 text-xs">Berikutnya</a>
                                @else
                                    <span class="h-7 px-2.5 rounded-md border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20 text-xs">Berikutnya</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        </div>

        <!-- ADD MODAL -->
        <template x-teleport="body">
            <div x-cloak x-show="showAddModal" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showAddModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-slate-900/60 backdrop-blur-xs text-left" style="z-index: 9999; display: none;">
                 <div @click.outside="showAddModal = false"
                     x-transition:enter="transition ease-out duration-150 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden text-left text-xs">
                    <!-- Header -->
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 shrink-0">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Tambah Template Shift Baru</span>
                        </h3>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('working-shifts.store') }}" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <div class="flex-1 overflow-y-auto px-5 py-4 text-left custom-scrollbar">
                            
                            @if(isset($errors) && $errors->any() && !old('_method'))
                                <div class="mb-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-lg p-3 text-xs text-rose-800 dark:text-rose-400 text-left flex gap-2.5 items-start shrink-0">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                    <div>
                                        <h5 class="font-bold mb-0.5">Gagal menyimpan data:</h5>
                                        <ul class="list-disc pl-3.5 space-y-0.5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <!-- 2 COLUMNS: INFORMASI SHIFT & JADWAL HARI -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                                
                                <!-- LEFT COLUMN: INFORMASI SHIFT (5 cols) -->
                                <div class="md:col-span-5 space-y-3">
                                    <div class="flex items-center gap-1.5 pb-1 border-b border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200 font-bold text-[11px] uppercase tracking-wider">
                                        <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500"></i>
                                        <span>Informasi Shift</span>
                                    </div>

                                    <!-- Nama Shift -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Shift <span class="text-rose-500">*</span></label>
                                        <input type="text" name="name" required x-model="addName" placeholder="Contoh: Reguler SMP (06:30 - 15:30)" class="w-full text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                    </div>

                                    <!-- Cakupan Unit & Kode Singkat -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Cakupan Unit</label>
                                            <select name="school_unit_id" x-model="addSchoolUnitId" class="w-full text-xs h-8 pl-2 pr-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                                <option value="">Global</option>
                                                @foreach($schoolUnits as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Singkat</label>
                                            <input type="text" name="short_code" x-model="addShortCode" maxlength="5" placeholder="Cth: S1, P, M" class="w-full text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono uppercase">
                                        </div>
                                    </div>

                                    <!-- Kode Unik (Auto) -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Sistem (Auto)</label>
                                        <input type="text" name="code" required x-model="addCode" readonly placeholder="Otomatis dari nama..." 
                                            class="w-full text-xs h-8 px-2.5 bg-slate-100 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 dark:text-slate-400 focus:outline-none font-mono cursor-not-allowed select-none">
                                    </div>

                                    <!-- Checkboxes -->
                                    <div class="flex items-center gap-4 py-0.5">
                                        <label class="flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="is_shift" value="1" x-model="addIsShift" class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                                            <span class="ml-1.5 font-medium text-slate-700 dark:text-slate-300 text-xs">Shift Bergulir</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="is_active" value="1" x-model="addIsActive" class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                                            <span class="ml-1.5 font-medium text-emerald-700 dark:text-emerald-400 text-xs">Status Aktif</span>
                                        </label>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi (Opsional)</label>
                                        <textarea name="description" rows="2" x-model="addDescription" placeholder="Keterangan singkat mengenai shift ini..." class="w-full text-xs p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN: KONFIGURASI JADWAL (7 cols) -->
                                <div class="md:col-span-7 space-y-2">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                                        <span class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500"></i>
                                            <span>Konfigurasi Hari & Jam Kerja</span>
                                        </span>
                                        <button type="button" @click="applySeninToAll()" class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-semibold cursor-pointer transition-colors" title="Salin jam kerja Senin ke Selasa s/d Jumat">
                                            + Samakan Senin s/d Jumat
                                        </button>
                                    </div>

                                    <div class="space-y-1 bg-slate-50/70 dark:bg-slate-950/40 p-2 sm:p-2.5 rounded-lg border border-slate-100 dark:border-slate-800/80">
                                        <div class="grid grid-cols-12 gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1 pb-0.5">
                                            <div class="col-span-3">Hari</div>
                                            <div class="col-span-3">Status</div>
                                            <div class="col-span-3 text-center">Masuk</div>
                                            <div class="col-span-3 text-center">Pulang</div>
                                        </div>
                                        <template x-for="(day, index) in days" :key="index">
                                            <div class="grid grid-cols-12 items-center gap-1.5 py-1 px-1 rounded-md transition-colors"
                                                :class="day.is_off ? 'bg-rose-50/30 dark:bg-rose-950/10' : 'hover:bg-white dark:hover:bg-slate-900/60'">
                                                
                                                <!-- Day Name -->
                                                <div class="col-span-3 font-semibold text-slate-800 dark:text-slate-200 text-xs truncate" x-text="day.name"></div>
                                                
                                                <!-- Off status toggle -->
                                                <div class="col-span-3 flex items-center">
                                                    <input type="hidden" :name="`days[${index}][is_off]`" value="0">
                                                    <input type="checkbox" :name="`days[${index}][is_off]`" value="1" x-model="day.is_off" class="rounded border-slate-300 dark:border-slate-700 text-rose-500 w-3.5 h-3.5 focus:ring-0 cursor-pointer">
                                                    <span class="ml-1 text-rose-600 dark:text-rose-400 font-semibold uppercase text-[10px] cursor-pointer" @click="day.is_off = !day.is_off">Libur</span>
                                                </div>
                                                
                                                <!-- Jam Masuk -->
                                                <div class="col-span-3">
                                                    <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" :disabled="day.is_off" class="w-full text-xs h-7 px-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded text-center disabled:opacity-30 disabled:bg-slate-100 dark:disabled:bg-slate-950 font-mono focus:outline-none focus:border-indigo-500">
                                                </div>

                                                <!-- Jam Pulang -->
                                                <div class="col-span-3">
                                                    <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" :disabled="day.is_off" class="w-full text-xs h-7 px-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded text-center disabled:opacity-30 disabled:bg-slate-100 dark:disabled:bg-slate-950 font-mono focus:outline-none focus:border-indigo-500">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex gap-2 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button" @click="showAddModal = false" class="h-8 px-3.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium rounded-lg cursor-pointer text-xs transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="h-8 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-medium rounded-lg shadow-sm text-xs cursor-pointer transition-colors">
                                Simpan Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT MODAL -->
        <template x-teleport="body">
            <div x-cloak x-show="showEditModal" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showEditModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-slate-900/60 backdrop-blur-xs text-left" style="z-index: 9999; display: none;">
                <div @click.outside="showEditModal = false"
                     x-transition:enter="transition ease-out duration-150 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden text-left text-xs">
                    <!-- Header -->
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 shrink-0">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i data-lucide="edit" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Edit Template Shift</span>
                        </h3>
                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" :action="`{{ url('working-shifts') }}/${editId}`" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_id" :value="editId">
                        
                        <div class="flex-1 overflow-y-auto px-5 py-4 text-left custom-scrollbar">
                            
                            @if(isset($errors) && $errors->any() && old('_method') === 'PUT')
                                <div class="mb-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-lg p-3 text-xs text-rose-800 dark:text-rose-400 text-left flex gap-2.5 items-start shrink-0">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0 mt-0.5"></i>
                                    <div>
                                        <h5 class="font-bold mb-0.5">Gagal menyimpan data:</h5>
                                        <ul class="list-disc pl-3.5 space-y-0.5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <!-- 2 COLUMNS: INFORMASI SHIFT & JADWAL HARI -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                                
                                <!-- LEFT COLUMN: INFORMASI SHIFT (5 cols) -->
                                <div class="md:col-span-5 space-y-3">
                                    <div class="flex items-center gap-1.5 pb-1 border-b border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200 font-bold text-[11px] uppercase tracking-wider">
                                        <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500"></i>
                                        <span>Informasi Shift</span>
                                    </div>

                                    <!-- Nama Shift -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Shift <span class="text-rose-500">*</span></label>
                                        <input type="text" name="name" required x-model="editName" class="w-full text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                    </div>

                                    <!-- Cakupan Unit & Kode Singkat -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Cakupan Unit</label>
                                            <select name="school_unit_id" x-model="editSchoolUnitId" class="w-full text-xs h-8 pl-2 pr-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                                <option value="">Global</option>
                                                @foreach($schoolUnits as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Singkat</label>
                                            <input type="text" name="short_code" maxlength="5" x-model="editShortCode" placeholder="Cth: S1, P, M" class="w-full text-xs h-8 px-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono uppercase">
                                        </div>
                                    </div>

                                    <!-- Kode Unik (Readonly) -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Sistem</label>
                                        <input type="text" name="code" required x-model="editCode" disabled class="w-full text-xs h-8 px-2.5 bg-slate-100 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 dark:text-slate-400 focus:outline-none font-mono cursor-not-allowed">
                                    </div>

                                    <!-- Checkboxes -->
                                    <div class="flex items-center gap-4 py-0.5">
                                        <label class="flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="is_shift" value="1" x-model="editIsShift" class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                                            <span class="ml-1.5 font-medium text-slate-700 dark:text-slate-300 text-xs">Shift Bergulir</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer select-none">
                                            <input type="checkbox" name="is_active" value="1" x-model="editIsActive" class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                                            <span class="ml-1.5 font-medium text-emerald-700 dark:text-emerald-400 text-xs">Status Aktif</span>
                                        </label>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div>
                                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Deskripsi (Opsional)</label>
                                        <textarea name="description" rows="2" x-model="editDescription" placeholder="Keterangan singkat mengenai shift ini..." class="w-full text-xs p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 resize-none"></textarea>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN: KONFIGURASI JADWAL (7 cols) -->
                                <div class="md:col-span-7 space-y-2">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                                        <span class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500"></i>
                                            <span>Konfigurasi Hari & Jam Kerja</span>
                                        </span>
                                        <button type="button" @click="applySeninToAll()" class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-semibold cursor-pointer transition-colors" title="Salin jam kerja Senin ke Selasa s/d Jumat">
                                            + Samakan Senin s/d Jumat
                                        </button>
                                    </div>

                                    <div class="space-y-1 bg-slate-50/70 dark:bg-slate-950/40 p-2 sm:p-2.5 rounded-lg border border-slate-100 dark:border-slate-800/80">
                                        <div class="grid grid-cols-12 gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider px-1 pb-0.5">
                                            <div class="col-span-3">Hari</div>
                                            <div class="col-span-3">Status</div>
                                            <div class="col-span-3 text-center">Masuk</div>
                                            <div class="col-span-3 text-center">Pulang</div>
                                        </div>
                                        <template x-for="(day, index) in days" :key="index">
                                            <div class="grid grid-cols-12 items-center gap-1.5 py-1 px-1 rounded-md transition-colors"
                                                :class="day.is_off ? 'bg-rose-50/30 dark:bg-rose-950/10' : 'hover:bg-white dark:hover:bg-slate-900/60'">
                                                
                                                <!-- Day Name -->
                                                <div class="col-span-3 font-semibold text-slate-800 dark:text-slate-200 text-xs truncate" x-text="day.name"></div>
                                                
                                                <!-- Off status toggle -->
                                                <div class="col-span-3 flex items-center">
                                                    <input type="hidden" :name="`days[${index}][is_off]`" value="0">
                                                    <input type="checkbox" :name="`days[${index}][is_off]`" value="1" x-model="day.is_off" class="rounded border-slate-300 dark:border-slate-700 text-rose-500 w-3.5 h-3.5 focus:ring-0 cursor-pointer">
                                                    <span class="ml-1 text-rose-600 dark:text-rose-400 font-semibold uppercase text-[10px] cursor-pointer" @click="day.is_off = !day.is_off">Libur</span>
                                                </div>
                                                
                                                <!-- Jam Masuk -->
                                                <div class="col-span-3">
                                                    <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" :disabled="day.is_off" class="w-full text-xs h-7 px-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded text-center disabled:opacity-30 disabled:bg-slate-100 dark:disabled:bg-slate-950 font-mono focus:outline-none focus:border-indigo-500">
                                                </div>

                                                <!-- Jam Pulang -->
                                                <div class="col-span-3">
                                                    <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" :disabled="day.is_off" class="w-full text-xs h-7 px-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded text-center disabled:opacity-30 disabled:bg-slate-100 dark:disabled:bg-slate-950 font-mono focus:outline-none focus:border-indigo-500">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 flex gap-2 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button" @click="showEditModal = false" class="h-8 px-3.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-medium rounded-lg cursor-pointer text-xs transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="h-8 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 font-medium rounded-lg shadow-sm text-xs cursor-pointer transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- TOGGLE ACTIVE/INACTIVE CONFIRMATION MODAL -->
        <template x-teleport="body">
            <div x-cloak x-show="showToggleModal" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showToggleModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-slate-900/60 backdrop-blur-xs text-left" style="z-index: 9999; display: none;">
                <div @click.outside="showToggleModal = false"
                     x-transition:enter="transition ease-out duration-150 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-sm w-full overflow-hidden text-xs">
                    <div class="p-5 text-center">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" :class="toggleIsActiveTarget ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400'">
                            <template x-if="!toggleIsActiveTarget">
                                <i data-lucide="archive" class="w-6 h-6"></i>
                            </template>
                            <template x-if="toggleIsActiveTarget">
                                <i data-lucide="rotate-ccw" class="w-6 h-6"></i>
                            </template>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 mb-1.5" x-text="toggleIsActiveTarget ? 'Aktifkan Kembali Shift?' : 'Arsipkan / Nonaktifkan Shift?'"></h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-5 leading-relaxed">
                            <span x-show="!toggleIsActiveTarget">
                                Shift <strong class="text-slate-700 dark:text-slate-300" x-text="toggleShift ? toggleShift.name : ''"></strong> akan dipindahkan ke tab Arsip dan disembunyikan dari penugasan jadwal baru. Riwayat jadwal & absensi lampau tetap aman.
                            </span>
                            <span x-show="toggleIsActiveTarget">
                                Shift <strong class="text-slate-700 dark:text-slate-300" x-text="toggleShift ? toggleShift.name : ''"></strong> akan diaktifkan kembali dan dapat dipilih di form penugasan jadwal baru.
                            </span>
                        </p>
                        
                        <div class="flex justify-center gap-2">
                            <button type="button" @click="showToggleModal = false" class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold rounded-lg cursor-pointer transition-colors text-xs">
                                Batal
                            </button>
                            <form method="POST" :action="toggleShift ? `{{ url('working-shifts') }}/${toggleShift.id}/toggle-active` : ''" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full text-xs px-3 py-2 text-white font-semibold rounded-lg cursor-pointer transition-colors shadow-xs flex items-center justify-center gap-1.5" :class="toggleIsActiveTarget ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700'">
                                    <span x-text="toggleIsActiveTarget ? 'Ya, Aktifkan' : 'Ya, Arsipkan'"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- DELETE MODAL -->
        <template x-teleport="body">
            <div x-cloak x-show="showDeleteModal" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showDeleteModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-slate-900/60 backdrop-blur-xs text-left" style="z-index: 9999; display: none;">
                <div @click.outside="showDeleteModal = false"
                     x-transition:enter="transition ease-out duration-150 transform"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-sm w-full overflow-hidden text-xs">
                    <div class="p-5 text-center">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="trash-2" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 mb-1.5">Hapus Shift Kerja?</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-5 leading-relaxed">
                            Anda yakin ingin menghapus template shift <strong class="text-slate-700 dark:text-slate-300" x-text="deleteShift ? deleteShift.name : ''"></strong>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        
                        <div class="flex justify-center gap-2">
                            <button type="button" @click="showDeleteModal = false" class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold rounded-lg cursor-pointer transition-colors text-xs">
                                Batal
                            </button>
                            <form id="delete-shift-form" method="POST" :action="deleteShift ? `{{ url('working-shifts') }}/${deleteShift.id}` : ''" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-xs px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg cursor-pointer transition-colors shadow-xs flex items-center justify-center gap-1.5">
                                    <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                    <span>Ya, Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-admin-layout>
