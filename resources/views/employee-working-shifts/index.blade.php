<x-admin-layout>
    <style>
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar { 
            width: 14px !important; 
            display: block !important;
        }
        .custom-scrollbar::-webkit-scrollbar-track { 
            background: #f8fafc !important; 
            border-radius: 8px !important;
            border: 1px solid #e2e8f0;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb { 
            background-color: #64748b !important; 
            border-radius: 8px !important; 
            border: 3px solid #f8fafc !important; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { 
            background-color: #475569 !important; 
        }
        .dark .custom-scrollbar {
            scrollbar-color: #475569 #0f172a;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-track { 
            background: #0f172a !important; 
            border: 1px solid #1e293b;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { 
            border: 3px solid #0f172a !important; 
        }
    </style>
    <div class="p-6 space-y-6" x-data="{ 
        showModal: false, 
        selectedBatch: null,
        
        openModal(batch) {
            this.selectedBatch = batch;
            this.showModal = true;
        },

        init() {
            this.$watch('showModal', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        }
    }">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Penjadwalan Shift Pegawai</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur penugasan dan rotasi shift kerja secara kolektif per unit sekolah.</p>
            </div>
            <div>
                <a href="{{ route('employee-working-shifts.create') }}" class="h-9 px-4 inline-flex items-center justify-center bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tugaskan Shift Baru
                </a>
            </div>
        </header>

        <!-- FILTERS & LIST -->
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="p-4 border-b border-slate-100 dark:border-slate-900 flex flex-col sm:flex-row justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/30">
                <form method="GET" action="{{ route('employee-working-shifts.index') }}" class="flex items-center gap-3">
                    <select name="unit_id" class="px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Unit Sekolah</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="h-8 px-4 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-all cursor-pointer">
                        Filter Data
                    </button>
                    @if($selectedUnitId)
                        <a href="{{ route('employee-working-shifts.index') }}" class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 underline">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-4 text-left">Grup Penugasan</th>
                            <th class="px-6 py-4 text-left">Unit Sekolah</th>
                            <th class="px-6 py-4 text-center">Periode Jadwal</th>
                            <th class="px-6 py-4 text-center">Jumlah Pegawai</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="px-6 py-4 text-left">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $batch['shift_name'] }}</span>
                                        <span class="inline-flex w-max px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500">Kode: {{ $batch['shift_code'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $batch['unit_name'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        <span class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($batch['start_date'])->format('d M Y') }}</span>
                                        <i data-lucide="arrow-down" class="w-3 h-3 text-slate-400"></i>
                                        @if($batch['end_date'])
                                            <span class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($batch['end_date'])->format('d M Y') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-200 dark:border-emerald-800">Seterusnya</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="openModal({{ json_encode($batch) }})" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors font-bold text-xs border border-indigo-100 dark:border-indigo-800 cursor-pointer">
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        {{ count($batch['employees']) }} Orang
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('employee-working-shifts.edit-batch', [
                                            'unit_id' => $batch['school_unit_id'],
                                            'shift_id' => $batch['working_shift_id'],
                                            'start_date' => \Carbon\Carbon::parse($batch['start_date'])->format('Y-m-d'),
                                            'end_date' => $batch['end_date'] ? \Carbon\Carbon::parse($batch['end_date'])->format('Y-m-d') : 'null'
                                        ]) }}" class="h-8 px-3 inline-flex items-center justify-center bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-xs font-semibold rounded-lg border border-amber-200/50 dark:border-amber-800/50 transition-all cursor-pointer gap-1.5">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </a>

                                        <form action="{{ route('employee-working-shifts.destroy-batch') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus seluruh penugasan shift pada grup ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="unit_id" value="{{ $batch['school_unit_id'] }}">
                                            <input type="hidden" name="shift_id" value="{{ $batch['working_shift_id'] }}">
                                            <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($batch['start_date'])->format('Y-m-d') }}">
                                            <input type="hidden" name="end_date" value="{{ $batch['end_date'] ? \Carbon\Carbon::parse($batch['end_date'])->format('Y-m-d') : 'null' }}">
                                            
                                            <button type="submit" class="h-8 px-3 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/50 dark:border-rose-900/30 transition-all cursor-pointer gap-1.5">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3 text-slate-500 dark:text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                                            <i data-lucide="calendar-x" class="w-6 h-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum ada jadwal shift</h4>
                                            <p class="text-xs mt-1">Silakan tugaskan shift baru untuk pegawai Anda.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL DETAIL PEGAWAI -->
        <div x-show="showModal" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
             
            <!-- Backdrop -->
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
                 @click="showModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-xl bg-white dark:bg-slate-950 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl border border-slate-200 dark:border-slate-800">
                    
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-5 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50" id="modal-title">Daftar Pegawai Ditugaskan</h3>
                                <p class="text-xs text-slate-500 mt-1">
                                    Shift <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedBatch?.shift_name"></span> - 
                                    <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedBatch?.unit_name"></span>
                                </p>
                            </div>
                            <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-500 cursor-pointer transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        
                        <!-- Search within Modal -->
                        <div class="mb-4" x-data="{ searchModal: '' }">
                            <div class="relative flex items-center">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 pointer-events-none"></i>
                                <input type="text" x-model="searchModal" placeholder="Cari nama pegawai..." class="w-full pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500" style="padding-left: 2.25rem;">
                            </div>

                            <div class="mt-4 custom-scrollbar" style="height: 350px; overflow-y: scroll; overscroll-behavior: contain; padding-right: 8px;">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="emp in (selectedBatch?.employees || []).filter(e => e.name.toLowerCase().includes(searchModal.toLowerCase()) || e.nip.toLowerCase().includes(searchModal.toLowerCase()))" :key="emp.id">
                                        <div class="flex items-start gap-3 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm">
                                            <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                                <span x-text="emp.name.substring(0, 2)"></span>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate" x-text="emp.name"></span>
                                                <span class="text-[10px] font-mono text-slate-500 truncate" x-text="`NIP/NIK: ${emp.nip}`"></span>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div x-show="(selectedBatch?.employees || []).filter(e => e.name.toLowerCase().includes(searchModal.toLowerCase()) || e.nip.toLowerCase().includes(searchModal.toLowerCase())).length === 0" class="col-span-1 sm:col-span-2 py-8 text-center text-sm text-slate-500 italic">
                                        Pegawai tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex items-center justify-between border-t border-slate-200 dark:border-slate-800">
                        <span class="text-xs font-semibold text-slate-500" x-text="`Total: ${selectedBatch?.employees?.length || 0} Pegawai`"></span>
                        <button type="button" class="h-9 px-4 inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold shadow-sm hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer" @click="showModal = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
