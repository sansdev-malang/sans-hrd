<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showEmpDetailModal: false,
        selectedEmp: null
    }">

        <!-- SUCCESS/ERROR ALERTS -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Perhatian!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('error') }}</p>
                </div>
            </div>
        @endif

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
                            class="w-full h-9 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 focus:border-slate-400 dark:focus:border-slate-600 text-slate-900 dark:text-slate-50 placeholder-slate-400 dark:placeholder-slate-500 transition-all shadow-inner">
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
                            <th class="px-6 py-3 text-left">Pegawai</th>
                            <th class="px-6 py-3 text-left">Unit</th>
                            <th class="px-6 py-3 text-center">Jenis Izin</th>
                            <th class="px-6 py-3 text-center">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-center">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($paginatedLeaves as $leave)
                            <tr>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($leave->employee_photo) && !empty($leave->employee_unit_url))
                                            @php
                                                $photoPath = str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo;
                                                $photoUrl = rtrim($leave->employee_unit_url, '/') . '/storage/' . $photoPath;
                                            @endphp
                                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                                                {{ strtoupper(substr($leave->employee_name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                             <span @click="selectedEmp = {
                                                name: '{{ $leave->employee_name }}',
                                                nuptk_nip_nik: '{{ $leave->employee_nip }}',
                                                subject_position: '{{ $leave->employee_position }}',
                                                unit: '{{ strtoupper($leave->schoolUnit ? $leave->schoolUnit->name : '-') }}',
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
                                                gets_presence_bonus: {{ $leave->gets_presence_bonus ? 'true' : 'false' }}
                                            }; showEmpDetailModal = true" class="text-slate-900 dark:text-slate-200 font-bold tracking-tight inline-block cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:scale-[1.03] transform transition-all duration-200 origin-left">{{ $leave->employee_name }}</span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-450 block mt-0.5">{{ $leave->employee_position }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-left font-nasalization">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30 uppercase">
                                        {{ $leave->schoolUnit ? $leave->schoolUnit->name : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1">
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
                                        <div class="flex items-center gap-1.5 justify-center">
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black border uppercase {{ $badgeClasses }}" title="Kode Status: {{ $statusCode }}">
                                                {{ $statusCode }}
                                            </span>
                                            <span class="text-xs text-slate-700 dark:text-slate-350 font-bold uppercase tracking-tight">{{ $leave->type }}</span>
                                        </div>
                                        @if($leave->gets_presence_bonus)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-750 dark:text-emerald-400 border border-emerald-250/20 dark:border-emerald-900/30 uppercase mt-0.5">
                                                <svg xmlns="http://www.w3.org/2500/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                Bonus Presensi
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $leave->start_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $leave->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="text-slate-600 dark:text-slate-400 block">{{ $leave->reason ?? '-' }}</span>
                                    @if($leave->attachment)
                                        <div class="mt-1">
                                            @php
                                                $attachmentUrl = str_starts_with($leave->attachment, 'http') ? $leave->attachment : (
                                                    $leave->employee_unit_url ? rtrim($leave->employee_unit_url, '/') . '/storage/' . $leave->attachment : '#'
                                                );
                                            @endphp
                                            <a href="{{ $attachmentUrl }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold underline">
                                                <i data-lucide="paperclip" class="w-3 h-3"></i>
                                                Lihat Lampiran
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada data riwayat izin pegawai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($paginatedLeaves->hasPages() || $paginatedLeaves->total() > 0)
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-4 bg-white dark:bg-slate-900">
                    <!-- Mobile view (compact) -->
                    <div class="flex flex-1 justify-between sm:hidden items-center">
                        @if($paginatedLeaves->onFirstPage())
                            <span class="px-3 py-1.5 text-xs font-semibold text-slate-350 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800/40 rounded-lg cursor-not-allowed">Sebelumnya</span>
                        @else
                            <a href="{{ $paginatedLeaves->previousPageUrl() }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-lg transition-colors">Sebelumnya</a>
                        @endif

                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Halaman {{ $paginatedLeaves->currentPage() }} dari {{ $paginatedLeaves->lastPage() }}
                        </span>

                        @if($paginatedLeaves->hasMorePages())
                            <a href="{{ $paginatedLeaves->nextPageUrl() }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 rounded-lg transition-colors">Berikutnya</a>
                        @else
                            <span class="px-3 py-1.5 text-xs font-semibold text-slate-350 dark:text-slate-600 bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-slate-800/40 rounded-lg cursor-not-allowed">Berikutnya</span>
                        @endif
                    </div>

                    <!-- Desktop view -->
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                Menampilkan
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginatedLeaves->firstItem() ?? 0 }}</span>
                                sampai
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginatedLeaves->lastItem() ?? 0 }}</span>
                                dari
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $paginatedLeaves->total() }}</span>
                                data riwayat izin
                            </p>
                        </div>

                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-lg shadow-2xs" aria-label="Pagination">
                                {{-- Previous Page Link --}}
                                @if ($paginatedLeaves->onFirstPage())
                                    <span class="relative inline-flex items-center rounded-l-lg px-2 py-1.5 text-slate-350 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-not-allowed">
                                        <span class="sr-only">Sebelumnya</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @else
                                    <a href="{{ $paginatedLeaves->previousPageUrl() }}" class="relative inline-flex items-center rounded-l-lg px-2 py-1.5 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                        <span class="sr-only">Sebelumnya</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                @endif

                                {{-- Page numbers --}}
                                @php
                                    $startPage = max($paginatedLeaves->currentPage() - 2, 1);
                                    $endPage = min($startPage + 4, $paginatedLeaves->lastPage());
                                    if ($endPage - $startPage < 4) {
                                        $startPage = max($endPage - 4, 1);
                                    }
                                @endphp

                                @if ($startPage > 1)
                                    <a href="{{ $paginatedLeaves->url(1) }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">1</a>
                                    @if ($startPage > 2)
                                        <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-default">...</span>
                                    @endif
                                @endif

                                @for ($p = $startPage; $p <= $endPage; $p++)
                                    @if ($p == $paginatedLeaves->currentPage())
                                        <span aria-current="page" class="relative z-10 inline-flex items-center bg-indigo-50 dark:bg-indigo-950 px-3 py-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900 focus:z-20">{{ $p }}</span>
                                    @else
                                        <a href="{{ $paginatedLeaves->url($p) }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $p }}</a>
                                    @endif
                                @endfor

                                @if ($endPage < $paginatedLeaves->lastPage())
                                    @if ($endPage < $paginatedLeaves->lastPage() - 1)
                                        <span class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-default">...</span>
                                    @endif
                                    <a href="{{ $paginatedLeaves->url($paginatedLeaves->lastPage()) }}" class="relative inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">{{ $paginatedLeaves->lastPage() }}</a>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($paginatedLeaves->hasMorePages())
                                    <a href="{{ $paginatedLeaves->nextPageUrl() }}" class="relative inline-flex items-center rounded-r-lg px-2 py-1.5 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                        <span class="sr-only">Berikutnya</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="relative inline-flex items-center rounded-r-lg px-2 py-1.5 text-slate-350 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-not-allowed">
                                        <span class="sr-only">Berikutnya</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- MODAL DETAIL PEGAWAI -->
        <template x-teleport="body">
            <div x-show="showEmpDetailModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs text-left" style="display: none; margin-top: 0px !important; z-index: 9999;">
            <div @click.outside="showEmpDetailModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden text-xs">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-200 font-nasalization flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Profil Pegawai
                    </h3>
                    <button @click="showEmpDetailModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="p-5 space-y-6">
                    <div class="flex items-center gap-4">
                        <!-- Photo / Initials -->
                        <div class="shrink-0">
                            <template x-if="selectedEmp && selectedEmp.photo_url">
                                <img :src="selectedEmp.photo_url" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-800 shadow-sm">
                            </template>
                            <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                <div class="w-16 h-16 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold flex items-center justify-center text-2xl uppercase shadow-sm">
                                    <span x-text="selectedEmp ? selectedEmp.name.substring(0,2) : ''"></span>
                                </div>
                            </template>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                            <p class="text-slate-400 dark:text-slate-500 font-mono" x-text="selectedEmp ? 'NIP/NUPTK: ' + (selectedEmp.nuptk_nip_nik || '-') : ''"></p>
                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 uppercase" x-text="selectedEmp ? selectedEmp.subject_position : ''"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-[11px] pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Unit Kerja</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200 uppercase" x-text="selectedEmp ? selectedEmp.unit : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Email</span>
                            <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.email : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jenis Kelamin</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : 'Perempuan') : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Status Pegawai</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.employment_status : ''"></span>
                        </div>
                    </div>

                    <!-- Detail Pengajuan Izin -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[10px]">Detail Izin Pegawai</h4>
                        <div class="grid grid-cols-2 gap-4 text-[11px]">
                            <div>
                                <span class="block text-slate-400 text-[9px] uppercase font-semibold mb-1">Jenis Izin</span>
                                <div class="flex flex-col items-start gap-1">
                                    <template x-if="selectedEmp">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black border uppercase"
                                                :class="{
                                                    'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-900/50': selectedEmp.status_code === 'S',
                                                    'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-900/50': selectedEmp.status_code === 'I',
                                                    'bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-900/30 dark:text-sky-400 dark:border-sky-900/50': selectedEmp.status_code === 'C',
                                                    'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-900/50': selectedEmp.status_code === 'H'
                                                }"
                                                x-text="selectedEmp.status_code"></span>
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200/45 dark:border-slate-800 uppercase" x-text="selectedEmp.leave_type"></span>
                                        </div>
                                    </template>
                                    <template x-if="selectedEmp && selectedEmp.gets_presence_bonus">
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[8px] font-bold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-900/30 uppercase mt-0.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                            Bonus Presensi
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <span class="block text-slate-400 text-[9px] uppercase font-semibold">Rentang Tanggal</span>
                                <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.leave_start + ' - ' + selectedEmp.leave_end : ''"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-slate-400 text-[9px] uppercase font-semibold">Keterangan</span>
                                <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.leave_reason : ''"></span>
                            </div>
                            <template x-if="selectedEmp && selectedEmp.leave_attachment">
                                <div class="col-span-2">
                                    <span class="block text-slate-400 text-[9px] uppercase font-semibold">Lampiran</span>
                                    @php
                                        $modalAttachmentUrl = "selectedEmp.leave_attachment.startsWith('http') ? selectedEmp.leave_attachment : (selectedEmp.photo_url ? selectedEmp.photo_url.split('/storage')[0] + '/storage/' + selectedEmp.leave_attachment : '#')";
                                    @endphp
                                    <a :href="{{ $modalAttachmentUrl }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                        Lihat Lampiran Dokumen
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button @click="showEmpDetailModal = false" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">Tutup</button>
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
