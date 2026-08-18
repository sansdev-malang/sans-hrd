<x-admin-layout>
    <div class="p-6" x-data="rawLogsData">
        <!-- HEADER SECTION -->
        <section class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Log Absensi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Data absensi murni yang ditarik/dipush dari mesin ZKTeco.</p>
        </section>

        <!-- ALERTS -->
        @if($errors->any())
            <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-400 text-xs rounded-xl">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MAIN CARD -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            
            <!-- FILTER & SEARCH BAR -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <form action="{{ route('raw-attendance-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <div x-data="{ searchVal: '{{ $search }}' }" class="flex items-center flex-1 min-w-[200px] search-container bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-inner focus-within:ring-0 focus-within:border-slate-300 dark:focus-within:border-slate-700">
                        <input type="text" name="search" x-model="searchVal" placeholder="Cari nama atau UID..."
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

                    <!-- UNIT FILTER -->
                    <select name="unit_id" x-model="filterUnitId" class="h-9 px-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-slate-900 dark:text-slate-100 cursor-pointer">
                        <option value="">Semua Unit</option>
                        @foreach($schoolUnits as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>

                    <!-- JABATAN FILTER -->
                    <select name="position" x-model="filterPosition" class="h-9 px-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-slate-900 dark:text-slate-100 cursor-pointer">
                        <option value="">Semua Jabatan</option>
                        <template x-for="pos in filteredPositions" :key="pos">
                            <option :value="pos" x-text="pos"></option>
                        </template>
                    </select>

                    <!-- DATE RANGE FILTER -->
                    <div class="flex items-center gap-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2 shadow-inner focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 transition-colors">
                        <input type="date" name="start_date" value="{{ $startDate }}" placeholder="Mulai" class="h-9 px-1 text-xs bg-transparent border-0 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 cursor-pointer">
                        <span class="text-slate-400 text-xs">s/d</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" placeholder="Selesai" class="h-9 px-1 text-xs bg-transparent border-0 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-0 cursor-pointer">
                    </div>

                    <!-- BUTTONS -->
                    <div class="flex items-center gap-2">
                        <button type="submit" class="h-9 px-4 bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 font-semibold text-xs rounded-lg transition-colors flex items-center justify-center min-w-[90px] cursor-pointer">
                            Filter
                        </button>
                        @if($search || $unitId || $position || $startDate || $endDate)
                        <a href="{{ route('raw-attendance-logs.index') }}" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 text-slate-650 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg transition-colors flex items-center justify-center">
                            Reset
                        </a>
                        @endif
                    </div>
                </form>

                <div class="flex items-center gap-2 mt-4 md:mt-0">
                    <a href="{{ route('raw-attendance-logs.export', request()->query()) }}" data-no-loader class="h-9 px-4 bg-teal-600 hover:bg-teal-700 text-white font-medium text-xs rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                        <i data-lucide="file-down" class="w-4 h-4"></i>
                        Ekspor Excel
                    </a>
                    <button @click="showImportModal = true" class="h-9 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                        <i data-lucide="file-up" class="w-4 h-4"></i>
                        Import Excel
                    </button>
                    <button @click="openCreateModal" class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-lg transition-colors flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Tambah Log Manual
                    </button>
                </div>
            </div>

            <!-- TABLE DESKTOP (FREEZE HEADER / SCROLLABLE ROWS) -->
            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-290px)] relative">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400 border-collapse">
                    <thead class="sticky top-0 bg-slate-50/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 z-10 shadow-[0_1px_0_0_rgba(0,0,0,0.05)] dark:shadow-[0_1px_0_0_rgba(255,255,255,0.05)]">
                        <tr>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider w-16">ID Log</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">UID Pegawai</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">Waktu (Timestamp)</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">State</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">Tipe</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider">Mesin Sumber</th>
                            <th class="py-3 px-6 font-semibold text-xs text-slate-500 uppercase tracking-wider text-right w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-6">{{ $log->id }}</td>
                            <td class="py-3 px-6">
                                <div class="font-medium text-slate-900 dark:text-slate-200">
                                    {{ $employeeMap[$log->uid] ?? 'Tidak Dikenal' }}
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">UID: {{ $log->uid }}</div>
                            </td>
                            <td class="py-3 px-6">{{ $log->timestamp }}</td>
                            <td class="py-3 px-6">
                                @php
                                    $stateLabel = match((int)$log->state) {
                                        0 => 'Check-In',
                                        1 => 'Check-Out',
                                        2 => 'Break-Out',
                                        3 => 'Break-In',
                                        4 => 'Overtime-In',
                                        5 => 'Overtime-Out',
                                        15 => 'Otomatis', // ZKBio Time API default
                                        255 => 'Otomatis', // ADMS default
                                        default => 'Auto ('.$log->state.')'
                                    };
                                @endphp
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded text-[10px] font-semibold">
                                    {{ $stateLabel }}
                                </span>
                            </td>
                            <td class="py-3 px-6">
                                @php
                                    $typeLabel = match((int)$log->type) {
                                        0 => 'Password',
                                        1 => 'Wajah', // ZKTeco EFace10 firmware default
                                        4 => 'Kartu RFID',
                                        15 => 'Wajah',
                                        255 => 'Wajah (via API)', // Hardcoded in PullZktecoLogs
                                        default => 'Sensor Lain ('.$log->type.')'
                                    };
                                @endphp
                                {{ $typeLabel }}
                            </td>
                            <td class="py-3 px-6">
                                @if($log->device)
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full {{ $log->device->is_online ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                                        <span class="text-xs font-medium">{{ $log->device->name }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 ml-3.5">{{ $log->device->sn }}</div>
                                @else
                                    <span class="text-slate-400 italic">Mesin Dihapus/Tidak Diketahui</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click='selectedLog = @json($log); showEditModal = true' class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors cursor-pointer" title="Edit Log">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('raw-attendance-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus log absensi ini?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950 rounded-lg text-rose-600 hover:text-rose-700 transition-colors cursor-pointer" title="Hapus Log">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <i data-lucide="database" class="w-8 h-8 mx-auto mb-3 opacity-50"></i>
                                Belum ada log absensi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                    {{ $logs->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            @endif

        </section>

        <!-- MODAL TAMBAH LOG MANUAL -->
        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" style="margin-top: 0px !important;">
            <div @click.away="showCreateModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all scale-100 duration-200 text-left">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tambah Log Absensi Manual</h3>
                    <button type="button" @click="showCreateModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form action="{{ route('raw-attendance-logs.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                    @csrf
                    
                    <!-- Cari & Filter Pegawai -->
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800/80 space-y-2">
                        <span class="block font-bold text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">Filter Pegawai</span>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Unit -->
                            <div>
                                <label class="block text-[9px] font-semibold text-slate-400 dark:text-slate-400 uppercase mb-1">Unit Sekolah</label>
                                <select x-model="createUnit" class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer text-slate-900 dark:text-slate-100">
                                    <option value="">Semua Unit</option>
                                    @foreach($schoolUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Jabatan -->
                            <div>
                                <label class="block text-[9px] font-semibold text-slate-400 dark:text-slate-400 uppercase mb-1">Jabatan</label>
                                <select x-model="createPosition" class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer text-slate-900 dark:text-slate-100">
                                    <option value="">Semua Jabatan</option>
                                    <template x-for="pos in createFilteredPositions" :key="pos">
                                        <option :value="pos" x-text="pos"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Pegawai (Searchable Select Component) -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Pilih Pegawai <span class="text-rose-500">*</span></label>
                        
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <!-- Select Trigger Button -->
                            <button type="button" @click="open = !open" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-left flex justify-between items-center text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                                <span x-text="createLabel" class="truncate text-xs"></span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <input type="hidden" name="uid" :value="createUid" required>

                            <!-- Dropdown Menu -->
                            <div x-show="open" style="display: none;" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <!-- Search input inside dropdown -->
                                <div class="p-2 border-b border-slate-100 dark:border-slate-800 sticky top-0 bg-white dark:bg-slate-900">
                                    <input type="text" x-model="createSearch" placeholder="Ketik nama atau UID..." class="w-full h-8 px-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-900 dark:text-slate-100">
                                </div>
                                
                                <!-- Options List -->
                                <div class="py-1">
                                    <template x-for="emp in filteredCreateEmployees" :key="emp.zkteco_uid">
                                        <button type="button" @click="createUid = emp.zkteco_uid; createLabel = `${emp.name} (UID: ${emp.zkteco_uid})`; open = false; createSearch = ''" class="w-full px-3 py-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs block transition-colors">
                                            <span class="font-medium" x-text="emp.name"></span>
                                            <span class="text-[10px] text-slate-400 block" x-text="`UID: ${emp.zkteco_uid} | ${emp.position || emp.subject_position || '-'}`"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredCreateEmployees.length === 0" class="px-3 py-3 text-slate-400 dark:text-slate-500 italic text-center text-[11px]">
                                        Pegawai tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400 block" x-text="`Total terfilter: ${filteredCreateEmployees.length} pegawai.`"></span>
                    </div>

                    <!-- Mesin Sumber -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Mesin Sumber <span class="text-rose-500">*</span></label>
                        <select name="zkteco_device_id" required class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-950 text-slate-400">-- Pilih Mesin --</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->id }}" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">{{ $dev->name }} (SN: {{ $dev->sn }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Waktu Absen -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Waktu Absen (Timestamp) <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="timestamp" required value="{{ date('Y-m-d\TH:i') }}" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 dark:[color-scheme:dark] border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- State -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Status Kehadiran (State) <span class="text-rose-500">*</span></label>
                            <select name="state" required class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 cursor-pointer">
                                <option value="0" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Check-In (Masuk)</option>
                                <option value="1" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Check-Out (Pulang)</option>
                                <option value="2" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Mulai Istirahat (Break-Out)</option>
                                <option value="3" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Selesai Istirahat (Break-In)</option>
                                <option value="4" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Mulai Lembur (Overtime-In)</option>
                                <option value="5" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Selesai Lembur (Overtime-Out)</option>
                                <option value="255" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Otomatis (ADMS / Mesin)</option>
                            </select>
                        </div>

                        <!-- Type -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Metode Verifikasi (Type) <span class="text-rose-500">*</span></label>
                            <select name="type" required class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 cursor-pointer">
                                <option value="15" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Wajah (Face Recognition)</option>
                                <option value="3" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Sidik Jari (Fingerprint)</option>
                                <option value="4" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Kartu RFID (Card)</option>
                                <option value="0" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Password (PIN)</option>
                                <option value="255" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Wajah (via API)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Local Name -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Nama Lokal di Mesin (Opsional)</label>
                        <input type="text" name="local_name" placeholder="misal: Hadi" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-950 -mx-6 -mb-6 px-6 py-4 mt-6 rounded-b-xl">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-855 text-slate-700 dark:text-slate-300 bg-transparent text-xs font-bold rounded-lg cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg cursor-pointer">Simpan Log</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EDIT LOG -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" style="margin-top: 0px !important;">
            <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all scale-100 duration-200 text-left">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Log Absensi</h3>
                    <button type="button" @click="showEditModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form :action="selectedLog ? `{{ url('raw-attendance-logs') }}/${selectedLog.id}` : '#'" method="POST" class="p-6 space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    
                    <!-- Cari & Filter Pegawai -->
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-200 dark:border-slate-800/80 space-y-2">
                        <span class="block font-bold text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">Filter Pegawai</span>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Unit -->
                            <div>
                                <label class="block text-[9px] font-semibold text-slate-400 dark:text-slate-400 uppercase mb-1">Unit Sekolah</label>
                                <select x-model="editUnit" class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer text-slate-900 dark:text-slate-100">
                                    <option value="">Semua Unit</option>
                                    @foreach($schoolUnits as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Jabatan -->
                            <div>
                                <label class="block text-[9px] font-semibold text-slate-400 dark:text-slate-400 uppercase mb-1">Jabatan</label>
                                <select x-model="editPosition" class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer text-slate-900 dark:text-slate-100">
                                    <option value="">Semua Jabatan</option>
                                    <template x-for="pos in editFilteredPositions" :key="pos">
                                        <option :value="pos" x-text="pos"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Pegawai (Searchable Select Component) -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Pilih Pegawai <span class="text-rose-500">*</span></label>
                        
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <!-- Select Trigger Button -->
                            <button type="button" @click="open = !open" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-left flex justify-between items-center text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                                <span x-text="editLabel" class="truncate text-xs"></span>
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <input type="hidden" name="uid" :value="editUid" required>

                            <!-- Dropdown Menu -->
                            <div x-show="open" style="display: none;" class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <!-- Search input inside dropdown -->
                                <div class="p-2 border-b border-slate-100 dark:border-slate-800 sticky top-0 bg-white dark:bg-slate-900">
                                    <input type="text" x-model="editSearch" placeholder="Ketik nama atau UID..." class="w-full h-8 px-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-900 dark:text-slate-100">
                                </div>
                                
                                <!-- Options List -->
                                <div class="py-1">
                                    <template x-for="emp in filteredEditEmployees" :key="emp.zkteco_uid">
                                        <button type="button" @click="editUid = emp.zkteco_uid; editLabel = `${emp.name} (UID: ${emp.zkteco_uid})`; open = false; editSearch = ''" class="w-full px-3 py-1.5 text-left hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs block transition-colors">
                                            <span class="font-medium" x-text="emp.name"></span>
                                            <span class="text-[10px] text-slate-400 block" x-text="`UID: ${emp.zkteco_uid} | ${emp.position || emp.subject_position || '-'}`"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredEditEmployees.length === 0" class="px-3 py-3 text-slate-400 dark:text-slate-500 italic text-center text-[11px]">
                                        Pegawai tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-400 block" x-text="`Total terfilter: ${filteredEditEmployees.length} pegawai.`"></span>
                    </div>

                    <!-- Mesin Sumber -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Mesin Sumber <span class="text-rose-500">*</span></label>
                        <select name="zkteco_device_id" required :value="selectedLog ? selectedLog.zkteco_device_id : ''" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-105">-- Pilih Mesin --</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->id }}" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">{{ $dev->name }} (SN: {{ $dev->sn }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Waktu Absen -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Waktu Absen (Timestamp) <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="timestamp" required :value="selectedLog ? selectedLog.timestamp.replace(' ', 'T').substring(0, 16) : ''" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 dark:[color-scheme:dark] border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- State -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Status Kehadiran (State) <span class="text-rose-500">*</span></label>
                            <select name="state" required :value="selectedLog ? selectedLog.state : '0'" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 cursor-pointer">
                                <option value="0" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Check-In (Masuk)</option>
                                <option value="1" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Check-Out (Pulang)</option>
                                <option value="2" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Mulai Istirahat (Break-Out)</option>
                                <option value="3" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Selesai Istirahat (Break-In)</option>
                                <option value="4" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Mulai Lembur (Overtime-In)</option>
                                <option value="5" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Selesai Lembur (Overtime-Out)</option>
                                <option value="255" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Otomatis (ADMS / Mesin)</option>
                            </select>
                        </div>

                        <!-- Type -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-305">Metode Verifikasi (Type) <span class="text-rose-500">*</span></label>
                            <select name="type" required :value="selectedLog ? selectedLog.type : '15'" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 cursor-pointer">
                                <option value="15" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Wajah (Face Recognition)</option>
                                <option value="3" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Sidik Jari (Fingerprint)</option>
                                <option value="4" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Kartu RFID (Card)</option>
                                <option value="0" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Password (PIN)</option>
                                <option value="255" class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">Wajah (via API)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Local Name -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">Nama Lokal di Mesin (Opsional)</label>
                        <input type="text" name="local_name" :value="selectedLog ? selectedLog.local_name : ''" placeholder="misal: Hadi" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500/50">
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-950 -mx-6 -mb-6 px-6 py-4 mt-6 rounded-b-xl">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-transparent text-xs font-bold rounded-lg cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL IMPORT EXCEL -->
        <div x-show="showImportModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" style="margin-top: 0px !important;">
            <div @click.away="showImportModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100 duration-200 text-left">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Import Log dari Excel</h3>
                    <button type="button" @click="showImportModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <form action="{{ route('raw-attendance-logs.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 text-xs">
                    @csrf
                    
                    <div class="space-y-3">
                        <p class="text-slate-600 dark:text-slate-400">Pastikan Anda menggunakan format Excel yang benar. Klik tombol di bawah untuk mengunduh template terbaru beserta daftar referensi UID/ID yang dibutuhkan.</p>
                        <a href="{{ route('raw-attendance-logs.template') }}" data-no-loader class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg transition-colors border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Download Template & Referensi
                        </a>
                    </div>

                    <div class="space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <label class="block font-bold text-slate-700 dark:text-slate-300">File Excel (.xlsx, .xls) <span class="text-rose-500">*</span></label>
                        <input type="file" name="file" accept=".xlsx, .xls" required class="w-full h-10 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-500/50 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 cursor-pointer">
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2.5 bg-slate-50/50 dark:bg-slate-950 -mx-6 -mb-6 px-6 py-4 mt-6 rounded-b-xl">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-transparent text-xs font-bold rounded-lg cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg cursor-pointer">Import Data</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rawLogsData', () => ({
            showCreateModal: false,
            showEditModal: false,
            showImportModal: false,
            selectedLog: null,
            employees: @json($employees),
            
            // Filters
            filterUnitId: '{{ $unitId }}',
            filterPosition: '{{ $position }}',
            unitPositions: @json($unitPositions),
            allPositions: @json($positions),

            get filteredPositions() {
                if (this.filterUnitId && this.unitPositions[this.filterUnitId]) {
                    return this.unitPositions[this.filterUnitId];
                }
                return this.allPositions;
            },

            // Create fields
            createSearch: '',
            createUnit: '',
            createPosition: '',
            createUid: '',
            createLabel: '-- Pilih Pegawai --',

            get createFilteredPositions() {
                if (this.createUnit && this.unitPositions[this.createUnit]) {
                    return this.unitPositions[this.createUnit];
                }
                return this.allPositions;
            },

            // Edit fields
            editSearch: '',
            editUnit: '',
            editPosition: '',
            editUid: '',
            editLabel: '-- Pilih Pegawai --',

            get editFilteredPositions() {
                if (this.editUnit && this.unitPositions[this.editUnit]) {
                    return this.unitPositions[this.editUnit];
                }
                return this.allPositions;
            },

            init() {
                // Reset create fields on modal open
                this.$watch('showCreateModal', (val) => {
                    if (val) {
                        this.createSearch = '';
                        this.createUnit = '';
                        this.createPosition = '';
                        this.createUid = '';
                        this.createLabel = '-- Pilih Pegawai --';
                    }
                });

                // Populate edit fields when selectedLog changes
                this.$watch('selectedLog', (log) => {
                    if (log) {
                        this.editSearch = '';
                        this.editUnit = '';
                        this.editPosition = '';
                        this.editUid = log.uid;
                        const emp = this.employees.find(e => String(e.zkteco_uid) === String(log.uid));
                        this.editLabel = emp ? `${emp.name} (UID: ${emp.zkteco_uid})` : `UID: ${log.uid}`;
                    } else {
                        this.editUid = '';
                        this.editLabel = '-- Pilih Pegawai --';
                    }
                });
            },

            openCreateModal() {
                this.showCreateModal = true;
            },

            get filteredCreateEmployees() {
                return this.employees.filter(emp => {
                    if (!emp.zkteco_uid) return false;
                    if (this.createSearch) {
                        const s = this.createSearch.toLowerCase();
                        if (!emp.name.toLowerCase().includes(s) && !String(emp.zkteco_uid).includes(s)) return false;
                    }
                    if (this.createUnit && String(emp.unit_id) !== String(this.createUnit)) return false;
                    if (this.createPosition && (emp.position || emp.subject_position || '') !== this.createPosition) return false;
                    return true;
                });
            },

            get filteredEditEmployees() {
                return this.employees.filter(emp => {
                    if (!emp.zkteco_uid) return false;
                    if (this.editSearch) {
                        const s = this.editSearch.toLowerCase();
                        if (!emp.name.toLowerCase().includes(s) && !String(emp.zkteco_uid).includes(s)) return false;
                    }
                    if (this.editUnit && String(emp.unit_id) !== String(this.editUnit)) return false;
                    if (this.editPosition && (emp.position || emp.subject_position || '') !== this.editPosition) return false;
                    return true;
                });
            }
        }));
    });
</script>
