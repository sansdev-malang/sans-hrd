<x-admin-layout>
    <div class="p-3.5 sm:p-5 lg:p-6 space-y-3.5 sm:space-y-5 lg:space-y-6 text-left" x-data="{
        showAddModal: false,
        showEditModal: false,
        editId: '',
        editName: '',
        editIp: '',
        editPort: '4370',
        editModelName: 'ZKTeco K40',
        editLocation: '',
        editSn: '',
        editSyncInterval: 5,
        openEditModal(device) {
            this.editId = device.id;
            this.editName = device.name;
            this.editIp = device.ip_address;
            this.editPort = device.port;
            this.editModelName = device.model_name;
            this.editLocation = device.location || '';
            this.editSn = device.sn || '';
            this.editSyncInterval = device.sync_interval || 5;
            this.showEditModal = true;
        },

    }">



        <!-- GREETING / PAGE TITLE -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 sm:gap-3 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-base sm:text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">Mesin & Perangkat Absensi</h2>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400">Kelola konfigurasi, status koneksi, dan sinkronisasi mesin sidik jari/wajah ZKTeco secara terpusat.</p>
            </div>
            <div class="flex items-center gap-2.5 shrink-0">
                <button @click="showAddModal = true"
                    class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 bg-slate-900 dark:bg-slate-50 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-[11px] sm:text-xs font-semibold rounded-lg shadow-sm transition-all duration-100 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>Daftarkan Perangkat</span>
                </button>
            </div>
        </section>

        <!-- ULTRA-COMPACT KPI SUMMARY STRIP -->
        <section class="flex flex-wrap items-center gap-2 sm:gap-2.5 w-full text-xs">
            <!-- Total Perangkat -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center shrink-0">
                    <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                </div>
                <span class="font-medium text-[11px] text-slate-600 dark:text-slate-400">Total Mesin:</span>
                <span class="font-bold text-xs font-mono text-slate-900 dark:text-slate-50">{{ $devices->count() }}</span>
            </div>

            <!-- Online -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <div class="w-5 h-5 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="wifi" class="w-3.5 h-3.5"></i>
                </div>
                <span class="font-medium text-[11px] text-slate-600 dark:text-slate-400">Terkoneksi:</span>
                <span class="font-bold text-xs font-mono text-emerald-600 dark:text-emerald-400">{{ $devices->where('is_online', true)->count() }}</span>
            </div>

            <!-- Offline -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <div class="w-5 h-5 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="wifi-off" class="w-3.5 h-3.5"></i>
                </div>
                <span class="font-medium text-[11px] text-slate-600 dark:text-slate-400">Terputus:</span>
                <span class="font-bold text-xs font-mono text-rose-600 dark:text-rose-400">{{ $devices->where('is_online', false)->count() }}</span>
            </div>
        </section>

        <!-- MAIN MACHINE TABLE CARD -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden transition-all w-full p-6 space-y-6">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Daftar Mesin Terdaftar</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kelola dan pastikan IP address dan port mesin sesuai dengan jaringan lokal sekolah.</p>
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto border border-slate-100 dark:border-slate-900 rounded-xl">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Mesin</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">SN (ADMS)</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40">IP Address</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Port</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Tipe Mesin</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Lokasi / Area</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sync Info</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">Status</th>
                            <th class="px-6 py-4 text-center font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-64 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($devices as $device)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/20 transition-colors text-left">
                                <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-50">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <i data-lucide="fingerprint" class="w-4 h-4 text-slate-400"></i>
                                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $device->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $device->sn ?? '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $device->ip_address }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-mono">{{ $device->port }}</td>
                                <td class="px-6 py-4 font-medium text-slate-600 dark:text-slate-400">{{ $device->model_name }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $device->location ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] text-slate-500">Interval: {{ $device->sync_interval }} menit</span>
                                        <span class="text-[10px] text-slate-500">Terakhir: {{ $device->last_sync_at ? \Carbon\Carbon::parse($device->last_sync_at)->diffForHumans() : 'Belum pernah' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($device->is_online)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30">
                                            <span id="status-indicator-{{ $device->id }}" class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span id="status-text-{{ $device->id }}">Online</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/30">
                                            <span id="status-indicator-{{ $device->id }}" class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span id="status-text-{{ $device->id }}">Offline</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('zkteco-devices.pull', $device->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-1.5 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg text-emerald-600 dark:text-emerald-400 hover:text-emerald-750 transition-colors cursor-pointer" title="Tarik Log Manual (Hanya berfungsi saat development local)">
                                                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('zkteco-devices.force-adms', $device->id) }}" method="POST" onsubmit="return confirm('Paksa mesin ini memuntahkan ulang semua data absennya via ADMS?')">
                                            @csrf
                                            <button type="submit" class="p-1.5 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400 hover:text-blue-750 transition-colors cursor-pointer" title="Paksa Sinkron Ulang ADMS">
                                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <button @click="openEditModal({{ $device }})" class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors cursor-pointer" title="Edit Mesin">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('zkteco-devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus perangkat absensi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg text-rose-600 dark:text-rose-400 hover:text-rose-750 transition-colors cursor-pointer" title="Hapus Mesin">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="fingerprint" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-xs">Belum ada perangkat ZKTeco terdaftar.</p>
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
            <div x-show="showAddModal" @click.self="showAddModal = false" class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none; z-index: 9999;" x-transition>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md shadow-2xl p-6 relative text-left">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-900">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Daftarkan Perangkat Baru</h3>
                    <button @click="showAddModal = false" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('zkteco-devices.store') }}" class="mt-4 space-y-4 text-xs">
                    @csrf
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Perangkat</label>
                        <input type="text" name="name" required placeholder="Contoh: Mesin Utama Lt. 1" 
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Serial Number (SN) - <span class="text-[9px] font-normal normal-case">Wajib untuk ADMS</span></label>
                        <input type="text" name="sn" placeholder="Contoh: C123456789" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">IP Address</label>
                            <input type="text" name="ip_address" required placeholder="192.168.1.201" 
                                class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Port Comm</label>
                            <input type="number" name="port" required value="4370" 
                                class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe / Model</label>
                            <select name="model_name" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                <option value="ZKTeco K40">ZKTeco K40</option>
                                <option value="ZKTeco iFace">ZKTeco iFace</option>
                                <option value="ZKTeco LX50">ZKTeco LX50</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Lokasi Penempatan</label>
                            <input type="text" name="location" placeholder="Pintu Gerbang Utama" 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Interval Sinkronisasi Otomatis (Menit)</label>
                        <input type="number" name="sync_interval" required value="5" min="1"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        <p class="text-[10px] text-slate-500 mt-1">Berapa menit sekali mesin ini akan ditarik log datanya secara otomatis di background.</p>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 cursor-pointer">Batal</button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg cursor-pointer">Simpan Perangkat</button>
                    </div>
                </form>
            </div>
        </div>
        </template>

        <!-- EDIT MODAL -->
        <template x-teleport="body">
            <div x-show="showEditModal" @click.self="showEditModal = false" class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none; z-index: 9999;" x-transition>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md shadow-2xl p-6 relative text-left">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-900">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Edit Perangkat Absensi</h3>
                    <button @click="showEditModal = false" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('zkteco-devices') }}/${editId}`" class="mt-4 space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Perangkat</label>
                        <input type="text" name="name" required x-model="editName"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Serial Number (SN) - <span class="text-[9px] font-normal normal-case">Wajib untuk ADMS</span></label>
                        <input type="text" name="sn" x-model="editSn" placeholder="Contoh: C123456789" 
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">IP Address</label>
                            <input type="text" name="ip_address" required x-model="editIp"
                                class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Port Comm</label>
                            <input type="number" name="port" required x-model="editPort"
                                class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Tipe / Model</label>
                            <select name="model_name" required x-model="editModelName" class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                <option value="ZKTeco K40">ZKTeco K40</option>
                                <option value="ZKTeco iFace">ZKTeco iFace</option>
                                <option value="ZKTeco LX50">ZKTeco LX50</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Lokasi Penempatan</label>
                            <input type="text" name="location" x-model="editLocation"
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Interval Sinkronisasi Otomatis (Menit)</label>
                        <input type="number" name="sync_interval" required min="1" x-model="editSyncInterval"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 cursor-pointer">Batal</button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
        </template>

    </div>
</x-admin-layout>
