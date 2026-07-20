<x-admin-layout>
    <div class="p-6 space-y-6">

        <!-- SUCCESS/ERROR ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-955/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 dark:bg-rose-955/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Gagal</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- GREETING / PAGE TITLE -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Log Absensi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Melihat daftar seluruh log absensi yang telah ditarik dari mesin ZKTeco.</p>
            </div>
            <div class="flex items-center gap-2.5 shrink-0" x-data="{ showPullModal: false }">
                <a href="{{ route('attendance-logs.export', request()->all()) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 cursor-pointer" style="background-color: #16a34a; color: white;">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    Export Excel
                </a>

                <button @click="showPullModal = true" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 cursor-pointer">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Tarik Log Manual
                </button>

                <form action="{{ route('attendance-logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan semua data log absensi? Aksi ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 cursor-pointer">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        Kosongkan Log
                    </button>
                </form>

                <!-- PULL MODAL -->
                <div x-show="showPullModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs" style="display: none;" x-transition>
                    <div class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md shadow-2xl p-6 relative text-left">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-900">
                            <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Tarik Log Manual</h3>
                            <button @click="showPullModal = false" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg text-slate-400 hover:text-slate-650 cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="mt-4 space-y-4 text-xs" x-data="{ selectedDevice: '{{ $devices->first()->id ?? '' }}' }">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Pilih Mesin</label>
                                <select x-model="selectedDevice" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                                    @foreach($devices as $device)
                                        <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->ip_address }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-900 gap-2.5">
                                <button @click="showPullModal = false" class="h-9 px-4 bg-slate-55 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-750 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 cursor-pointer">Batal</button>
                                <form :action="`{{ url('zkteco-devices') }}/${selectedDevice}/pull`" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='Menarik...';">
                                    @csrf
                                    <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 cursor-pointer">
                                        Tarik Log Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FILTER CARD -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full text-left">
            <form method="GET" action="{{ route('attendance-logs.index') }}" class="flex flex-col md:flex-row items-end gap-4 text-xs">
                
                <div class="space-y-1 w-full md:w-64">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Mesin</label>
                    <select name="device_id" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        <option value="">Semua Mesin</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" {{ request('device_id') == $device->id ? 'selected' : '' }}>
                                {{ $device->name }} ({{ $device->ip_address }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1 w-full md:w-48">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Tanggal</label>
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if(request()->has('device_id') || request()->has('date'))
                        <a href="{{ route('attendance-logs.index') }}" class="inline-flex items-center justify-center h-9 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <!-- MAIN TABLE CARD -->
        <section class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full p-0">
            
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-555 dark:text-slate-400 uppercase tracking-wider">Nama Mesin</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Waktu Absen</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">State / Type</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Waktu Ditarik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/20 transition-colors text-left">
                                <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-50">{{ $loop->iteration + $logs->firstItem() - 1 }}</td>
                                <td class="px-6 py-4 font-bold text-slate-850 dark:text-slate-100">
                                    {{ $log->device->name ?? 'Mesin Terhapus' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        @if(isset($employeeMap[(string)$log->uid]))
                                            <span class="font-bold text-slate-900 dark:text-slate-100">{{ $employeeMap[(string)$log->uid] }}</span>
                                        @elseif(!empty($log->local_name))
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $log->local_name }}</span>
                                                <span class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 rounded text-[9px] font-bold uppercase tracking-wide">Data Mesin</span>
                                            </div>
                                        @else
                                            <span class="font-bold text-slate-500 dark:text-slate-400">Tidak Dikenal</span>
                                        @endif
                                        <span class="text-[10px] font-mono text-slate-500">UID: {{ $log->uid }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                                    {{ \Carbon\Carbon::parse($log->timestamp)->format('d M Y, H:i:s') }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                    <div class="flex flex-col gap-1">
                                        @php
                                            $stateMap = [
                                                0 => 'Masuk (Check-In)',
                                                1 => 'Pulang (Check-Out)',
                                                2 => 'Mulai Istirahat',
                                                3 => 'Selesai Istirahat',
                                                4 => 'Lembur Masuk',
                                                5 => 'Lembur Pulang',
                                                15 => 'Absen Normal'
                                            ];
                                            $typeMap = [
                                                0 => 'Password',
                                                1 => 'Sidik Jari',
                                                4 => 'Kartu RFID',
                                                15 => 'Wajah',
                                                255 => 'Biometrik / Auto'
                                            ];
                                            $stateLabel = $stateMap[$log->state] ?? 'Status '.$log->state;
                                            $typeLabel = $typeMap[$log->type] ?? 'Mode '.$log->type;
                                        @endphp
                                        <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-md text-[10px] font-semibold truncate">{{ $stateLabel }}</span>
                                        <span class="text-[10px] text-slate-500 font-medium">Via: {{ $typeLabel }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                    {{ $log->created_at->format('d M Y, H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="inbox" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-xs">Tidak ada data log yang ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800/60 bg-slate-50/30 dark:bg-slate-900/10">
                    {{ $logs->links() }}
                </div>
            @endif

        </section>
    </div>
</x-admin-layout>
