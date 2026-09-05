<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        editId: null,
        editName: '',
        editStatusCode: 'I',
        editTargetUnit: 'all',
        editRequiresAttendance: '1',
        editRequiresApproval: '1',
        editGetsPresenceBonus: '0',
        
        openEdit(type) {
            this.editId = type.id;
            this.editName = type.name;
            this.editStatusCode = type.status_code;
            this.editTargetUnit = type.target_unit;
            this.editRequiresAttendance = type.requires_attendance ? '1' : '0';
            this.editRequiresApproval = type.requires_approval ? '1' : '0';
            this.editGetsPresenceBonus = type.gets_presence_bonus ? '1' : '0';
            this.showEditModal = true;
        },
        resetAdd() {
            this.editName = '';
            this.editStatusCode = 'I';
            this.editTargetUnit = 'all';
            this.editRequiresAttendance = '1';
            this.editRequiresApproval = '1';
            this.editGetsPresenceBonus = '0';
            this.showAddModal = true;
        }
    }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">Tipe Izin</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pusat pengaturan jenis izin, aturan absensi fisik, hak bonus kehadiran, dan distribusi ke unit sekolah.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('leave-types.pull') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-2 border border-slate-200 dark:border-slate-700" title="Tarik seluruh tipe izin dari database SD, SMP, PAUD ke HRD">
                        <i data-lucide="download-cloud" class="w-4 h-4 text-slate-500"></i>
                        <span>Tarik dari Unit</span>
                    </button>
                </form>

                <form action="{{ route('leave-types.push-all') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="h-9 px-3.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-2 border border-indigo-200 dark:border-indigo-800" title="Sinkronkan seluruh tipe izin dari HRD ke semua unit sekolah">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-indigo-500"></i>
                        <span>Dorong ke Unit</span>
                    </button>
                </form>

                <button @click="resetAdd()" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Tipe Izin</span>
                </button>
            </div>
        </header>

        <!-- STATS SECTION -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Types -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Tipe Izin</span>
                    <span class="text-2xl font-bold text-slate-900 dark:text-slate-50 mt-1 block">
                        <span>{{ $totalCount }}</span> <span class="text-xs font-medium text-slate-400">Jenis</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Global Types -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Berlaku Semua Unit</span>
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">
                        <span>{{ $globalCount }}</span> <span class="text-xs font-medium text-slate-400">Global</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="globe" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Bonus Eligible -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Dapat Bonus Kehadiran</span>
                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 block">
                        <span>{{ $bonusCount }}</span> <span class="text-xs font-medium text-slate-400">Tipe</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Auto Approved -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Otomatis Disetujui</span>
                    <span class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1 block">
                        <span>{{ $autoApproveCount }}</span> <span class="text-xs font-medium text-slate-400">By Instruksi</span>
                    </span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
            </div>
        </section>

        <!-- FILTER BAR -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm">
            <form action="{{ route('leave-types.index') }}" method="GET" class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Search -->
                    <div class="relative min-w-[200px] flex-1 md:flex-initial">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tipe izin..." class="w-full text-xs pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <!-- Target Unit Filter -->
                    <select name="unit" onchange="this.form.submit()" class="text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                        <option value="">Semua Lingkup Unit</option>
                        <option value="all" @selected(request('unit') === 'all')>Global (Semua Unit)</option>
                        @foreach($schoolUnits as $u)
                            <option value="{{ strtolower($u->name) }}" @selected(request('unit') === strtolower($u->name))>Unit {{ $u->name }}</option>
                        @endforeach
                    </select>

                    <!-- Status Code Filter -->
                    <select name="status_code" onchange="this.form.submit()" class="text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                        <option value="">Semua Status Absensi</option>
                        <option value="S" @selected(request('status_code') === 'S')>S - Sakit</option>
                        <option value="I" @selected(request('status_code') === 'I')>I - Izin</option>
                        <option value="C" @selected(request('status_code') === 'C')>C - Cuti</option>
                        <option value="H" @selected(request('status_code') === 'H')>H - Hadir / Dinas</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'unit', 'status_code']))
                    <a href="{{ route('leave-types.index') }}" class="text-xs text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </form>
        </section>

        <!-- TABLE SECTION -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3.5 text-left">Nama Tipe Izin</th>
                            <th class="px-6 py-3.5 text-center">Status Absensi</th>
                            <th class="px-6 py-3.5 text-center">Berlaku Di Unit</th>
                            <th class="px-6 py-3.5 text-center">Absensi Fisik</th>
                            <th class="px-6 py-3.5 text-center">Persetujuan</th>
                            <th class="px-6 py-3.5 text-center">Bonus Kehadiran</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($leaveTypes as $type)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <!-- Nama Tipe Izin -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-xs">{{ $type->name }}</span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $type->code }}</span>
                                    </div>
                                </td>

                                <!-- Status Absensi -->
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $badgeColors = [
                                            'S' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/40',
                                            'I' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/40',
                                            'C' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-400 dark:border-purple-900/40',
                                            'H' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/40',
                                        ];
                                        $labelMap = ['S' => 'Sakit (S)', 'I' => 'Izin (I)', 'C' => 'Cuti (C)', 'H' => 'Hadir (H)'];
                                    @endphp
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeColors[$type->status_code] ?? 'bg-slate-50 text-slate-600' }}">
                                        {{ $labelMap[$type->status_code] ?? $type->status_code }}
                                    </span>
                                </td>

                                <!-- Berlaku Di Unit -->
                                <td class="px-6 py-4 text-center">
                                    @if($type->target_unit === 'all' || empty($type->target_unit))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            <i data-lucide="globe" class="w-3 h-3"></i>
                                            Semua Unit
                                        </span>
                                    @else
                                        <div class="flex flex-wrap items-center justify-center gap-1">
                                            @foreach(explode(',', $type->target_unit) as $u)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30 uppercase">
                                                    {{ trim($u) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <!-- Absensi Fisik -->
                                <td class="px-6 py-4 text-center">
                                    @if($type->requires_attendance)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                            Wajib Absen (Parsial)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30">
                                            Bebas Absen (Full Day)
                                        </span>
                                    @endif
                                </td>

                                <!-- Persetujuan -->
                                <td class="px-6 py-4 text-center">
                                    @if($type->requires_approval)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30">
                                            Perlu Persetujuan
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200 dark:border-sky-900/30">
                                            Otomatis Setuju
                                        </span>
                                    @endif
                                </td>

                                <!-- Bonus Kehadiran -->
                                <td class="px-6 py-4 text-center">
                                    @if($type->gets_presence_bonus)
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            Dapat Bonus
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-slate-400 dark:text-slate-500">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            Tanpa Bonus
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openEdit({{ json_encode($type) }})" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition-colors cursor-pointer" title="Edit Tipe Izin">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('leave-types.destroy', $type->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe izin \'{{ $type->name }}\'? Perubahan akan disinkronkan ke unit sekolah.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg text-rose-600 dark:text-rose-400 hover:text-rose-700 transition-colors cursor-pointer" title="Hapus Tipe Izin">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="file-question" class="w-10 h-10 text-slate-300 dark:text-slate-700"></i>
                                        <p class="font-semibold text-xs text-slate-700 dark:text-slate-300">Belum ada tipe izin terdaftar</p>
                                        <p class="text-[11px] text-slate-400 max-w-sm">Klik tombol <strong>Tarik dari Unit</strong> untuk mengimpor otomatis seluruh tipe izin yang sudah berjalan di SD, SMP, dan PAUD.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ADD MODAL -->
        <template x-teleport="body">
            <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto" style="display: none; margin-top: 0px !important; z-index: 9999;" x-transition>
                <div @click.outside="showAddModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden text-left text-xs">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 p-5 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-indigo-500"></i>
                            Tambah Tipe Izin
                        </h3>
                        <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer p-1 rounded-lg">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('leave-types.store') }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Tipe Izin <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Izin Sakit Pribadi" class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Kehadiran <span class="text-rose-500">*</span></label>
                                <select name="status_code" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                                    <option value="I">Izin (I)</option>
                                    <option value="S">Sakit (S)</option>
                                    <option value="C">Cuti (C)</option>
                                    <option value="H">Hadir / Dinas (H)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berlaku Untuk <span class="text-rose-500">*</span></label>
                                <select name="target_unit" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                                    <option value="all">Semua Unit (Global)</option>
                                    @foreach($schoolUnits as $u)
                                        <option value="{{ strtolower($u->name) }}">Khusus Unit {{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Absensi Fisik -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Absensi Fisik di Mesin FP <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_attendance" value="0" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Bebas Absen (Full Day)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_attendance" value="1" checked class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Wajib Absen (Parsial)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Persetujuan -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Persetujuan Pengajuan <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_approval" value="1" checked class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Perlu Persetujuan</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_approval" value="0" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Otomatis Setuju</span>
                                </label>
                            </div>
                        </div>

                        <!-- Bonus Kehadiran -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Hak Bonus Kehadiran <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="gets_presence_bonus" value="1" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Dapat Bonus</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="gets_presence_bonus" value="0" checked class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-slate-500">Tanpa Bonus</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 justify-end">
                            <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm cursor-pointer">
                                Simpan & Distribusikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT MODAL -->
        <template x-teleport="body">
            <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto" style="display: none; margin-top: 0px !important; z-index: 9999;" x-transition>
                <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden text-left text-xs">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 p-5 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                            <i data-lucide="edit" class="w-4 h-4 text-indigo-500"></i>
                            Edit Tipe Izin
                        </h3>
                        <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer p-1 rounded-lg">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form :action="'/leave-types/' + editId" method="POST" class="p-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Tipe Izin <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editName" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Kehadiran <span class="text-rose-500">*</span></label>
                                <select name="status_code" x-model="editStatusCode" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                                    <option value="I">Izin (I)</option>
                                    <option value="S">Sakit (S)</option>
                                    <option value="C">Cuti (C)</option>
                                    <option value="H">Hadir / Dinas (H)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berlaku Untuk <span class="text-rose-500">*</span></label>
                                <select name="target_unit" x-model="editTargetUnit" required class="w-full text-xs px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none cursor-pointer">
                                    <option value="all">Semua Unit (Global)</option>
                                    @foreach($schoolUnits as $u)
                                        <option value="{{ strtolower($u->name) }}">Khusus Unit {{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Absensi Fisik -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Absensi Fisik di Mesin FP <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_attendance" value="0" x-model="editRequiresAttendance" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Bebas Absen (Full Day)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_attendance" value="1" x-model="editRequiresAttendance" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Wajib Absen (Parsial)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Persetujuan -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Persetujuan Pengajuan <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_approval" value="1" x-model="editRequiresApproval" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Perlu Persetujuan</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="requires_approval" value="0" x-model="editRequiresApproval" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Otomatis Setuju</span>
                                </label>
                            </div>
                        </div>

                        <!-- Bonus Kehadiran -->
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Hak Bonus Kehadiran <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="gets_presence_bonus" value="1" x-model="editGetsPresenceBonus" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Dapat Bonus</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 cursor-pointer">
                                    <input type="radio" name="gets_presence_bonus" value="0" x-model="editGetsPresenceBonus" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-slate-500">Tanpa Bonus</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 justify-end">
                            <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm cursor-pointer">
                                Simpan Perubahan & Sinkronkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
</x-admin-layout>
