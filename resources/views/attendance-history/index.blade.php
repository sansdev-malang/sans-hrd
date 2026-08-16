<x-admin-layout>
    <div class="p-6 space-y-6">
        <div id="history-table-container" class="space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Riwayat Kehadiran Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Melihat, memantau, dan menyaring riwayat log absensi harian pegawai secara terperinci.</p>
            </div>
            
            <!-- EXPORT DROPDOWN -->
            <div x-data="{ open: false }" class="relative inline-block text-left w-full md:w-auto">
                <button type="button" @click="open = !open" @click.outside="open = false" class="w-full md:w-auto justify-center h-9 px-4 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Ekspor Laporan</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                </button>
                
                <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
                    <a href="{{ route('attendance-history.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors border-b border-slate-100 dark:border-slate-800">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600 dark:text-emerald-500"></i>
                        Excel (.xlsx)
                    </a>
                    <a href="{{ route('attendance-history.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-700 dark:hover:text-rose-400 transition-colors">
                        <i data-lucide="file-text" class="w-4 h-4 text-rose-650 dark:text-rose-500"></i>
                        PDF (.pdf)
                    </a>
                </div>
            </div>
        </section>

        <!-- FILTERS & SEARCH -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
            <form method="GET" action="{{ route('attendance-history.index') }}" id="history-filter-form" data-no-loader="true" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                
                <!-- Left Side: Search & Filters -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    
                    <!-- Search Box Welded with Cari Button (Premium Input Group) -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center w-full search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                        <input type="text" name="search" x-model="searchVal" x-ref="searchInput" placeholder="Cari nama pegawai..."
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

                    <!-- Mulai Tanggal -->
                    <input type="date" name="start_date" value="{{ request('start_date', $startDateReq) }}" onchange="triggerFilterForm(this)" 
                        class="h-9 px-2.5 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 shadow-inner focus:outline-none focus:ring-0 focus:ring-transparent focus:border-slate-300 dark:focus:border-slate-700 cursor-pointer font-mono dark:[color-scheme:dark]" title="Mulai Tanggal">

                    <!-- Selesai Tanggal -->
                    <input type="date" name="end_date" value="{{ request('end_date', $endDateReq) }}" onchange="triggerFilterForm(this)" 
                        class="h-9 px-2.5 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 shadow-inner focus:outline-none focus:ring-0 focus:ring-transparent focus:border-slate-300 dark:focus:border-slate-700 cursor-pointer font-mono dark:[color-scheme:dark]" title="Selesai Tanggal">

                    <!-- Status Kehadiran -->
                    <select name="status" onchange="triggerFilterForm(this)" 
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-36 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 shadow-inner focus:outline-none focus:ring-0 focus:ring-transparent focus:border-slate-300 dark:focus:border-slate-700 cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Status</option>
                        <option value="Hadir" {{ request('status') === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="Terlambat" {{ request('status') === 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="Alfa" {{ request('status') === 'Alfa' ? 'selected' : '' }}>Alfa</option>
                        <option value="Sakit" {{ request('status') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Izin" {{ request('status') === 'Izin' ? 'selected' : '' }}>Izin</option>
                        <option value="Cuti" {{ request('status') === 'Cuti' ? 'selected' : '' }}>Cuti</option>
                        <option value="Dinas" {{ request('status') === 'Dinas' ? 'selected' : '' }}>Dinas</option>
                    </select>

                    <!-- Unit Sekolah -->
                    @if(isset($schoolUnits) && count($schoolUnits) > 0)
                        <select name="unit_id" onchange="if(typeof updatePositions === 'function') updatePositions(); triggerFilterForm(this);" 
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 shadow-inner focus:outline-none focus:ring-0 focus:ring-transparent focus:border-slate-300 dark:focus:border-slate-700 cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Unit</option>
                            @foreach($schoolUnits as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Jabatan / Posisi -->
                    @if(isset($positions) && count($positions) > 0)
                        <select name="position" onchange="triggerFilterForm(this)" 
                            class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 shadow-inner focus:outline-none focus:ring-0 focus:ring-transparent focus:border-slate-300 dark:focus:border-slate-700 cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                            <option value="">Semua Jabatan</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position') === $pos ? 'selected' : '' }}>
                                    {{ $pos }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Reset Button -->
                    @if(request()->hasAny(['search', 'start_date', 'end_date', 'status', 'unit_id', 'position']))
                        <a href="{{ route('attendance-history.index') }}" data-no-loader="true" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- TABLE SECTION -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <!-- Header Table Card -->
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Riwayat Kehadiran Pegawai</h3>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-500">Tampilkan</span>
                    <select onchange="document.getElementById('filter_per_page').value = this.value; document.getElementById('filter_form').submit();" class="text-xs h-8 px-2 bg-white dark:bg-slate-950 border border-slate-250 dark:border-slate-800 rounded-md text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 baris</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 baris</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 baris</option>
                        <option value="200" {{ request('per_page') == '200' ? 'selected' : '' }}>200 baris</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
                    </select>
                    <span class="text-slate-500">dari total {{ $paginatedHistory->total() }} entri</span>
                </div>
            </div>
            <div class="overflow-auto max-h-[600px] w-full relative">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="sticky top-0 z-10 border-b border-slate-200 dark:border-slate-850 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-semibold shadow-[0_1px_0_0_rgba(0,0,0,0.05)]">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left">Pegawai</th>
                            <th class="px-4 py-3 text-left">Hari & Tanggal</th>
                            <th class="px-4 py-3 text-left">Jadwal Kerja</th>
                            <th class="px-4 py-3 text-center">Waktu Masuk</th>
                            <th class="px-4 py-3 text-center">Waktu Pulang</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left max-w-xs">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($paginatedHistory as $index => $row)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="px-4 py-3 text-center font-medium text-slate-400 dark:text-slate-500">
                                    {{ ($paginatedHistory->currentPage() - 1) * $paginatedHistory->perPage() + $index + 1 }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $row['employee_name'] }}</div>
                                    @if($row['employee_nip'])
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mb-0.5">{{ $row['employee_nip'] }}</div>
                                    @endif
                                    <div class="text-[10px] text-slate-500 dark:text-slate-450">{{ $row['unit_name'] }} - {{ $row['position'] }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">
                                    {{ $row['date_formatted'] }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">
                                    @if($row['shift_name'] === '-')
                                        <span>-</span>
                                    @else
                                        <div class="text-slate-800 dark:text-slate-200 font-semibold">{{ $row['shift_name'] }}</div>
                                        @if($row['shift_start'])
                                            <div class="text-[10px] text-slate-400 dark:text-slate-400 font-mono">
                                                {{ $row['shift_start'] }} - {{ $row['shift_end'] }}
                                            </div>
                                        @else
                                            <div class="text-[10px] text-slate-400 dark:text-slate-400 font-mono">Libur</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-medium">
                                    @if($row['check_in'])
                                        <span class="text-slate-800 dark:text-slate-200">{{ $row['check_in'] }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-650">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-medium">
                                    @if($row['check_out'])
                                        <span class="text-slate-800 dark:text-slate-200">{{ $row['check_out'] }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-650">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $statusClass = match($row['status']) {
                                            'Hadir' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-450 border-emerald-250 dark:border-emerald-800/40',
                                            'Terlambat' => 'bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-500 border-amber-200 dark:border-amber-900/30',
                                            'Alfa' => 'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-450 border-rose-250 dark:border-rose-900/40',
                                            'Sakit' => 'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-450 border-red-200 dark:border-red-900/40',
                                            'Izin' => 'bg-orange-50 dark:bg-orange-950/20 text-orange-700 dark:text-orange-450 border-orange-200 dark:border-orange-900/30',
                                            'Cuti' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-450 border-blue-200 dark:border-blue-900/40',
                                            'Dinas' => 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border-indigo-200/30 dark:border-indigo-900/30',
                                            'Off' => 'bg-slate-50 dark:bg-slate-800/40 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-800',
                                            'Libur' => 'bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800',
                                            'Pending' => 'bg-slate-50 dark:bg-slate-950 text-slate-400 border-slate-100 dark:border-slate-900',
                                            default => 'bg-slate-50 text-slate-500 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 max-w-xs truncate">
                                    @if($row['status'] === 'Terlambat' && $row['late_minutes'] > 0)
                                        <span class="text-amber-600 dark:text-amber-500 font-semibold">Terlambat {{ $row['late_minutes'] }} menit</span>
                                    @elseif($row['notes'])
                                        <span class="font-medium">{{ $row['notes'] }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700 font-mono">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="clipboard-x" class="w-8 h-8 opacity-40"></i>
                                        <span class="text-sm font-semibold">Tidak Ada Data Riwayat Kehadiran</span>
                                        <span class="text-xs">Silakan sesuaikan filter tanggal atau kata kunci pencarian Anda.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION FOOTER -->
            <!-- PAGINATION FOOTER -->
            @if($paginatedHistory->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $paginatedHistory->links('pagination::simple-tailwind') }}
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-end">
                        {{ $paginatedHistory->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </section>
        </div>

    </div>

    <script>
        window.triggerFilterForm = function(el) {
            const form = el.form || document.getElementById('history-filter-form');
            if (form) {
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const unitPositions = @json($unitPositions ?? []);
            const unitSelect = document.querySelector('select[name="unit_id"]');
            const positionSelect = document.querySelector('select[name="position"]');
            
            function updatePositions() {
                if (!unitSelect || !positionSelect) return;
                const selectedUnit = unitSelect.value;
                const positions = unitPositions[selectedUnit] || unitPositions[''] || [];
                
                const currentValue = positionSelect.value;
                
                positionSelect.innerHTML = '<option value="">Semua Jabatan</option>';
                
                positions.forEach(pos => {
                    const opt = document.createElement('option');
                    opt.value = pos;
                    opt.textContent = pos;
                    if (pos === currentValue) {
                        opt.selected = true;
                    }
                    positionSelect.appendChild(opt);
                });
            }

            window.updatePositions = updatePositions;
            
            if (unitSelect && positionSelect) {
                unitSelect.addEventListener('change', updatePositions);
                // Run initially to sync position list if a unit was selected
                if (unitSelect.value !== '') {
                    updatePositions();
                }
            }

            // AJAX Filtering and Pagination
            const container = document.getElementById('history-table-container');
            if (container) {
                function loadTableContent(url) {
                    container.classList.add('opacity-40', 'pointer-events-none');
                    if (window.NProgress) NProgress.start();

                    const relativeUrl = url.pathname + url.search;

                    fetch(relativeUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.getElementById('history-table-container');
                        if (newContent && container) {
                            container.innerHTML = newContent.innerHTML;
                            
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                            
                            // Re-bind unitSelect listener on newly loaded DOM
                            const newUnitSelect = document.querySelector('select[name="unit_id"]');
                            const newPositionSelect = document.querySelector('select[name="position"]');
                            if (newUnitSelect && newPositionSelect) {
                                newUnitSelect.addEventListener('change', window.updatePositions);
                            }
                            
                            window.history.pushState({}, '', url.toString());
                        }
                    })
                    .catch(error => {
                        console.error('AJAX Load Error:', error);
                        window.location.href = url.toString();
                    })
                    .finally(() => {
                        container.classList.remove('opacity-40', 'pointer-events-none');
                        if (window.NProgress) NProgress.done();
                    });
                }

                // Delegate submit event
                document.addEventListener('submit', function (e) {
                    const form = e.target.closest('#history-filter-form');
                    if (!form) return;

                    e.preventDefault();
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const action = form.getAttribute('action') || window.location.pathname;
                    const url = new URL(action, window.location.origin);
                    url.search = params.toString();

                    loadTableContent(url);
                });

                // Delegate click event for pagination
                document.addEventListener('click', function (e) {
                    const link = e.target.closest('#history-table-container a[href]');
                    if (!link) return;

                    const hrefAttr = link.getAttribute('href');
                    if (!hrefAttr || hrefAttr.startsWith('#') || hrefAttr.includes('export')) return;

                    e.preventDefault();
                    const url = new URL(link.href);
                    loadTableContent(url);
                });
            }
        });
    </script>

    <style>
        @media (min-width: 640px) {
            .search-container {
                max-width: 280px !important;
            }
        }
        .search-container button[type="submit"]:hover {
            background-color: #0f172a !important; /* bg-slate-900 */
            color: #ffffff !important; /* text-white */
        }
        .dark .search-container button[type="submit"]:hover {
            background-color: #f8fafc !important; /* bg-slate-100 */
            color: #0f172a !important; /* text-slate-900 */
        }
    </style>
</x-admin-layout>
