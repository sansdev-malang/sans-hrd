<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showEmpDetailModal: false,
        selectedEmp: null
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
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left ">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-200 font-nasalization">Riwayat Izin Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Daftar riwayat izin, sakit, dan cuti pegawai yang dihimpun dari seluruh unit sekolah.</p>
            </div>
        </header>

        <!-- FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('leave-approvals.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <!-- Left Side: Search & Filters -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <!-- Search Box -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="relative w-full search-container">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari pegawai..."
                            style="padding-left: 0.75rem; padding-right: 2.25rem;"
                            class="w-full h-9 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 focus:border-slate-400 dark:focus:border-slate-600 text-slate-900 dark:text-slate-55 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
                        <button type="submit" 
                            :class="searchVal.trim() !== '' ? 'text-indigo-600 dark:text-indigo-400 scale-105' : 'text-slate-400 dark:text-slate-500'"
                            class="absolute right-0 top-0 h-full w-9 flex items-center justify-center hover:text-indigo-750 dark:hover:text-indigo-300 transition-all duration-200 cursor-pointer bg-transparent border-0">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Unit -->
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                        <select name="unit_id" onchange="this.form.submit()"
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Unit Sekolah</option>
                            @foreach($schoolUnits as $su)
                                <option value="{{ $su->id }}" {{ request('unit_id') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Jenis Izin -->
                    @if(isset($leaveTypes) && count($leaveTypes) > 0)
                        <select name="type" onchange="this.form.submit()"
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Jenis Izin</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt }}" {{ request('type') == $lt ? 'selected' : '' }}>{{ $lt }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if(request()->anyFilled(['search', 'unit_id', 'type']) || request()->filled('per_page') && request('per_page') != 50)
                        <a href="{{ route('leave-approvals.index') }}" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" title="Reset Filter">
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

        <!-- TABLE LIST -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left w-full flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 dark:border-slate-900 flex justify-between items-center flex-wrap gap-2 bg-white dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Daftar Riwayat Izin
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-505 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3 text-left w-64 min-w-[220px]">Profil Pegawai</th>
                            <th class="px-6 py-3 text-left w-48 min-w-[150px]">Jenis Izin</th>
                            <th class="px-6 py-3 text-center w-32">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-center w-32">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-left min-w-[280px]">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($paginatedLeaves as $index => $leave)
                            @php
                                $empName = $leave->employee_name ?? 'Tidak Diketahui';
                                $color = $colors[$index % count($colors)];
                                $initial = getInitials($empName);
                                $unitName = $leave->schoolUnit ? $leave->schoolUnit->name : '-';
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
                                             <span @click="selectedEmp = {
                                                name: '{{ $leave->employee_name }}',
                                                nuptk_nip_nik: '{{ $leave->employee_nip }}',
                                                subject_position: '{{ $leave->employee_position }}',
                                                unit: '{{ strtoupper($unitName) }}',
                                                email: '{{ $leave->employee_email }}',
                                                gender: '{{ $leave->employee_gender }}',
                                                employment_status: '{{ $leave->employee_status }}',
                                                photo_url: '{{ !empty($leave->employee_photo) && !empty($leave->employee_unit_url) ? rtrim($leave->employee_unit_url, '/') . '/storage/' . (str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo) : '' }}',
                                                leave_type: '{{ $leave->type }}',
                                                leave_start: '{{ $leave->start_date->format('d M Y') }}',
                                                leave_end: '{{ $leave->end_date->format('d M Y') }}',
                                                leave_reason: '{{ addslashes($leave->reason ?? '-') }}',
                                                leave_attachment: '{{ $leave->attachment ?? '' }}',
                                                status_code: '{{ $leave->status_code ?? 'I' }}',
                                                gets_presence_bonus: {{ $leave->gets_presence_bonus ? 'true' : 'false' }},
                                                leave_status: '{{ $leave->status }}'
                                            }; showEmpDetailModal = true" class="text-slate-900 dark:text-slate-200 font-bold tracking-tight inline-block cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:scale-[1.01] transform transition-all duration-200 origin-left truncate">{{ $empName }}</span>
                                             <div class="flex flex-col gap-0.5 mt-0.5 min-w-0">
                                                 <span class="text-[9px] px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-slate-600 dark:text-slate-300 truncate max-w-[120px] inline-block w-max">{{ $unitName }}</span>
                                                 <span class="text-[10px] text-slate-500 dark:text-slate-450 block truncate max-w-[180px]" title="{{ $leave->employee_position }}">{{ $leave->employee_position }}</span>
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
                                                    $badgeClasses = 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/30 dark:text-rose-455 dark:border-rose-900/50';
                                                } elseif ($statusCode === 'I') {
                                                    $badgeClasses = 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-450 dark:border-amber-900/50';
                                                } elseif ($statusCode === 'C') {
                                                    $badgeClasses = 'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-900/30 dark:text-sky-450 dark:border-sky-900/50';
                                                } elseif ($statusCode === 'H') {
                                                    $badgeClasses = 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-450 dark:border-emerald-900/50';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black border uppercase {{ $badgeClasses }}" title="Kode Status: {{ $statusCode }}">
                                                {{ $statusCode }}
                                            </span>
                                            <span class="text-xs text-slate-700 dark:text-slate-355 font-bold capitalize tracking-tight">{{ $leave->type }}</span>
                                        </div>
                                        @if($leave->attachment)
                                            <div class="mt-1">
                                                @php
                                                    $attachmentUrl = str_starts_with($leave->attachment, 'http') ? $leave->attachment : (
                                                        $leave->employee_unit_url ? rtrim($leave->employee_unit_url, '/') . '/storage/' . $leave->attachment : '#'
                                                    );
                                                @endphp
                                                <a href="{{ $attachmentUrl }}" target="_blank" class="inline-flex items-center gap-1 text-[9px] text-indigo-700 hover:text-indigo-850 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 px-1.5 py-0.5 rounded transition-all shadow-2xs hover:shadow-xs">
                                                    <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                                    Lampiran
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center font-mono">
                                    {{ $leave->start_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-3 text-center font-mono">
                                    {{ $leave->end_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-3 text-left">
                                    <span class="text-slate-600 dark:text-slate-400 block truncate max-w-xs" title="{{ $leave->reason ?? '-' }}">{{ $leave->reason ?? '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
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
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-655 flex items-center justify-center cursor-not-allowed select-none">Sebelumnya</span>
                        @else
                            <a href="{{ $paginatedLeaves->appends(request()->query())->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Sebelumnya</a>
                        @endif

                        <span class="px-2 font-semibold text-slate-600 dark:text-slate-400">
                            Halaman {{ $paginatedLeaves->currentPage() }} dari {{ $paginatedLeaves->lastPage() }}
                        </span>

                        @if ($paginatedLeaves->hasMorePages())
                            <a href="{{ $paginatedLeaves->appends(request()->query())->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-655 flex items-center justify-center cursor-not-allowed select-none">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- MODAL DETAIL PEGAWAI -->
        <template x-teleport="body">
            <div x-show="showEmpDetailModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs text-left" style="display: none; margin-top: 0px !important; z-index: 9999;">
            <div @click.outside="showEmpDetailModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-lg w-full overflow-hidden text-xs">
                <!-- Header -->
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                        <span>Detail Riwayat Izin</span>
                    </h3>
                    <button @click="showEmpDetailModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-5 space-y-5">
                    <!-- Employee Compact Profile Card -->
                    <div class="bg-slate-50 dark:bg-slate-900/40 rounded-xl p-4 border border-slate-100 dark:border-slate-800/80 flex items-center gap-4">
                        <!-- Photo / Initials -->
                        <div class="shrink-0">
                            <template x-if="selectedEmp && selectedEmp.photo_url">
                                <img :src="selectedEmp.photo_url" class="w-14 h-14 rounded-full object-cover border border-slate-200 dark:border-slate-800 shadow-xs">
                            </template>
                            <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                <div class="w-14 h-14 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold flex items-center justify-center text-xl uppercase shadow-xs">
                                    <span x-text="selectedEmp ? selectedEmp.name.substring(0,2) : ''"></span>
                                </div>
                            </template>
                        </div>
                        <div class="space-y-1 text-left flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-200 font-nasalization truncate" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                            <p class="text-[10px] text-slate-550 dark:text-slate-400 truncate" x-text="selectedEmp ? selectedEmp.email : ''"></p>
                            <div class="flex items-center gap-2 flex-wrap pt-0.5">
                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30 uppercase" x-text="selectedEmp ? selectedEmp.subject_position : ''"></span>
                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-900/60 text-slate-700 dark:text-slate-350 border border-slate-200/50 dark:border-slate-800/50 uppercase" x-text="selectedEmp ? selectedEmp.unit : ''"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Info Grid -->
                    <div class="grid grid-cols-2 gap-4 text-[11px] bg-white dark:bg-slate-900 text-left">
                        <div>
                            <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Jenis Izin</span>
                            <div class="flex flex-col items-start gap-1.5">
                                <template x-if="selectedEmp">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <!-- SICH Code Badge -->
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black border uppercase"
                                            :class="{
                                                'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/30 dark:text-rose-455 dark:border-rose-900/50': selectedEmp.status_code === 'S',
                                                'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/50': selectedEmp.status_code === 'I',
                                                'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-900/30 dark:text-sky-400 dark:border-sky-900/50': selectedEmp.status_code === 'C',
                                                'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-900/50': selectedEmp.status_code === 'H'
                                            }"
                                            x-text="selectedEmp.status_code"></span>
                                        
                                        <!-- Type Name -->
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-950 text-slate-850 dark:text-slate-200 border border-slate-200/50 dark:border-slate-800 capitalize" x-text="selectedEmp.leave_type"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Rentang Tanggal</span>
                            <div class="flex items-center gap-1.5 h-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
                                <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.leave_start + ' - ' + selectedEmp.leave_end : ''"></span>
                            </div>
                        </div>

                        <div class="col-span-2 pt-1">
                            <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Keterangan / Alasan</span>
                            <div class="bg-slate-50/50 dark:bg-slate-950/30 rounded-xl p-3 border border-slate-100 dark:border-slate-800/80">
                                <p class="text-slate-600 dark:text-slate-350 italic font-medium leading-relaxed" x-text="selectedEmp ? selectedEmp.leave_reason : ''"></p>
                            </div>
                        </div>

                        <template x-if="selectedEmp && selectedEmp.leave_attachment">
                            <div class="col-span-2 pt-1">
                                <span class="block text-slate-400 dark:text-slate-500 text-[9px] uppercase font-bold tracking-tight mb-1">Berkas Lampiran</span>
                                @php
                                    $modalAttachmentUrl = "selectedEmp.leave_attachment.startsWith('http') ? selectedEmp.leave_attachment : (selectedEmp.photo_url ? selectedEmp.photo_url.split('/storage')[0] + '/storage/' + selectedEmp.leave_attachment : '#')";
                                @endphp
                                <a :href="{{ $modalAttachmentUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] text-indigo-750 hover:text-indigo-850 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 transition-all shadow-2xs hover:shadow-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                    Lihat Lampiran Dokumen
                                </a>
                            </div>
                        </template>
                    </div>

                    <!-- Additional Details Collapsible -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-4 text-[10px] text-left">
                        <div>
                            <span class="block text-slate-450 dark:text-slate-500 font-medium">Status Kepegawaian</span>
                            <span class="font-bold text-slate-600 dark:text-slate-350" x-text="selectedEmp ? selectedEmp.employment_status : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-450 dark:text-slate-500 font-medium">Jenis Kelamin</span>
                            <span class="font-bold text-slate-600 dark:text-slate-350" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : 'Perempuan') : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex justify-end bg-slate-50 dark:bg-slate-900/40">
                    <button @click="showEmpDetailModal = false" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-850 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-2xs hover:shadow-xs transition-all cursor-pointer">Tutup</button>
                </div>
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
</x-admin-layout>
