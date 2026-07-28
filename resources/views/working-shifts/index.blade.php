<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        editId: null,
        editName: '',
        editCode: '',
        editShortCode: '',
        editIsShift: false,
        editDescription: '',
        days: [
            { name: 'Minggu', start_time: '', end_time: '', is_off: true },
            { name: 'Senin', start_time: '07:00', end_time: '15:30', is_off: false },
            { name: 'Selasa', start_time: '07:00', end_time: '15:30', is_off: false },
            { name: 'Rabu', start_time: '07:00', end_time: '15:30', is_off: false },
            { name: 'Kamis', start_time: '07:00', end_time: '15:30', is_off: false },
            { name: 'Jumat', start_time: '07:00', end_time: '15:30', is_off: false },
            { name: 'Sabtu', start_time: '07:30', end_time: '12:00', is_off: false }
        ],
        openEdit(shift) {
            this.editId = shift.id;
            this.editName = shift.name;
            this.editCode = shift.code;
            this.editShortCode = shift.short_code || '';
            this.editIsShift = !!shift.is_shift;
            this.editDescription = shift.description || '';
            
            // Map details
            this.days = [
                { name: 'Minggu', start_time: '', end_time: '', is_off: true },
                { name: 'Senin', start_time: '', end_time: '', is_off: true },
                { name: 'Selasa', start_time: '', end_time: '', is_off: true },
                { name: 'Rabu', start_time: '', end_time: '', is_off: true },
                { name: 'Kamis', start_time: '', end_time: '', is_off: true },
                { name: 'Jumat', start_time: '', end_time: '', is_off: true },
                { name: 'Sabtu', start_time: '', end_time: '', is_off: true }
            ];
            
            shift.details.forEach(d => {
                let idx = d.day_of_week;
                if (this.days[idx]) {
                    this.days[idx].start_time = d.start_time ? d.start_time.substring(0, 5) : '';
                    this.days[idx].end_time = d.end_time ? d.end_time.substring(0, 5) : '';
                    this.days[idx].is_off = !!d.is_off;
                }
            });
            
            this.showEditModal = true;
        },
        resetAdd() {
            this.days = [
                { name: 'Minggu', start_time: '', end_time: '', is_off: true },
                { name: 'Senin', start_time: '07:00', end_time: '15:30', is_off: false },
                { name: 'Selasa', start_time: '07:00', end_time: '15:30', is_off: false },
                { name: 'Rabu', start_time: '07:00', end_time: '15:30', is_off: false },
                { name: 'Kamis', start_time: '07:00', end_time: '15:30', is_off: false },
                { name: 'Jumat', start_time: '07:00', end_time: '15:30', is_off: false },
                { name: 'Sabtu', start_time: '07:30', end_time: '12:00', is_off: false }
            ];
            this.showAddModal = true;
        }
    }">



        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Pengaturan Shift & Jam Kerja</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur template jam kerja masuk dan pulang bagi guru dan karyawan (shift maupun non-shift) secara terpusat.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('working-shifts.sync') }}" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Sync Ulang ke Unit
                </a>
                <button @click="resetAdd()" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Shift Baru
                </button>
            </div>
        </header>

        <!-- CARDS GRID -->
        <section class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 text-left">
            @forelse($shifts as $shift)
                <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $shift->name }}</h4>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono border border-slate-200 dark:border-slate-800 px-1.5 py-0.5 rounded bg-slate-50 dark:bg-slate-900/50">CODE: <strong class="text-slate-700 dark:text-slate-300">{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</strong></span>
                            </div>
                            @if($shift->is_shift)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30 uppercase">Shift</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-900 text-slate-750 dark:text-slate-350 border border-slate-200/50 dark:border-slate-800 uppercase">Non-Shift</span>
                            @endif
                        </div>

                        @if($shift->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-normal">{{ $shift->description }}</p>
                        @endif

                        <!-- Day Schedules -->
                        <div class="space-y-1.5 pt-3 border-t border-slate-100 dark:border-slate-900">
                            @foreach($shift->details->sortBy('day_of_week') as $detail)
                                @php
                                    $daysName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    $dayName = $daysName[$detail->day_of_week] ?? '';
                                @endphp
                                <div class="flex justify-between items-center text-xs py-0.5">
                                    <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $dayName }}</span>
                                    @if($detail->is_off)
                                        <span class="text-[10px] text-rose-500 dark:text-rose-400 font-semibold uppercase">Libur</span>
                                    @else
                                        <span class="font-mono text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 px-1.5 py-0.5 rounded border border-slate-100 dark:border-slate-900">
                                            {{ substr($detail->start_time, 0, 5) }} - {{ substr($detail->end_time, 0, 5) }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex gap-2.5 mt-5 border-t border-slate-50 dark:border-slate-900/60 pt-4 justify-end">
                        <button @click="openEdit({{ json_encode($shift) }})" class="h-8 px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                            Edit Shift
                        </button>
                        <form action="{{ route('working-shifts.destroy', $shift->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template shift kerja ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-8 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1.5">
                                <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-950">
                    <i data-lucide="clock" class="w-8 h-8 mx-auto text-slate-400 mb-2"></i>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada template shift kerja yang terdaftar.</p>
                </div>
            @endforelse
        </section>

        <!-- ADD MODAL -->
        <template x-teleport="body">
            <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showAddModal = false" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-950 dark:text-slate-50">Tambah Template Shift Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('working-shifts.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Nama Shift</label>
                        <input type="text" name="name" required placeholder="Contoh: Salehmart Shift 1" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Kode Unik Shift</label>
                            <input type="text" name="code" required placeholder="Contoh: salehmart_s1" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Kode Singkat (Max 5)</label>
                            <input type="text" name="short_code" maxlength="5" placeholder="Cth: S1, P, M" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono uppercase">
                        </div>
                        <div class="flex items-center pt-2 col-span-2">
                            <input type="checkbox" id="is_shift" name="is_shift" value="1" class="rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 w-4 h-4">
                            <label for="is_shift" class="ml-2 font-semibold text-slate-750 dark:text-slate-300">Merupakan Shift Bergulir (Gantian)</label>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2" placeholder="Keterangan singkat jam kerja..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Day Configs -->
                    <div>
                        <label class="block font-bold text-slate-750 dark:text-slate-300 mb-2 uppercase tracking-wide text-[10px]">Konfigurasi Hari & Jam Kerja</label>
                        <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-900">
                            <template x-for="(day, index) in days" :key="index">
                                <div class="grid grid-cols-12 items-center gap-3 border-b border-slate-100 dark:border-slate-900 last:border-0 pb-3 last:pb-0">
                                    <div class="col-span-3 font-semibold text-slate-700 dark:text-slate-350" x-text="day.name"></div>
                                    
                                    <div class="col-span-3 flex items-center">
                                        <input type="checkbox" :name="`days[${index}][is_off]`" value="1" x-model="day.is_off" class="rounded border-slate-300 text-rose-500 w-4 h-4 focus:ring-rose-400">
                                        <span class="ml-2 text-rose-600 dark:text-rose-450 font-medium">Libur</span>
                                    </div>
                                    
                                    <div class="col-span-3">
                                        <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" :disabled="day.is_off" class="w-full px-2 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded text-center disabled:opacity-50 font-mono">
                                    </div>
                                    
                                    <div class="col-span-3">
                                        <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" :disabled="day.is_off" class="w-full px-2 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded text-center disabled:opacity-50 font-mono">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Shift
                        </button>
                    </div>
                </form>
            </div>
        </template>

        <!-- EDIT MODAL -->
        <template x-teleport="body">
            <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-950 dark:text-slate-50">Edit Template Shift</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('working-shifts') }}/${editId}`" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Nama Shift</label>
                        <input type="text" name="name" required x-model="editName" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Kode Unik Shift</label>
                            <input type="text" name="code" required x-model="editCode" disabled class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-500 dark:text-slate-400 focus:outline-none font-mono cursor-not-allowed">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Kode Singkat (Max 5)</label>
                            <input type="text" name="short_code" maxlength="5" x-model="editShortCode" placeholder="Cth: S1, P, M" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 font-mono uppercase">
                        </div>
                        <div class="flex items-center pt-2 col-span-2">
                            <input type="checkbox" id="edit_is_shift" name="is_shift" value="1" x-model="editIsShift" class="rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 w-4 h-4">
                            <label for="edit_is_shift" class="ml-2 font-semibold text-slate-750 dark:text-slate-300">Merupakan Shift Bergulir (Gantian)</label>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="2" x-model="editDescription" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Day Configs -->
                    <div>
                        <label class="block font-bold text-slate-750 dark:text-slate-300 mb-2 uppercase tracking-wide text-[10px]">Konfigurasi Hari & Jam Kerja</label>
                        <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-900">
                            <template x-for="(day, index) in days" :key="index">
                                <div class="grid grid-cols-12 items-center gap-3 border-b border-slate-100 dark:border-slate-900 last:border-0 pb-3 last:pb-0">
                                    <div class="col-span-3 font-semibold text-slate-700 dark:text-slate-350" x-text="day.name"></div>
                                    
                                    <div class="col-span-3 flex items-center">
                                        <!-- Hidden input to guarantee value submits when unchecked -->
                                        <input type="hidden" :name="`days[${index}][is_off]`" value="0">
                                        <input type="checkbox" :name="`days[${index}][is_off]`" value="1" x-model="day.is_off" class="rounded border-slate-300 text-rose-500 w-4 h-4 focus:ring-rose-400">
                                        <span class="ml-2 text-rose-600 dark:text-rose-450 font-medium">Libur</span>
                                    </div>
                                    
                                    <div class="col-span-3">
                                        <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" :disabled="day.is_off" class="w-full px-2 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded text-center disabled:opacity-50 font-mono">
                                    </div>
                                    
                                    <div class="col-span-3">
                                        <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" :disabled="day.is_off" class="w-full px-2 py-1 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-850 rounded text-center disabled:opacity-50 font-mono">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </template>

    </div>
</x-admin-layout>
