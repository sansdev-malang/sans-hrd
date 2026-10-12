<x-admin-layout>
    <div class="p-3.5 sm:p-5 lg:p-6 space-y-3.5 sm:space-y-5 lg:space-y-6 text-left" x-data="{ 
        showRejectModal: false,
        selectedLeaveId: '',
        selectedLeaveEmployee: '',
        showEmpDetailModal: false,
        selectedEmp: null,
        showEditModal: false,
        editLeave: { id: '', status: '', notes: '', name: '', type: '', school_unit_id: '' },
        availableTypes: [],
        loadingTypes: false,
        async fetchUnitLeaveTypes(unitId) {
            if (!unitId) return;
            this.loadingTypes = true;
            this.availableTypes = [];
            try {
                let res = await fetch(`{{ url('leave-approvals/units') }}/${unitId}/leave-types`);
                let data = await res.json();
                this.availableTypes = data;
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingTypes = false;
            }
        },
        openDetail(data) {
            this.selectedEmp = data;
            this.showEmpDetailModal = true;
            this.$nextTick(() => {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        }
    }">

        @php
            if (!function_exists('getInitials')) {
                function getInitials($name) {
                    if (empty($name)) return '?';
                    $words = explode(' ', $name);
                    if (count($words) >= 2) {
                        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                    }
                    return strtoupper(substr($name, 0, 2));
                }
            }
            $colors = ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#14b8a6', '#f43f5e', '#0ea5e9', '#d946ef'];
        @endphp

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 sm:gap-3 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">Riwayat Izin Pegawai</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium">Daftar riwayat izin, sakit, dan cuti pegawai yang dihimpun dari seluruh unit sekolah.</p>
            </div>
            <div>
                <form action="{{ route('leave-approvals.sync') }}" method="POST" class="inline" data-no-loader="true" onsubmit="this.querySelector('button').style.pointerEvents = 'none'; let icon = this.querySelector('i, svg'); if(icon) icon.classList.add('animate-spin');">
                    @csrf
                    <button type="submit" class="px-3 sm:px-4 py-2 inline-flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-650 dark:text-indigo-400 text-[11px] sm:text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-all cursor-pointer gap-2 shadow-xs">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Sync Ulang Cuti</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- ULTRA-COMPACT KPI SUMMARY STRIP -->
        <section class="flex flex-wrap items-center gap-2 sm:gap-2.5 w-full text-xs">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/40 text-amber-800 dark:text-amber-300 shadow-2xs">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                <span class="font-medium text-[11px] text-slate-600 dark:text-slate-400">Menunggu Approval:</span>
                <span class="font-bold text-xs font-mono text-amber-700 dark:text-amber-300">{{ $pendingCount }}</span>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 text-indigo-800 dark:text-indigo-300 shadow-2xs">
                <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                <span class="font-medium text-[11px] text-slate-600 dark:text-slate-400">Total Diproses:</span>
                <span class="font-bold text-xs font-mono text-indigo-700 dark:text-indigo-300">{{ $processedCount }}</span>
            </div>

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-900/40 text-emerald-800 dark:text-emerald-300 shadow-2xs">
                <i data-lucide="percent" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <span class="font-medium text-[11px] text-slate-600 dark:text-slate-400">Rasio Persetujuan:</span>
                <span class="font-bold text-xs font-mono text-emerald-700 dark:text-emerald-300">{{ $approvalRate }}%</span>
            </div>
        </section>

        <!-- LEAVE MAIN CONTAINER -->
        <div id="leave-table-container" class="space-y-6">
            <!-- FILTERS & SEARCH -->
            <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('leave-approvals.index') }}" id="leave-filter-form" data-no-loader="true" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <!-- Left Side: Search & Filters -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <!-- Search Box -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center flex-1 min-w-[200px] search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari pegawai..."
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

                    <!-- Unit -->
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                        <select name="unit_id" onchange="triggerFilterForm(this)"
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua</option>
                            @foreach($schoolUnits as $su)
                                <option value="{{ $su->id }}" {{ request('unit_id') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Jenis Izin -->
                    @if(isset($leaveTypes) && count($leaveTypes) > 0)
                        <select name="type" onchange="triggerFilterForm(this)"
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Jenis</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt }}" {{ request('type') == $lt ? 'selected' : '' }}>{{ $lt }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Status -->
                    <select name="status" onchange="triggerFilterForm(this)"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>

                    @if(request()->filled('search') || request()->filled('unit_id') || request()->filled('type') || request()->filled('status') || (request()->filled('per_page') && request('per_page') != 50))
                        <a href="{{ route('leave-approvals.index') }}" data-no-loader="true" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- Right Side: Per Page Options -->
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 self-end md:self-auto justify-end">
                    <select name="per_page" onchange="triggerFilterForm(this)"
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

        <!-- TABLE LIST -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left w-full flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 dark:border-slate-900 flex justify-between items-center flex-wrap gap-2 bg-white dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Daftar Riwayat Izin
                </h3>
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/40 px-2.5 py-1 rounded-md">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>Klik nama pegawai untuk melihat detail izin</span>
                </div>
            </div>
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: calc(100vh - 240px);">
                <table class="w-full text-xs border-collapse">
                    <thead class="z-10">
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-6 py-3 text-left w-64 min-w-[220px]">Profil Pegawai</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-6 py-3 text-left w-44 min-w-[140px]">Jenis Izin</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-6 py-3 text-center w-48 min-w-[150px]">Periode Izin</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-6 py-3 text-left min-w-[160px]">Alasan & Catatan</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-6 py-3 text-center w-32 min-w-[110px]">Status</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-6 py-3 text-center w-28 min-w-[100px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($paginatedLeaves as $index => $leave)
                            @php
                                $empName = $leave->employee_name ?? 'Tidak Diketahui';
                                $color = $colors[$index % count($colors)];
                                $initial = getInitials($empName);
                                $unitName = $leave->schoolUnit ? $leave->schoolUnit->name : '-';
                                $processedBy = $leave->processed_by;
                                if (empty($processedBy) || $processedBy === '-') {
                                    $noteText = $leave->notes ?? '';
                                    $lowerNote = strtolower($noteText);
                                    if (
                                        str_starts_with($lowerNote, 'disetujui oleh ') ||
                                        str_starts_with($lowerNote, 'ditolak oleh ') ||
                                        str_starts_with($lowerNote, 'disetujui otomatis oleh ')
                                    ) {
                                        $parts = explode('oleh ', $noteText);
                                        $processedBy = preg_replace('/[\s.]+$/', '', end($parts));
                                    } elseif (preg_match('/\((Keputusan|Ditolak|Disetujui)\s+oleh\s+(.*?)\)/i', $noteText, $matches)) {
                                        $processedBy = $matches[2];
                                    } else {
                                        $processedBy = '-';
                                    }
                                }

                                $displayNotes = $leave->notes ?? '';
                                $lowerNotes = strtolower($displayNotes);
                                if (
                                    str_starts_with($lowerNotes, 'disetujui oleh') || 
                                    str_starts_with($lowerNotes, 'ditolak oleh') || 
                                    str_starts_with($lowerNotes, 'disetujui otomatis oleh')
                                ) {
                                    $displayNotes = '';
                                } else {
                                    $displayNotes = preg_replace('/\s*\((Keputusan|Ditolak|Disetujui)\s+oleh.*?\)/i', '', $displayNotes);
                                }

                                $reasonText = !empty($leave->reason) && $leave->reason !== '-' ? $leave->reason : ($displayNotes ?: '-');
                                
                                $start = $leave->start_date ? $leave->start_date->copy()->startOfDay() : null;
                                $end = $leave->end_date ? $leave->end_date->copy()->startOfDay() : null;
                                $durationDays = ($start && $end) ? ($start->diffInDays($end) + 1) : 1;
                                $isSingleDay = ($start && $end) ? $start->equalTo($end) : true;

                                $periodText = $isSingleDay 
                                    ? ($leave->start_date ? $leave->start_date->translatedFormat('d M Y') : '-') 
                                    : (($leave->start_date ? $leave->start_date->translatedFormat('d M Y') : '') . ' s/d ' . ($leave->end_date ? $leave->end_date->translatedFormat('d M Y') : ''));

                                $leaveData = [
                                    'id' => $leave->id,
                                    'name' => $leave->employee_name,
                                    'nuptk_nip_nik' => $leave->employee_nip ?? '-',
                                    'subject_position' => $leave->employee_position ?? '-',
                                    'unit' => strtoupper($unitName),
                                    'email' => $leave->employee_email ?? '-',
                                    'gender' => $leave->employee_gender ?? '-',
                                    'employment_status' => $leave->employee_status ?? '-',
                                    'photo_url' => !empty($leave->employee_photo) && !empty($leave->employee_unit_url) ? rtrim($leave->employee_unit_url, '/') . '/storage/' . (str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo) : '',
                                    'leave_type' => $leave->type,
                                    'leave_start' => $leave->start_date ? $leave->start_date->translatedFormat('d M Y') : '-',
                                    'leave_end' => $leave->end_date ? $leave->end_date->translatedFormat('d M Y') : '-',
                                    'period_text' => $periodText,
                                    'duration_days' => $durationDays,
                                    'leave_reason' => $reasonText,
                                    'leave_attachment' => $leave->attachment ?? '',
                                    'status_code' => $leave->status_code ?? 'I',
                                    'gets_presence_bonus' => (bool) $leave->gets_presence_bonus,
                                    'leave_status' => $leave->status,
                                    'notes' => $displayNotes,
                                    'created_at' => $leave->created_at ? $leave->created_at->translatedFormat('d M Y H:i') : '-',
                                    'updated_at' => $leave->updated_at ? $leave->updated_at->translatedFormat('d M Y H:i') : '-',
                                    'processed_by' => $processedBy,
                                    'initials' => $initial,
                                    'avatar_color' => $color,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="px-6 py-3 text-left">
                                    <div class="flex items-center gap-3">
                                        <!-- Photo/Avatar -->
                                        <div class="shrink-0">
                                            @if(!empty($leave->employee_photo) && !empty($leave->employee_unit_url))
                                                @php
                                                    $photoPath = str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo;
                                                    $photoUrl = rtrim($leave->employee_unit_url, '/') . '/storage/' . $photoPath;
                                                @endphp
                                                <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                            @else
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm shrink-0" style="background:{{ $color }}">{{ $initial }}</div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span @click="openDetail({{ json_encode($leaveData) }})" class="text-slate-900 dark:text-slate-200 font-bold tracking-tight inline-block cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:scale-[1.01] transform transition-all duration-200 origin-left truncate text-left" title="Klik untuk melihat detail lengkap">{{ $empName }}</span>
                                            <div class="flex flex-col gap-0.5 mt-0.5 min-w-0 text-left">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-slate-600 dark:text-slate-300 uppercase shrink-0">{{ $unitName }}</span>
                                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold truncate max-w-[140px]" title="{{ $leave->employee_position }}">{{ $leave->employee_position }}</span>
                                                </div>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">Diajukan: {{ $leave->created_at ? $leave->created_at->translatedFormat('d M Y H:i') : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-left">
                                    <div class="flex flex-col items-start justify-start gap-1">
                                        <div class="flex items-center gap-1.5">
                                            @php
                                                $statusCode = $leave->status_code ?? 'I';
                                                $badgeClasses = 'bg-slate-50 text-slate-700 border-slate-100 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800';
                                                if ($statusCode === 'S') {
                                                    $badgeClasses = 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-900/50';
                                                } elseif ($statusCode === 'I') {
                                                    $badgeClasses = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/50';
                                                } elseif ($statusCode === 'C') {
                                                    $badgeClasses = 'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-900/30 dark:text-sky-400 dark:border-sky-900/50';
                                                } elseif ($statusCode === 'H') {
                                                    $badgeClasses = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-900/50';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black border uppercase {{ $badgeClasses }}" title="Kode Status: {{ $statusCode }}">
                                                {{ $statusCode }}
                                            </span>
                                            <span class="text-xs text-slate-700 dark:text-slate-300 font-bold capitalize tracking-tight">{{ $leave->type }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex flex-col items-center justify-center gap-0.5">
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs whitespace-nowrap font-mono">
                                            @if($isSingleDay)
                                                {{ $leave->start_date ? $leave->start_date->translatedFormat('d M Y') : '-' }}
                                            @else
                                                {{ $leave->start_date ? $leave->start_date->translatedFormat('d M') : '' }} – {{ $leave->end_date ? $leave->end_date->translatedFormat('d M Y') : '' }}
                                            @endif
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-2 py-0.5 rounded-full">
                                            <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                            {{ $durationDays }} Hari
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-left">
                                    <div class="flex flex-col items-start gap-1 max-w-[240px]">
                                        <span class="text-slate-700 dark:text-slate-300 block truncate max-w-full text-xs font-medium"
                                              title="{{ $reasonText }}">
                                            {{ $reasonText }}
                                        </span>
                                        @if($leave->attachment)
                                            <a href="{{ $leave->attachment }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold hover:underline">
                                                <i data-lucide="paperclip" class="w-3 h-3"></i>
                                                <span>Lampiran</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        @if($leave->status === 'Pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-955/30 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30 uppercase">
                                                Pending
                                            </span>
                                        @elseif($leave->status === 'Approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-955/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30 uppercase">
                                                Disetujui
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-955/30 text-rose-700 dark:text-rose-400 border border-rose-200/30 dark:border-rose-900/30 uppercase">
                                                Ditolak
                                            </span>
                                        @endif

                                        @if($leave->status !== 'Pending' && $processedBy !== '-')
                                            <div class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold mt-0.5 leading-tight">oleh: {{ $processedBy }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="openDetail({{ json_encode($leaveData) }})" class="inline-flex items-center justify-center w-6 h-6 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer shadow-2xs hover:shadow-xs" title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        @if($leave->status === 'Pending')
                                            <form action="{{ route('leave-approvals.approve', $leave->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold rounded-lg border border-emerald-200/30 dark:border-emerald-900/30 transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                    Setujui
                                                </button>
                                            </form>
                                            <button @click="selectedLeaveId = '{{ $leave->id }}'; selectedLeaveEmployee = '{{ $leave->employee_name }}'; showRejectModal = true" class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-[10px] font-bold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer shadow-2xs hover:shadow-xs">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                                Tolak
                                            </button>
                                        @else
                                            <button @click="editLeave = { id: '{{ $leave->id }}', status: '{{ $leave->status }}', notes: {{ json_encode($displayNotes) }}, name: {{ json_encode($leave->employee_name) }}, type: '{{ $leave->type }}', school_unit_id: '{{ $leave->school_unit_id }}' }; fetchUnitLeaveTypes('{{ $leave->school_unit_id }}'); showEditModal = true" class="inline-flex items-center justify-center w-6 h-6 bg-amber-50/60 hover:bg-amber-100/60 dark:bg-amber-900/20 dark:hover:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-lg border border-amber-200/30 dark:border-amber-900/30 transition-all cursor-pointer shadow-2xs hover:shadow-xs" title="Edit Keputusan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                            </button>
                                        @endif
                                        <form action="{{ route('leave-approvals.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data izin ini secara permanen? Data di unit sekolah dan HRD Pusat akan dihapus.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-6 h-6 bg-rose-50/50 hover:bg-rose-100/60 dark:bg-rose-900/20 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer shadow-2xs hover:shadow-xs" title="Hapus Izin">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada data riwayat izin pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($paginatedLeaves instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginatedLeaves->total() > 0)
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedLeaves->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedLeaves->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $paginatedLeaves->total() }}</span>
                        data riwayat izin
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        @if ($paginatedLeaves->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none">Sebelumnya</span>
                        @else
                            <a href="{{ $paginatedLeaves->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Sebelumnya</a>
                        @endif

                        <span class="px-2 font-semibold text-slate-600 dark:text-slate-400">
                            Halaman {{ $paginatedLeaves->currentPage() }} dari {{ $paginatedLeaves->lastPage() }}
                        </span>

                        @if ($paginatedLeaves->hasMorePages())
                            <a href="{{ $paginatedLeaves->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        </div>        <!-- MODAL DETAIL PEGAWAI (LEBIH LEBAR & 2-KOLOM SPLIT) -->
        <template x-teleport="body">
            <div x-show="showEmpDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-xs text-left" style="display: none; margin-top: 0px !important; z-index: 9999;">
            <div @click.outside="showEmpDetailModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl max-w-3xl w-full overflow-hidden text-xs flex flex-col max-h-[92vh]">
                <!-- Header -->
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/70 dark:bg-slate-900/50 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Detail Pengajuan Izin</h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Informasi lengkap pengajuan izin dan riwayat keputusan HRD</p>
                        </div>
                    </div>
                    <button @click="showEmpDetailModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer p-1 rounded-lg transition-colors flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <!-- Body with 2 Columns Split Card -->
                <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                    <!-- Status Banner -->
                    <template x-if="selectedEmp">
                        <div class="rounded-xl px-4 py-2.5 border text-xs font-medium flex items-center justify-between gap-3"
                            :class="{
                                'bg-emerald-50/70 dark:bg-emerald-955/20 text-emerald-800 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-900/40': selectedEmp.leave_status === 'Approved',
                                'bg-rose-50/70 dark:bg-rose-955/20 text-rose-800 dark:text-rose-300 border-rose-200/60 dark:border-rose-900/40': selectedEmp.leave_status === 'Rejected',
                                'bg-amber-50/70 dark:bg-amber-955/20 text-amber-800 dark:text-amber-300 border-amber-200/60 dark:border-amber-900/40': selectedEmp.leave_status === 'Pending'
                            }">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                    :class="{
                                        'bg-emerald-500 ring-2 ring-emerald-200 dark:ring-emerald-800': selectedEmp.leave_status === 'Approved',
                                        'bg-rose-500 ring-2 ring-rose-200 dark:ring-rose-800': selectedEmp.leave_status === 'Rejected',
                                        'bg-amber-500 ring-2 ring-amber-200 dark:ring-amber-800': selectedEmp.leave_status === 'Pending'
                                    }"></span>
                                <span class="font-bold" x-text="
                                    selectedEmp.leave_status === 'Approved' ? 'Pengajuan Telah Disetujui' : 
                                    (selectedEmp.leave_status === 'Rejected' ? 'Pengajuan Ditolak' : 'Menunggu Persetujuan')
                                "></span>
                            </div>
                            <span class="text-[10px] opacity-80" x-text="selectedEmp.leave_status !== 'Pending' ? ('oleh: ' + selectedEmp.processed_by) : 'Perlu tindakan HRD'"></span>
                        </div>
                    </template>

                    <!-- 2 Column Split Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Kolom Kiri: Profil Pegawai -->
                        <div class="md:col-span-5 bg-slate-50/70 dark:bg-slate-900/40 rounded-xl p-4 border border-slate-100 dark:border-slate-800/80 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-3">Profil Pegawai</span>
                                <div class="flex items-start gap-3">
                                    <template x-if="selectedEmp && selectedEmp.photo_url">
                                        <img :src="selectedEmp.photo_url" class="w-12 h-12 rounded-full object-cover border border-slate-200 dark:border-slate-800 shadow-xs shrink-0">
                                    </template>
                                    <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-sm uppercase shrink-0" :style="`background: ${selectedEmp ? selectedEmp.avatar_color : '#6366f1'}`">
                                            <span x-text="selectedEmp ? selectedEmp.initials : '?'"></span>
                                        </div>
                                    </template>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-tight" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5" x-text="selectedEmp ? ('NIP: ' + selectedEmp.nuptk_nip_nik) : ''"></p>
                                        <div class="flex items-center gap-1.5 flex-wrap mt-2">
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase" x-text="selectedEmp ? selectedEmp.unit : ''"></span>
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400" x-text="selectedEmp ? selectedEmp.subject_position : ''"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-200/60 dark:border-slate-800/60 space-y-2 text-[11px]">
                                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span>Status Kerja:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.employment_status : '-'"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span>Jenis Kelamin:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : (selectedEmp.gender === 'Female' ? 'Perempuan' : selectedEmp.gender)) : '-'"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span>Email:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[140px]" :title="selectedEmp ? selectedEmp.email : ''" x-text="selectedEmp ? selectedEmp.email : '-'"></span>
                                </div>
                            </div>

                            <template x-if="selectedEmp">
                                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                                    <div x-show="selectedEmp.gets_presence_bonus" class="flex items-center gap-1.5 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/30 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Mendapat Bonus Kehadiran</span>
                                    </div>
                                    <div x-show="!selectedEmp.gets_presence_bonus" class="flex items-center gap-1.5 p-2 rounded-lg bg-slate-100/70 dark:bg-slate-800/40 border border-slate-200/50 dark:border-slate-800 text-[10px] font-medium text-slate-500 dark:text-slate-400">
                                        <i data-lucide="info" class="w-3.5 h-3.5 shrink-0 text-slate-400"></i>
                                        <span>Tidak Mendapat Bonus Kehadiran</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Kolom Kanan: Detail Pengajuan Izin -->
                        <div class="md:col-span-7 bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-100 dark:border-slate-800/80 space-y-3.5">
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <!-- Jenis Izin -->
                                <div>
                                    <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Jenis Izin</span>
                                    <template x-if="selectedEmp">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black border uppercase shrink-0"
                                                :class="{
                                                    'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-900/50': selectedEmp.status_code === 'S',
                                                    'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/50': selectedEmp.status_code === 'I',
                                                    'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-900/30 dark:text-sky-400 dark:border-sky-900/50': selectedEmp.status_code === 'C',
                                                    'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-900/50': selectedEmp.status_code === 'H'
                                                }"
                                                x-text="selectedEmp.status_code"></span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 capitalize" x-text="selectedEmp.leave_type"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Periode Izin -->
                                <div>
                                    <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Periode Izin</span>
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 text-xs" x-text="selectedEmp ? selectedEmp.period_text : '-'"></span>
                                    </div>
                                    <span class="inline-flex mt-1 items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400" x-text="selectedEmp ? (selectedEmp.duration_days + ' Hari') : ''"></span>
                                </div>
                            </div>

                            <!-- Alasan / Keterangan -->
                            <div>
                                <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Alasan / Catatan Izin</span>
                                <div class="bg-slate-50/80 dark:bg-slate-950/40 rounded-xl p-3 border border-slate-100 dark:border-slate-800">
                                    <p class="text-slate-700 dark:text-slate-300 italic font-medium leading-relaxed text-xs" x-text="selectedEmp ? selectedEmp.leave_reason : '-'"></p>
                                </div>
                            </div>

                            <!-- Berkas Lampiran -->
                            <template x-if="selectedEmp && selectedEmp.leave_attachment">
                                <div>
                                    <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Berkas Bukti / Lampiran</span>
                                    <a :href="selectedEmp.leave_attachment" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-900/40 transition-colors">
                                        <i data-lucide="paperclip" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Buka Dokumen Lampiran</span>
                                        <i data-lucide="external-link" class="w-3 h-3 opacity-70"></i>
                                    </a>
                                </div>
                            </template>

                            <!-- Riwayat Log Persetujuan -->
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-3 text-[10px]">
                                <div>
                                    <span class="block text-slate-400 dark:text-slate-500 font-medium">Diajukan Pada:</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono" x-text="selectedEmp ? selectedEmp.created_at : '-'"></span>
                                </div>
                                <template x-if="selectedEmp && selectedEmp.leave_status !== 'Pending'">
                                    <div>
                                        <span class="block text-slate-400 dark:text-slate-500 font-medium" x-text="selectedEmp.leave_status === 'Approved' ? 'Disetujui Pada:' : 'Ditolak Pada:'"></span>
                                        <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono" x-text="selectedEmp.updated_at"></span>
                                    </div>
                                </template>
                                <template x-if="selectedEmp && selectedEmp.leave_status !== 'Pending' && selectedEmp.processed_by && selectedEmp.processed_by !== '-'">
                                    <div class="col-span-2">
                                        <span class="block text-slate-400 dark:text-slate-500 font-medium">Diproses Oleh:</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp.processed_by"></span>
                                    </div>
                                </template>
                                <template x-if="selectedEmp && selectedEmp.notes && selectedEmp.notes.trim() !== '' && selectedEmp.leave_status === 'Rejected'">
                                    <div class="col-span-2 bg-rose-50/50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-lg p-2">
                                        <span class="block text-rose-500 dark:text-rose-400 font-medium text-[9px] uppercase">Alasan Penolakan:</span>
                                        <span class="font-medium text-rose-700 dark:text-rose-300" x-text="selectedEmp.notes"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer with Quick Action Buttons -->
                <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/40 shrink-0">
                    <button @click="showEmpDetailModal = false" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg text-xs transition-colors cursor-pointer">Tutup</button>
                    
                    <!-- Quick action buttons when status is Pending -->
                    <template x-if="selectedEmp && selectedEmp.leave_status === 'Pending'">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectedLeaveId = selectedEmp.id; selectedLeaveEmployee = selectedEmp.name; showRejectModal = true; showEmpDetailModal = false;" class="h-9 px-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-xs font-bold rounded-lg border border-rose-200/40 dark:border-rose-900/40 transition-all cursor-pointer inline-flex items-center gap-1.5 shadow-2xs hover:shadow-xs">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>Tolak Izin</span>
                            </button>
                            <form :action="`{{ url('leave-approvals') }}/${selectedEmp.id}/approve`" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-xs hover:shadow-sm transition-all cursor-pointer inline-flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Setujui Izin</span>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
            </div>
        </template>

        <!-- REJECT MODAL -->
        <template x-teleport="body">
            <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none; margin-top: 0px !important; z-index: 9999;">
            <div @click.outside="showRejectModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-left animate-in fade-in duration-200">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tolak Pengajuan Izin</h3>
                        <p class="text-[10px] text-slate-400 dark:text-slate-300 mt-0.5" x-text="selectedLeaveEmployee"></p>
                    </div>
                    <button @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('leave-approvals') }}/${selectedLeaveId}/reject`" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alasan Penolakan</label>
                        <textarea name="notes" required rows="3" placeholder="Masukkan alasan penolakan izin..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/30 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800 justify-end">
                        <button type="button" @click="showRejectModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-855 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-semibold rounded-lg shadow-xs hover:shadow-sm transition-all cursor-pointer">
                            Tolak Izin
                        </button>
                    </div>
                </form>
            </div>
            </div>
        </template>

        <!-- EDIT DECISION MODAL -->
        <template x-teleport="body">
            <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs text-left animate-in fade-in duration-200" style="display: none; margin-top: 0px !important; z-index: 9999;">
                <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-left flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                <span>Ubah Keputusan Izin</span>
                            </h3>
                            <p class="text-[10px] text-slate-400 dark:text-slate-300 mt-0.5" x-text="editLeave.name"></p>
                        </div>
                        <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form method="POST" :action="`{{ url('leave-approvals') }}/${editLeave.id}`" class="space-y-4 text-xs">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                                <span>Jenis Izin</span>
                                <span x-show="loadingTypes" class="text-[10px] text-slate-400 dark:text-slate-500 animate-pulse normal-case font-normal">Loading opsi...</span>
                            </label>
                            <select name="type" x-model="editLeave.type" :disabled="loadingTypes"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/30 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 disabled:opacity-50 font-sans text-xs">
                                <template x-if="availableTypes.length === 0">
                                    <option :value="editLeave.type" x-text="editLeave.type"></option>
                                </template>
                                <template x-for="t in availableTypes" :key="t.id">
                                    <option :value="t.name" x-text="t.name + ' - ' + t.status_code" :selected="t.name === editLeave.type"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Keputusan Status</label>
                            <select name="status" x-model="editLeave.status" 
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/30 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-sans text-xs">
                                <option value="Pending">Menunggu Persetujuan (Pending)</option>
                                <option value="Approved">Disetujui (Approved)</option>
                                <option value="Rejected">Ditolak (Rejected)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan / Alasan</label>
                            <textarea name="notes" x-model="editLeave.notes" rows="3" placeholder="Masukkan catatan atau alasan keputusan..."
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950/30 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none font-sans text-xs"></textarea>
                        </div>

                        <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800 justify-end">
                            <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-855 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-750 text-white text-xs font-semibold rounded-lg shadow-xs hover:shadow-sm transition-all cursor-pointer">
                                Simpan Keputusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <style>
        @media (min-width: 768px) {
            .search-container {
                max-width: 280px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('leave-table-container');

            window.triggerFilterForm = function(el) {
                const form = el.form || document.getElementById('leave-filter-form');
                if (form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                    }
                }
            };

            function loadTableContent(url) {
                if (!container) return;

                container.classList.add('opacity-40', 'pointer-events-none');
                if (window.NProgress) NProgress.start();

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('leave-table-container');
                    
                    if (newContent && container) {
                        container.innerHTML = newContent.innerHTML;
                    }
                    
                    // Update URL bar without reload
                    window.history.pushState({}, '', url.toString());

                    // Re-bind Lucide icons
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                })
                .catch(err => {
                    console.error('Error fetching filtered data:', err);
                })
                .finally(() => {
                    container.classList.remove('opacity-40', 'pointer-events-none');
                    if (window.NProgress) NProgress.done();
                });
            }

            // Delegate submit event
            document.addEventListener('submit', function (e) {
                const form = e.target.closest('#leave-filter-form');
                if (!form) return;

                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                const action = form.getAttribute('action') || window.location.pathname;
                const url = new URL(action, window.location.origin);
                url.search = params.toString();

                loadTableContent(url);
            });

            // Delegate click event for pagination and reset buttons
            document.addEventListener('click', function (e) {
                const link = e.target.closest('#leave-table-container a[href], .reset-filter-btn');
                if (!link) return;

                const hrefAttr = link.getAttribute('href');
                if (!hrefAttr || hrefAttr.startsWith('#')) return;

                // Don't intercept target="_blank" links (like attachments)
                if (link.getAttribute('target') === '_blank') return;

                // Don't intercept links to other domains/origins
                if (link.origin !== window.location.origin) return;

                e.preventDefault();
                const url = new URL(link.href);
                loadTableContent(url);
            });
        });
    </script>
</x-admin-layout>
