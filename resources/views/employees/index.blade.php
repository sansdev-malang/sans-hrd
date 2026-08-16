<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ showEmpDetailModal: false, showImportModal: false, showCreateModal: {{ $errors->any() && !old('_edit_id') ? 'true' : 'false' }}, showEditModal: {{ $errors->any() && old('_edit_id') ? 'true' : 'false' }}, showDeleteModal: false, selectedEmp: {{ old('_edit_emp_data') ? json_encode(array_merge(json_decode(old('_edit_emp_data'), true) ?? [], request()->old(), ['raw_name' => old('name', json_decode(old('_edit_emp_data'), true)['raw_name'] ?? '')])) : 'null' }} }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Pegawai Terintegrasi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar gabungan dan manajemen guru/staf dari seluruh unit sekolah yang terhubung.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto shrink-0">
                <!-- EXPORT DROPDOWN -->
                <div x-data="{ open: false }" class="relative w-full sm:w-auto">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="w-full sm:w-auto justify-center px-4 py-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-xs rounded-lg shadow-sm transition-all cursor-pointer whitespace-nowrap flex items-center gap-2">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Ekspor</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                    </button>
                    
                    <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50">
                        <a href="{{ route('employees.export.excel', request()->query()) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors border-b border-slate-100 dark:border-slate-800">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600 dark:text-emerald-500"></i>
                            Excel (.xlsx)
                        </a>
                        <a href="{{ route('employees.export.pdf', request()->query()) }}" class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-rose-50 dark:hover:bg-rose-900/30 hover:text-rose-700 dark:hover:text-rose-400 transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4 text-rose-600 dark:text-rose-500"></i>
                            PDF (.pdf)
                        </a>
                    </div>
                </div>
                <button @click="showImportModal = true" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-100 cursor-pointer w-full sm:w-auto">
                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5 text-slate-500"></i>
                    Impor Pegawai
                </button>
                <button @click="showCreateModal = true" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer w-full sm:w-auto">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    Tambah Pegawai
                </button>
            </div>
        </header>

        <!-- SUMMARY CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
            <!-- Total Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex items-center justify-between shadow-sm text-left">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Seluruh Pegawai</span>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ collect($rawEmployees)->count() }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 ml-1">Pegawai</span></h3>
                </div>
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Dynamic Unit Cards -->
            @foreach($schoolUnits as $unit)
                @php
                    $count = $unitCounts[$unit->id] ?? 0;
                    $colorClasses = match(strtolower($unit->name)) {
                        'paud' => [
                            'bg' => 'bg-amber-50 dark:bg-amber-950/20',
                            'border' => 'border-amber-100 dark:border-amber-900/30',
                            'text' => 'text-amber-600 dark:text-amber-400',
                            'icon' => 'baby'
                        ],
                        'sd' => [
                            'bg' => 'bg-emerald-50 dark:bg-emerald-950/20',
                            'border' => 'border-emerald-100 dark:border-emerald-900/30',
                            'text' => 'text-emerald-600 dark:text-emerald-400',
                            'icon' => 'graduation-cap'
                        ],
                        'smp' => [
                            'bg' => 'bg-blue-50 dark:bg-blue-950/20',
                            'border' => 'border-blue-100 dark:border-blue-900/30',
                            'text' => 'text-blue-600 dark:text-blue-400',
                            'icon' => 'book-open'
                        ],
                        default => [
                            'bg' => 'bg-slate-50 dark:bg-slate-800/30',
                            'border' => 'border-slate-200 dark:border-slate-800/50',
                            'text' => 'text-slate-600 dark:text-slate-400',
                            'icon' => 'user'
                        ]
                    };
                @endphp
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex items-center justify-between shadow-sm text-left">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Unit {{ $unit->name }}</span>
                        <h3 class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ $count }} <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 ml-1">Pegawai</span></h3>
                    </div>
                    <div class="p-3 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} rounded-xl">
                        <i data-lucide="{{ $colorClasses['icon'] }}" class="w-5 h-5"></i>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- FILTERS & SEARCH & DATA TABLE WRAPPER -->
        <div id="employee-table-container" class="w-full space-y-6">
            <!-- FILTERS & SEARCH -->
            <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm w-full text-left">
                <form id="filter-form" data-no-loader="true" method="GET" action="{{ route('employees.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <!-- Left Side: Search & Filters -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <!-- Search Box Welded with Cari Button (Premium Input Group) -->
                    <div x-data="{ searchVal: '{{ request('search') }}' }" class="flex items-center w-full search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
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
                    <select name="unit" onchange="triggerFilterForm(this)"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($schoolUnits as $su)
                            <option value="{{ $su->id }}" {{ request('unit') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                        @endforeach
                    </select>

                    <!-- Jabatan -->
                    <select name="position" onchange="triggerFilterForm(this)"
                        class="h-9 pl-2.5 pr-8 flex-1 sm:flex-initial sm:w-44 text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap">
                        <option value="">Semua Jabatan</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                        @endforeach
                    </select>

                    @if(request()->anyFilled(['search', 'unit', 'position']) || request()->filled('per_page') && request('per_page') != 50)
                        <a href="{{ route('employees.index') }}" data-no-loader="true" class="h-9 px-3 flex items-center justify-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-lg transition-colors reset-filter-btn" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
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

        <!-- DATA TABLE -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <div class="p-5 border-b border-slate-100 dark:border-slate-900 flex justify-between items-center flex-wrap gap-2 bg-white dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    @if(request('unit') && $schoolUnits->firstWhere('id', request('unit')))
                        Daftar Pegawai {{ $schoolUnits->firstWhere('id', request('unit'))->name }}
                    @else
                        Daftar Pegawai
                    @endif
                </h3>
                <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 md:hidden bg-slate-50 dark:bg-slate-900/50 px-2 py-1 rounded-md border border-slate-200/50 dark:border-slate-800/40">
                    <i data-lucide="move-horizontal" class="w-3.5 h-3.5 animate-pulse text-indigo-500"></i>
                    <span>Geser tabel</span>
                </div>
            </div>
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: calc(100vh - 240px);">
                <table class="w-full min-w-[900px] text-xs border-collapse">
                                        <thead class="z-10">
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-14">No</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[200px]">Nama Lengkap</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Unit</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[120px] whitespace-nowrap">Tipe Pegawai</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[240px]">Jabatan</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40 whitespace-nowrap">Kontak</th>
                            @if(auth()->user()->role === 'super_admin')
                                <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider min-w-[120px] w-36">ZK ID</th>
                            @endif
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900 px-3 md:px-6 py-3 md:py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @php
                            $colors = ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#14b8a6', '#f43f5e', '#0ea5e9', '#d946ef'];
                        @endphp
                        @forelse($employees as $index => $emp)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors text-left">
                                <td class="px-3 md:px-6 py-3 md:py-4 text-slate-900 dark:text-slate-50 font-medium">{{ $index + 1 }}</td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($emp['photo']))
                                            <img @click='selectedEmp = @json($emp); showEmpDetailModal = true' src="{{ str_contains($emp['photo'], 'photos/') ? rtrim($emp['unit_url'], '/') . '/storage/' . $emp['photo'] : rtrim($emp['unit_url'], '/') . '/storage/photos/' . $emp['photo'] }}" class="w-8 h-8 cursor-pointer rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            @php $avatarColor = $colors[$index % count($colors)]; @endphp
                                            <div @click='selectedEmp = @json($emp); showEmpDetailModal = true' class="w-8 h-8 cursor-pointer rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 shadow-sm" style="background:{{ $avatarColor }}">
                                                {{ strtoupper(substr($emp['raw_name'] ?? $emp['name'], 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col text-left">
                                            @php
                                                $emp['photo_url'] = !empty($emp['photo']) ? rtrim($emp['unit_url'], '/') . '/storage/' . (str_contains($emp['photo'], 'photos/') ? $emp['photo'] : 'photos/' . $emp['photo']) : '';
                                                $emp['nik_nuptk'] = $emp['nik'] ?? $emp['nuptk'] ?? '-';
                                                $emp['unit_name'] = strtoupper($emp['unit_name'] ?? '-');
                                                $emp['zkteco_device_ids'] = !empty($emp['zkteco_uid']) ? \App\Models\EmployeeDeviceMapping::where('zkteco_uid', $emp['zkteco_uid'])->pluck('zkteco_device_id')->toArray() : [];
                                                $color = $colors[$index % count($colors)];
                                            @endphp
                                            <div @click='selectedEmp = @json($emp); showEmpDetailModal = true' class="font-semibold text-slate-900 dark:text-slate-200 inline-block cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:scale-[1.03] transform transition-all duration-200 origin-left">{{ $emp['name'] }}</div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $emp['email'] ?? 'Tidak ada email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                     @if(str_contains(strtolower($emp['unit_name']), 'paud'))
                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border border-teal-200/50 dark:border-teal-800/40 uppercase">{{ $emp['unit_name'] }}</span>
                                     @elseif(str_contains(strtolower($emp['unit_name']), 'sd'))
                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200/50 dark:border-blue-800/40 uppercase">{{ $emp['unit_name'] }}</span>
                                     @elseif(str_contains(strtolower($emp['unit_name']), 'smp'))
                                         <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-200/50 dark:border-purple-800/40 uppercase">{{ $emp['unit_name'] }}</span>
                                     @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 uppercase tracking-wider">{{ $emp['unit_name'] }}</span>
                                    @endif
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-slate-600 dark:text-slate-400 font-medium whitespace-nowrap">
                                    {{ $emp['employee_type']['name'] ?? 'Pegawai' }}
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    <span class="block text-slate-700 dark:text-slate-300 font-medium">{{ $emp['position'] ?? '-' }}</span>
                                    @if(!empty($emp['additional_position']))
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $emp['additional_position'] }}</span>
                                    @endif
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-slate-600 dark:text-slate-400 font-mono text-xs whitespace-nowrap">
                                    {{ $emp['phone'] ?? '-' }}
                                </td>
                                @if(auth()->user()->role === 'super_admin')
                                    <td class="px-3 md:px-6 py-3 md:py-4">
                                        @if(!empty($emp['zkteco_uid']))
                                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-mono text-xs">ID: {{ $emp['zkteco_uid'] }}</span>
                                        @else
                                            <span class="text-slate-400 dark:bg-slate-600">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-3 md:px-6 py-3 md:py-4">
                                    @if(($emp['status'] ?? '') == 'Active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-lime-50 dark:bg-lime-900/30 text-lime-700 dark:text-lime-400 border border-lime-200/50 dark:border-lime-800/40">Aktif</span>
                                    @elseif(($emp['status'] ?? '') == 'Leave')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40">Cuti</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 border border-red-200/50 dark:border-red-800/40">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-3 md:px-6 py-3 md:py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button type="button" @click='selectedEmp = @json($emp); showEditModal = true; if(window.loadEditEmployeeTypes) window.loadEditEmployeeTypes(selectedEmp.unit_id, selectedEmp.employee_type_code || (selectedEmp.employee_type ? selectedEmp.employee_type.code : ""));' class="h-8 px-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </button>
                                        <button type="button" @click='selectedEmp = @json($emp); showDeleteModal = true' class="h-8 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role === 'super_admin' ? 9 : 8 }}" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="users-2" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-xs">Tidak ada data pegawai yang dapat ditampilkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION & COUNT FOOTER -->
            @if($employees instanceof \Illuminate\Pagination\LengthAwarePaginator && $employees->total() > 0)
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-900 bg-slate-50/30 dark:bg-slate-900/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <div>
                        Menampilkan
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $employees->firstItem() }}</span>
                        sampai
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $employees->lastItem() }}</span>
                        dari
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $employees->total() }}</span>
                        pegawai
                    </div>
                    <div class="flex items-center gap-1.5 font-semibold">
                        @if ($employees->onFirstPage())
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20">Sebelumnya</span>
                        @else
                            <a href="{{ $employees->previousPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Sebelumnya</a>
                        @endif

                        <span class="px-3 py-1 font-medium text-slate-700 dark:text-slate-300">
                            Halaman {{ $employees->currentPage() }} dari {{ $employees->lastPage() }}
                        </span>

                        @if ($employees->hasMorePages())
                            <a href="{{ $employees->nextPageUrl() }}" class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 flex items-center justify-center transition-all bg-white dark:bg-slate-900">Berikutnya</a>
                        @else
                            <span class="h-8 px-3 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-600 flex items-center justify-center cursor-not-allowed select-none bg-slate-50 dark:bg-slate-900/20">Berikutnya</span>
                        @endif
                    </div>
                </div>
            @endif
            </section>
        </div>
        <!-- IMPORT MODAL -->
        <div x-show="showImportModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="import-modal-title" 
             role="dialog" 
             aria-modal="true">
             
            <!-- Backdrop -->
            <div x-show="showImportModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
                 @click="showImportModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="showImportModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                    
                    <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                                @if($errors->has('api_error'))
                                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium flex items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span>{{ $errors->first('api_error') }}</span>
                                </div>
                                @endif

                        <div class="p-6 space-y-4">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50" id="import-modal-title">Impor Pegawai dari Excel</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Unggah berkas Excel untuk mengimpor data pegawai secara kolektif.</p>
                                </div>
                                <button type="button" @click="showImportModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-500 cursor-pointer transition-colors">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <!-- Template Format Box -->
                            <div class="space-y-2 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Format Template Pengisian</h4>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Unduh template Excel resmi terlebih dahulu untuk memahami susunan kolom data pegawai yang benar. Pastikan kolom "Unit Sekolah" diisi dengan tepat (paud/sd/smp).
                                </p>
                                <a href="{{ route('employees.download-template') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline pt-1">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    Unduh Template Excel (.xlsx)
                                </a>
                            </div>

                            <!-- File Selection -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pilih File Excel (.xlsx)</label>
                                <input type="file" name="file" accept=".xlsx, .xls" required class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-600 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showImportModal = false" class="h-9 px-4 inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer">
                                Mulai Impor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL PEGAWAI -->
        <div x-show="showEmpDetailModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="detail-modal-title" 
             role="dialog" 
             aria-modal="true">
             
            <!-- Backdrop -->
            <div x-show="showEmpDetailModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
                 @click="showEmpDetailModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="showEmpDetailModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl border border-slate-200 dark:border-slate-800">
                    
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-5 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50" id="detail-modal-title">Profil Pegawai</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Detail informasi dan status pegawai terdaftar.</p>
                            </div>
                            <button type="button" @click="showEmpDetailModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-500 cursor-pointer transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <!-- Photo / Initials -->
                                <div class="shrink-0">
                                    <template x-if="selectedEmp && selectedEmp.photo_url">
                                        <img :src="selectedEmp.photo_url" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-800 shadow-sm">
                                    </template>
                                    <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                        <div class="w-16 h-16 rounded-xl bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold flex items-center justify-center text-2xl uppercase border border-slate-200 dark:border-slate-800 shadow-sm">
                                            <span x-text="selectedEmp ? selectedEmp.name.substring(0,2) : ''"></span>
                                        </div>
                                    </template>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                                    <p class="text-slate-500 dark:text-slate-400 font-mono text-[11px]" x-text="selectedEmp ? 'NIP/NUPTK: ' + (selectedEmp.nik_nuptk || '-') : ''"></p>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 uppercase" x-text="selectedEmp ? selectedEmp.position : ''"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 gap-x-3 text-[11px] pt-4 border-t border-slate-100 dark:border-slate-800 max-h-[50vh] overflow-y-auto pr-2 custom-scrollbar">
                                <div class="col-span-full bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 pb-1.5">Informasi Umum</h5>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Unit Kerja</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 uppercase break-words block" x-text="selectedEmp ? selectedEmp.unit_name : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Email</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-all block" x-text="selectedEmp ? (selectedEmp.email || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jenis Kelamin</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 block" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : (selectedEmp.gender === 'Female' ? 'Perempuan' : '-')) : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Tempat, Tgl Lahir</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? ((selectedEmp.birth_place || '-') + ', ' + (selectedEmp.birth_date || '-')) : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Alamat</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? (selectedEmp.address || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">No. HP</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-all block" x-text="selectedEmp ? (selectedEmp.phone || '-') : '-'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-full bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 pb-1.5">Informasi Status Pegawai</h5>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Status Pegawai</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 block" x-text="selectedEmp ? (selectedEmp.employment_status || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jabatan</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? (selectedEmp.position || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Tugas Tambahan</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? (selectedEmp.additional_position || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Masa Kerja</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 block" x-text="selectedEmp ? (selectedEmp.work_period || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Pangkat/Golongan</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? (selectedEmp.pangkat_golongan || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">ID ZKTeco</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 font-mono break-all block" x-text="selectedEmp ? (selectedEmp.zkteco_uid || '-') : '-'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-full bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 pb-1.5">Informasi Pegawai</h5>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">NIK</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 font-mono break-all block" x-text="selectedEmp ? (selectedEmp.nik || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">NUPTK</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 font-mono break-all block" x-text="selectedEmp ? (selectedEmp.nuptk || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">NIY</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 font-mono break-all block" x-text="selectedEmp ? (selectedEmp.niy || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">No. UKG</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 font-mono break-all block" x-text="selectedEmp ? (selectedEmp.no_ukg || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">NRG</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 font-mono break-all block" x-text="selectedEmp ? (selectedEmp.nrg || '-') : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-span-full bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 pb-1.5">Pendidikan & SK</h5>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-3 gap-x-3 text-[11px]">
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Pendidikan Terakhir</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? (selectedEmp.last_education || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jurusan</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? (selectedEmp.major || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Tgl Mulai Tugas</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 block" x-text="selectedEmp ? (selectedEmp.task_start_date || '-') : '-'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Info SK</span>
                                            <span class="font-bold text-slate-900 dark:text-slate-100 break-words block" x-text="selectedEmp ? ((selectedEmp.last_sk_number || 'Tidak Ada SK') + (selectedEmp.last_sk_date ? ' (' + selectedEmp.last_sk_date + ')' : '')) : '-'"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-span-full mt-2">
                                    <span class="block text-slate-400 text-[9px] uppercase font-semibold">Catatan</span>
                                    <div class="p-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 mt-1" x-text="selectedEmp && selectedEmp.notes ? selectedEmp.notes : 'Tidak ada catatan tambahan.'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex items-center justify-end border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEmpDetailModal = false" class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MODAL TAMBAH PEGAWAI ===== -->
        <div x-show="showCreateModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="create-modal-title" 
             role="dialog" 
             aria-modal="true">
             
            <!-- Backdrop -->
            <div x-show="showCreateModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
                 @click="if('{{ $errors->any() }}') { window.location.href = window.location.pathname; } else { showCreateModal = false; document.getElementById('create-employee-form').reset(); }"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl border border-slate-200 dark:border-slate-800">
                    
                    <div class="flex flex-col max-h-[85vh] text-left">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 shrink-0">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50" id="create-modal-title">Tambah Pegawai Baru</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tambahkan guru atau staf baru ke dalam unit sekolah terkait.</p>
                            </div>
                            <button type="button" @click="if('{{ $errors->any() }}') { window.location.href = window.location.pathname; } else { showCreateModal = false; document.getElementById('create-employee-form').reset(); }" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-500 cursor-pointer transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Scrollable Form Body -->
                        <div class="px-6 py-4 overflow-y-auto custom-scrollbar flex-1">
                            <form id="create-employee-form" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                                @csrf
                                @if($errors->has('api_error'))
                                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium flex items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span>{{ $errors->first('api_error') }}</span>
                                </div>
                                @endif


                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                    <!-- Unit Sekolah -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Unit Sekolah Tujuan <span class="text-rose-500">*</span></label>
                                        <select name="school_unit_id" id="modal_school_unit_id" required class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('school_unit_id') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="">-- Pilih Unit Sekolah --</option>
                                            @foreach($schoolUnits as $unit)
                                                <option value="{{ $unit->id }}" {{ old('school_unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('school_unit_id')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Gelar & Nama -->
                                    <div class="col-span-full grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <!-- Gelar Depan -->
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Gelar Depan</label>
                                            <input type="text" name="front_title" placeholder="Dr., Ir."
                                                value="{{ old('front_title') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('front_title') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('front_title')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                        </div>
                                        <!-- Nama Lengkap -->
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                            <input type="text" name="name" required placeholder="Eko Wibowo"
                                                value="{{ old('name') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('name') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('name')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                        </div>
                                        <!-- Gelar Belakang -->
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Gelar Belakang</label>
                                            <input type="text" name="back_title" placeholder="S.Pd., M.Kom."
                                                value="{{ old('back_title') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('back_title') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('back_title')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat Email</label>
                                        <input type="email" name="email" placeholder="Contoh: nama@sans.dev" 
                                            value="{{ old('email') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('email') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('email')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Tipe Pegawai -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tipe Pegawai <span class="text-rose-500">*</span></label>
                                        <select name="employee_type_code" id="modal_employee_type_code" required disabled class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('employee_type_code') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="">-- Pilih Unit Sekolah Dahulu --</option>
                                        </select>
                                        @error('employee_type_code')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SECTION: DATA DIRI -->
                                    <div class="col-span-full mt-2 mb-1 border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Diri</h4>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tempat Lahir</label>
                                        <input type="text" name="birth_place" value="{{ old('birth_place') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('birth_place') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('birth_place')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('birth_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('birth_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                                        <select name="gender" required class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('gender') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        @error('gender')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat</label>
                                        <input type="text" name="address" value="{{ old('address') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('address') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('address')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">No. HP / WA</label>
                                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('phone') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('phone')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SECTION: DATA KEPEGAWAIAN -->
                                    <div class="col-span-full mt-2 mb-1 border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Kepegawaian &amp; Identitas</h4>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NIK</label>
                                        <input type="text" name="nik" value="{{ old('nik') }}" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('nik') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('nik')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NIY</label>
                                        <input type="text" name="niy" value="{{ old('niy') }}" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('niy') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('niy')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NUPTK</label>
                                        <input type="text" name="nuptk" value="{{ old('nuptk') }}" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('nuptk') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('nuptk')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NO UKG</label>
                                        <input type="text" name="no_ukg" value="{{ old('no_ukg') }}" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('no_ukg') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('no_ukg')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NRG</label>
                                        <input type="text" name="nrg" value="{{ old('nrg') }}" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('nrg') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('nrg')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pangkat / Golongan</label>
                                        <input type="text" name="pangkat_golongan" value="{{ old('pangkat_golongan') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('pangkat_golongan') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('pangkat_golongan')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pendidikan Terakhir</label>
                                        <input type="text" name="last_education" value="{{ old('last_education') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('last_education') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('last_education')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jurusan</label>
                                        <input type="text" name="major" value="{{ old('major') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('major') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('major')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jabatan Utama</label>
                                        <input type="text" name="position" value="{{ old('position') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('position') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('position')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jabatan Tambahan</label>
                                        <input type="text" name="additional_position" value="{{ old('additional_position') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('additional_position') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('additional_position')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                                        <input type="text" name="employment_status" value="{{ old('employment_status') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('employment_status') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('employment_status')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai Tugas</label>
                                        <input type="date" name="task_start_date" value="{{ old('task_start_date') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('task_start_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('task_start_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Diangkat</label>
                                        <input type="date" name="appointment_date" value="{{ old('appointment_date') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('appointment_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('appointment_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal SK Terakhir</label>
                                        <input type="date" name="last_sk_date" value="{{ old('last_sk_date') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('last_sk_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('last_sk_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nomor SK Terakhir</label>
                                        <input type="text" name="last_sk_number" value="{{ old('last_sk_number') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('last_sk_number') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('last_sk_number')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Masa Kerja Golongan</label>
                                        <input type="text" name="work_period" value="{{ old('work_period') }}" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('work_period') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('work_period')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SECTION: SISTEM ABSENSI -->
                                    <div class="col-span-full mt-2 mb-1 border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Sistem Absensi &amp; Foto</h4>
                                    </div>

                                    @if(auth()->user()->role === 'super_admin')
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">ID ZKTeco / PIN Fingerprint</label>
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="zkteco_uid" id="modal_zkteco_uid" value="{{ old('zkteco_uid') }}" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('zkteco_uid') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('zkteco_uid')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                            <button type="button" id="modal_btn_generate_uid" class="px-3 h-9 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors whitespace-nowrap">Buat ID Otomatis</button>
                                        </div>
                                    </div>
                                    <div class="col-span-full">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Sinkronisasi Mesin ZKTeco (Opsional)</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($devices as $dev)
                                            <label class="flex items-center gap-2 p-2 border rounded-lg dark:border-slate-800 bg-slate-50 dark:bg-slate-900 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                                                <input type="checkbox" name="zkteco_device_ids[]" value="{{ $dev->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $dev->name }} <span class="text-slate-400">({{ $dev->sn }})</span></span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Keaktifan <span class="text-rose-500">*</span></label>
                                        <select name="status" required class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('status') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Aktif</option>
                                            <option value="Leave" {{ old('status') == 'Leave' ? 'selected' : '' }}>Cuti</option>
                                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                        @error('status')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-span-full">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Foto Profil</label>
                                        <input type="file" name="photo" accept="image/*"
                                            class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-600 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                                        <span class="text-[10px] text-slate-400 block mt-1">Format: JPG, JPEG, PNG, GIF, SVG. Maksimal 2MB.</span>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex items-center justify-end gap-2.5 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="if('{{ $errors->any() }}') { window.location.href = window.location.pathname; } else { showCreateModal = false; document.getElementById('create-employee-form').reset(); }" class="h-9 px-4 inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">Batal</button>
                        <button type="submit" form="create-employee-form" class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer">Simpan Data Pegawai</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MODAL EDIT PEGAWAI ===== -->
        <div x-show="showEditModal"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="edit-modal-title"
             role="dialog"
             aria-modal="true">

            <!-- Backdrop -->
            <div x-show="showEditModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                 @click="if('{{ $errors->any() }}') { window.location.href = window.location.pathname; } else { showEditModal = false; document.getElementById('edit-employee-form').reset(); }"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl border border-slate-200 dark:border-slate-800">

                    <div class="flex flex-col max-h-[85vh] text-left">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 shrink-0">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50" id="edit-modal-title">Edit Data Pegawai</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui data guru atau staf pada unit sekolah secara terpusat.</p>
                            </div>
                            <button type="button" @click="if('{{ $errors->any() }}') { window.location.href = window.location.pathname; } else { showEditModal = false; document.getElementById('edit-employee-form').reset(); }" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-500 cursor-pointer transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Scrollable Form Body -->
                        <div class="px-6 py-4 overflow-y-auto custom-scrollbar flex-1">
                            <form id="edit-employee-form" method="POST" :action="selectedEmp ? '/employees/' + selectedEmp.unit_id + '/' + selectedEmp.id : ''" enctype="multipart/form-data" class="space-y-4 text-xs">
                                @csrf
                                <input type="hidden" name="_edit_id" :value="selectedEmp ? selectedEmp.id : ''">
                                <input type="hidden" name="_edit_emp_data" :value="selectedEmp ? JSON.stringify(selectedEmp) : ''">
                                @if($errors->has('api_error'))
                                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium flex items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span>{{ $errors->first('api_error') }}</span>
                                </div>
                                @endif

                                @method('PUT')
                                <!-- Foto Profil Preview (Edit Only) -->
                                <div class="col-span-full flex items-center gap-4 p-4 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-900/50 mb-2">
                                    <template x-if="selectedEmp && selectedEmp.photo_url">
                                        <img id="photo-preview" :src="selectedEmp.photo_url" class="w-16 h-16 rounded-full object-cover border-2 border-white dark:border-slate-800 shadow-sm" :alt="selectedEmp.name">
                                    </template>
                                    <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                        <div id="photo-fallback" class="w-16 h-16 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-lg font-bold text-slate-700 dark:text-slate-300 border-2 border-white dark:border-slate-800 uppercase" x-text="selectedEmp && (selectedEmp.raw_name || selectedEmp.name) ? (selectedEmp.raw_name || selectedEmp.name).substring(0, 2).toUpperCase() : 'XX'">
                                        </div>
                                    </template>
                                    <div class="flex flex-col gap-1 text-left">
                                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Foto Profil Saat Ini</h4>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500">Ganti foto dengan mengunggah file baru pada field di bawah.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <!-- Unit Sekolah (Read-only) -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Unit Sekolah</label>
                                        <select disabled class="w-full text-xs h-9 pl-3 pr-8 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 dark:text-slate-400 focus:outline-none cursor-not-allowed">
                                            @foreach($schoolUnits as $unit)
                                                <option value="{{ $unit->id }}" :selected="selectedEmp && selectedEmp.unit_id == {{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Gelar & Nama -->
                                    <div class="col-span-full grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <!-- Gelar Depan -->
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Gelar Depan</label>
                                            <input type="text" name="front_title" :value="selectedEmp ? selectedEmp.front_title : ''" placeholder="Dr., Ir."
                                                class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('front_title') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('front_title')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                        </div>
                                        <!-- Nama Lengkap -->
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                            <input type="text" name="name" required :value="selectedEmp ? selectedEmp.raw_name : ''" placeholder="Eko Wibowo"
                                                class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('name') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('name')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                        </div>
                                        <!-- Gelar Belakang -->
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Gelar Belakang</label>
                                            <input type="text" name="back_title" :value="selectedEmp ? selectedEmp.back_title : ''" placeholder="S.Pd., M.Kom."
                                                class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('back_title') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('back_title')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat Email</label>
                                        <input type="email" name="email" :value="selectedEmp ? selectedEmp.email : ''"
                                            class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('email') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('email')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Tipe Pegawai -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tipe Pegawai <span class="text-rose-500">*</span></label>
                                        <select name="employee_type_code" id="modal_edit_employee_type_code" required class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('employee_type_code') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="">Memuat...</option>
                                        </select>
                                        @error('employee_type_code')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SECTION: DATA DIRI -->
                                    <div class="col-span-full mt-2 mb-1 border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Diri</h4>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tempat Lahir</label>
                                        <input type="text" name="birth_place" :value="selectedEmp ? selectedEmp.birth_place : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('birth_place') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('birth_place')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                        <input type="date" name="birth_date" :value="selectedEmp ? selectedEmp.birth_date : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('birth_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('birth_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                                        <select name="gender" required class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('gender') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="Male" x-bind:selected="selectedEmp && selectedEmp.gender == 'Male'">Laki-laki</option>
                                            <option value="Female" x-bind:selected="selectedEmp && selectedEmp.gender == 'Female'">Perempuan</option>
                                        </select>
                                        @error('gender')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat</label>
                                        <input type="text" name="address" :value="selectedEmp ? selectedEmp.address : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('address') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('address')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">No. HP / WA</label>
                                        <input type="text" name="phone" :value="selectedEmp ? selectedEmp.phone : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('phone') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('phone')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SECTION: DATA KEPEGAWAIAN -->
                                    <div class="col-span-full mt-2 mb-1 border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Data Kepegawaian &amp; Identitas</h4>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NIK</label>
                                        <input type="text" name="nik" :value="selectedEmp ? selectedEmp.nik : ''" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('nik') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('nik')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NIY</label>
                                        <input type="text" name="niy" :value="selectedEmp ? selectedEmp.niy : ''" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('niy') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('niy')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NUPTK</label>
                                        <input type="text" name="nuptk" :value="selectedEmp ? selectedEmp.nuptk : ''" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('nuptk') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('nuptk')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NO UKG</label>
                                        <input type="text" name="no_ukg" :value="selectedEmp ? selectedEmp.no_ukg : ''" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('no_ukg') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('no_ukg')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">NRG</label>
                                        <input type="text" name="nrg" :value="selectedEmp ? selectedEmp.nrg : ''" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('nrg') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('nrg')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pangkat / Golongan</label>
                                        <input type="text" name="pangkat_golongan" :value="selectedEmp ? selectedEmp.pangkat_golongan : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('pangkat_golongan') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('pangkat_golongan')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pendidikan Terakhir</label>
                                        <input type="text" name="last_education" :value="selectedEmp ? selectedEmp.last_education : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('last_education') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('last_education')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jurusan</label>
                                        <input type="text" name="major" :value="selectedEmp ? selectedEmp.major : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('major') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('major')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jabatan Utama</label>
                                        <input type="text" name="position" :value="selectedEmp ? selectedEmp.position : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('position') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('position')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jabatan Tambahan</label>
                                        <input type="text" name="additional_position" :value="selectedEmp ? selectedEmp.additional_position : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('additional_position') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('additional_position')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Kepegawaian</label>
                                        <input type="text" name="employment_status" :value="selectedEmp ? selectedEmp.employment_status : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('employment_status') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('employment_status')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Mulai Tugas</label>
                                        <input type="date" name="task_start_date" :value="selectedEmp ? selectedEmp.task_start_date : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('task_start_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('task_start_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Diangkat</label>
                                        <input type="date" name="appointment_date" :value="selectedEmp ? selectedEmp.appointment_date : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('appointment_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('appointment_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal SK Terakhir</label>
                                        <input type="date" name="last_sk_date" :value="selectedEmp ? selectedEmp.last_sk_date : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('last_sk_date') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('last_sk_date')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nomor SK Terakhir</label>
                                        <input type="text" name="last_sk_number" :value="selectedEmp ? selectedEmp.last_sk_number : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('last_sk_number') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('last_sk_number')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Masa Kerja Golongan</label>
                                        <input type="text" name="work_period" :value="selectedEmp ? selectedEmp.work_period : ''" class="w-full text-xs h-9 px-3 bg-white dark:bg-slate-900 border @error('work_period') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('work_period')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SECTION: SISTEM ABSENSI -->
                                    <div class="col-span-full mt-2 mb-1 border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Sistem Absensi &amp; Foto</h4>
                                    </div>

                                    @if(auth()->user()->role === 'super_admin')
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">ID ZKTeco / PIN Fingerprint</label>
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="zkteco_uid" id="modal_edit_zkteco_uid" :value="selectedEmp ? selectedEmp.zkteco_uid : ''" class="w-full text-xs h-9 px-3 font-mono bg-white dark:bg-slate-900 border @error('zkteco_uid') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                                        @error('zkteco_uid')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                            <button type="button" id="modal_edit_btn_generate_uid" class="px-3 h-9 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors whitespace-nowrap">Buat ID Otomatis</button>
                                        </div>
                                    </div>
                                    <div class="col-span-full">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Sinkronisasi Mesin ZKTeco (Opsional)</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($devices as $dev)
                                            <label class="flex items-center gap-2 p-2 border rounded-lg dark:border-slate-800 bg-slate-50 dark:bg-slate-900 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800">
                                                <input type="checkbox" name="zkteco_device_ids[]" value="{{ $dev->id }}" x-bind:checked="selectedEmp && selectedEmp.zkteco_device_ids && selectedEmp.zkteco_device_ids.includes({{ $dev->id }})" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ $dev->name }} <span class="text-slate-400">({{ $dev->sn }})</span></span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Keaktifan <span class="text-rose-500">*</span></label>
                                        <select name="status" required class="w-full text-xs h-9 pl-3 pr-8 bg-white dark:bg-slate-900 border @error('status') border-rose-500 focus:ring-rose-200 dark:focus:ring-rose-900/40 @else border-slate-200 dark:border-slate-800 @enderror rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 cursor-pointer">
                                            <option value="Active" x-bind:selected="selectedEmp && selectedEmp.status == 'Active'">Aktif</option>
                                            <option value="Leave" x-bind:selected="selectedEmp && selectedEmp.status == 'Leave'">Cuti</option>
                                            <option value="Inactive" x-bind:selected="selectedEmp && selectedEmp.status == 'Inactive'">Nonaktif</option>
                                        </select>
                                        @error('status')
                                            <span class="text-[10px] text-rose-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-span-full">
                                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Foto Profil</label>
                                        <input type="file" name="photo" accept="image/*"
                                            class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none file:mr-4 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-600 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                                        <span class="text-[10px] text-slate-400 block mt-1">Format: JPG, JPEG, PNG, GIF, SVG. Maksimal 2MB.</span>
                                    </div>

                                </div>
                            </form>
                        </div>
                        
                        <!-- Footer Actions -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 shrink-0">
                            <button type="button" @click="if('{{ $errors->any() }}') { window.location.href = window.location.pathname; } else { showEditModal = false; document.getElementById('edit-employee-form').reset(); }" class="h-9 px-4 inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">Batal</button>
                            <button type="submit" form="edit-employee-form" class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MODAL HAPUS PEGAWAI ===== -->
        <div x-show="showDeleteModal"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="delete-modal-title"
             role="dialog"
             aria-modal="true">

            <!-- Backdrop -->
            <div x-show="showDeleteModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                 @click="showDeleteModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="showDeleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-sm border border-slate-200 dark:border-slate-800">

                    <!-- Body -->
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="alert-triangle" class="w-8 h-8 text-rose-600 dark:text-rose-400"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-50 mb-2" id="delete-modal-title">Hapus Pegawai?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                            Anda yakin ingin menghapus <strong class="text-slate-700 dark:text-slate-300" x-text="selectedEmp ? selectedEmp.name : ''"></strong> dari unit <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="selectedEmp ? selectedEmp.unit_name : ''"></span>? Data yang telah dihapus tidak dapat dikembalikan.
                        </p>

                        <div class="flex justify-center gap-3">
                            <button type="button" @click="showDeleteModal = false" class="flex-1 h-10 px-4 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold rounded-xl cursor-pointer transition-colors">
                                Batal
                            </button>
                            <form id="delete-employee-form" data-confirm="false" method="POST" :action="selectedEmp ? '/employees/' + selectedEmp.unit_id + '/' + selectedEmp.id : ''" class="flex-1">
                                @csrf
                                @if($errors->has('api_error'))
                                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 text-xs font-medium flex items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <span>{{ $errors->first('api_error') }}</span>
                                </div>
                                @endif

                                @method('DELETE')
                                <button type="submit" class="w-full h-10 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl cursor-pointer transition-colors flex items-center justify-center gap-2">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    Ya, Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script for modal create employee -->
        <script>
            (function() {
                const unitSel = document.getElementById('modal_school_unit_id');
                const typeSel = document.getElementById('modal_employee_type_code');


                function loadModalEmployeeTypes(unitId, oldSelected = null) {
                    const typeSel = document.getElementById('modal_employee_type_code');
                    if (!typeSel) return;
                    if (!unitId) {
                        typeSel.innerHTML = '<option value="">Pilih Unit Sekolah Dahulu</option>';
                        typeSel.disabled = true;
                        return;
                    }
                    typeSel.disabled = true;
                    typeSel.innerHTML = '<option value="">Memuat tipe pegawai...</option>';
                    fetch(`/school-units/${unitId}/employee-types`)
                        .then(r => r.json())
                        .then(data => {
                            typeSel.innerHTML = '';
                            if (!data.length) {
                                typeSel.innerHTML = '<option value="">Tidak ada tipe tersedia</option>';
                                return;
                            }
                            data.forEach(t => {
                                const o = document.createElement('option');
                                o.value = t.code;
                                o.textContent = `${t.name} (${t.code})`;
                                if (oldSelected && t.code === oldSelected) o.selected = true;
                                typeSel.appendChild(o);
                            });
                            typeSel.disabled = false;
                        })
                        .catch(() => {
                            typeSel.innerHTML = '<option value="">Gagal memuat data</option>';
                        });
                }

                if (unitSel) {
                    unitSel.addEventListener('change', e => loadModalEmployeeTypes(e.target.value));
                    
                    // Trigger on page load if old value exists
                    if (unitSel.value) {
                        loadModalEmployeeTypes(unitSel.value, '{{ old("employee_type_code") }}');
                    }
                }

                // Generate UID Create
                const btnGen = document.getElementById('modal_btn_generate_uid');
                const inputUid = document.getElementById('modal_zkteco_uid');
                if (btnGen && inputUid) {
                    btnGen.addEventListener('click', function() {
                        const unitId = unitSel ? unitSel.value : '';
                        if (!unitId) { alert('Silakan pilih Unit Sekolah terlebih dahulu!'); return; }
                        const orig = btnGen.textContent;
                        btnGen.textContent = 'Memuat...';
                        btnGen.disabled = true;
                        fetch(`/employees/generate-uid/${unitId}`)
                            .then(r => r.json())
                            .then(d => { if (d.status === 'success') inputUid.value = d.next_uid; else alert('Gagal memuat UID'); })
                            .catch(() => alert('Terjadi kesalahan jaringan.'))
                            .finally(() => { btnGen.textContent = orig; btnGen.disabled = false; });
                    });
                }
                
                // Load Employee Types for Edit Modal
                window.loadEditEmployeeTypes = function(unitId, currentTypeCode) {
                    const typeSel = document.getElementById('modal_edit_employee_type_code');
                    if (!typeSel) return;
                    typeSel.disabled = true;
                    typeSel.innerHTML = '<option value="">Memuat...</option>';
                    fetch(`/school-units/${unitId}/employee-types`)
                        .then(r => r.json())
                        .then(data => {
                            typeSel.innerHTML = '';
                            if (!data.length) {
                                typeSel.innerHTML = '<option value="">Tidak ada tipe tersedia</option>';
                                return;
                            }
                            data.forEach(t => {
                                const o = document.createElement('option');
                                o.value = t.code;
                                o.textContent = `${t.name} (${t.code})`;
                                if (t.code === currentTypeCode) o.selected = true;
                                typeSel.appendChild(o);
                            });
                            typeSel.disabled = false;
                        });
                };
                
                // Generate UID Edit
                const btnGenEdit = document.getElementById('modal_edit_btn_generate_uid');
                const inputUidEdit = document.getElementById('modal_edit_zkteco_uid');
                if (btnGenEdit && inputUidEdit) {
                    btnGenEdit.addEventListener('click', function() {
                        // In alpine, selectedEmp is stored on the document context or we can get it from the window if bound, 
                        // but since we are outside Alpine component, we can use the form action to extract unit_id
                        const form = document.getElementById('edit-employee-form');
                        const action = form.getAttribute('action') || form.action;
                        // Expected action format: /employees/{unitId}/{id}
                        const match = action.match(/\/employees\/(\d+)\/\d+/);
                        const unitId = match ? match[1] : '';
                        
                        if (!unitId) { alert('Unit Sekolah tidak terdeteksi!'); return; }
                        const orig = btnGenEdit.textContent;
                        btnGenEdit.textContent = 'Memuat...';
                        btnGenEdit.disabled = true;
                        fetch(`/employees/generate-uid/${unitId}`)
                            .then(r => r.json())
                            .then(d => { if (d.status === 'success') inputUidEdit.value = d.next_uid; else alert('Gagal memuat UID'); })
                            .catch(() => alert('Terjadi kesalahan jaringan.'))
                            .finally(() => { btnGenEdit.textContent = orig; btnGenEdit.disabled = false; });
                    });
                }

                // Reset form when create modal is closed
                const _origToggle = window.toggleModal;
                window.toggleModal = function(modalId) {
                    if (modalId === 'create-employee-modal') {
                        const modal = document.getElementById(modalId);
                        const panel = modal.firstElementChild;
                        if (modal.classList.contains('hidden')) {
                            modal.classList.remove('hidden');
                            setTimeout(() => {
                                modal.style.opacity = '1';
                                panel.style.opacity = '1';
                                panel.style.transform = 'scale(1)';
                            }, 20);
                        } else {
                            panel.style.opacity = '0';
                            panel.style.transform = 'scale(0.95)';
                            modal.style.opacity = '0';
                            setTimeout(() => modal.classList.add('hidden'), 200);
                        }
                    } else {
                        _origToggle(modalId);
                    }
                };
                
                // If edit modal is open on load (due to errors), trigger employee types loading
                if ({{ $errors->any() && old('_edit_id') ? 'true' : 'false' }}) {
                    const oldEditData = {!! old('_edit_emp_data') ?: 'null' !!};
                    if (oldEditData && oldEditData.unit_id) {
                        window.loadEditEmployeeTypes(oldEditData.unit_id, '{{ old("employee_type_code") }}');
                    }
                }
            })();
        </script>
    </div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
    @media (min-width: 768px) {
        .search-container {
            max-width: 280px !important;
        }
    }
    .search-container button[type="submit"]:hover {
        background-color: #0f172a !important; /* bg-slate-900 */
        color: #ffffff !important; /* text-white */
    }
    .dark .search-container button[type="submit"]:hover {
        background-color: #f8fafc !important; /* bg-slate-105 */
        color: #0f172a !important; /* text-slate-900 */
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filter-form');
    const tableContainer = document.getElementById('employee-table-container');

    if (!filterForm || !tableContainer) return;

    // Helper to submit form via event
    window.triggerFilterForm = function(element) {
        const form = element.closest('form');
        if (form) {
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    };

    // Helper to fetch and replace table container content
    async function loadTableContent(url) {
        // Show loading state
        tableContainer.classList.add('opacity-40', 'pointer-events-none');
        
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const htmlText = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlText, 'text/html');
            
            const newTable = doc.getElementById('employee-table-container');
            if (newTable) {
                tableContainer.innerHTML = newTable.innerHTML;
                
                // Update URL in browser address bar without reload
                window.history.pushState({}, '', url);
                
                // Re-initialize Lucide icons for new rows
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }
        } catch (error) {
            console.error('Error filtering table:', error);
            // Fallback: full reload using relative path
            window.location.href = url;
        } finally {
            tableContainer.classList.remove('opacity-40', 'pointer-events-none');
        }
    }

    // Intercept form submit globally (delegated) since form is replaced by AJAX
    document.addEventListener('submit', (e) => {
        const form = e.target.closest('#filter-form');
        if (form) {
            e.preventDefault();
            const formData = new FormData(form);
            const queryParams = new URLSearchParams(formData).toString();
            // Use relative URL to prevent CORS/domain mismatch blocks
            const url = `${window.location.pathname}?${queryParams}`;
            loadTableContent(url);
        }
    });

    // Intercept pagination link clicks and reset filter button clicks
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && link.href) {
            // Check if link is inside the table container (e.g. pagination) or is the reset button
            if (link.closest('#employee-table-container') || link.classList.contains('reset-filter-btn')) {
                // Ensure it is not a delete/edit action or modal trigger link
                if (!link.classList.contains('no-ajax') && !link.hasAttribute('@click') && !link.hasAttribute('x-on:click')) {
                    e.preventDefault();
                    
                    // Convert absolute URL to relative path to bypass CORS blocks
                    try {
                        const targetUrl = new URL(link.href);
                        const relativeUrl = targetUrl.pathname + targetUrl.search;
                        loadTableContent(relativeUrl);
                    } catch (err) {
                        loadTableContent(link.href);
                    }
                }
            }
        }
    });
});
</script>
</x-admin-layout>



