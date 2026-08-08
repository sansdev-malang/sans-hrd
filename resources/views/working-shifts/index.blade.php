<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        editId: null,
        editName: '',
        editCode: '',
        editShortCode: '',
        editIsShift: false,
        editDescription: '',
        deleteShift: null,
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
            
            toggleModal('edit-shift-modal');
        },
        confirmDelete(shift) {
            this.deleteShift = shift;
            toggleModal('delete-shift-modal');
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
            toggleModal('add-shift-modal');
        }
    }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">Pengaturan Shift & Jam Kerja</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur template jam kerja masuk dan pulang bagi guru dan karyawan (shift maupun non-shift) secara terpusat.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('working-shifts.sync') }}" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Sync Ulang ke Unit
                </a>
                <button @click="resetAdd()" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Shift Baru
                </button>
            </div>
        </header>

        <!-- ERROR MESSAGES -->
        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-xl p-4 text-xs text-rose-800 dark:text-rose-400 mb-2 text-left flex gap-3 items-start animate-fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <h5 class="font-bold mb-1">Gagal menyimpan data:</h5>
                    <ul class="list-disc pl-4 space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- CARDS GRID -->
        <section class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 text-left">
            @forelse($shifts as $shift)
                <div class="bg-white dark:bg-slate-900 border-t-4 {{ $shift->is_shift ? 'border-t-indigo-600 dark:border-t-indigo-500' : 'border-t-slate-450 dark:border-t-slate-600' }} border-x border-b border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $shift->name }}</h4>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono border border-slate-200 dark:border-slate-800 px-1.5 py-0.5 rounded bg-slate-50 dark:bg-slate-900/50">CODE: <strong class="text-slate-700 dark:text-slate-300">{{ $shift->short_code ?: strtoupper(last(explode('_', $shift->code))) }}</strong></span>
                            </div>
                            @if($shift->is_shift)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30 uppercase">Shift</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-900 text-slate-750 dark:text-slate-300 border border-slate-200/50 dark:border-slate-800 uppercase">Non-Shift</span>
                            @endif
                        </div>

                        @if($shift->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-normal">{{ $shift->description }}</p>
                        @endif

                        <!-- Day Schedules -->
                        <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800/60">
                            @php
                                $daysName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                $groupedSchedules = [];
                                foreach($shift->details->sortBy('day_of_week') as $detail) {
                                    $dayName = $daysName[$detail->day_of_week] ?? '';
                                    $timeKey = $detail->is_off ? 'Libur' : substr($detail->start_time, 0, 5) . ' - ' . substr($detail->end_time, 0, 5);
                                    $groupedSchedules[$timeKey][] = $dayName;
                                }
                            @endphp
                            @foreach($groupedSchedules as $timeKey => $days)
                                <div class="flex justify-between items-center text-[11px] py-1 border-b border-slate-50 dark:border-slate-800/40 last:border-0">
                                    <span class="text-slate-600 dark:text-slate-400 font-semibold leading-relaxed max-w-[65%] text-left">
                                        {{ implode(', ', $days) }}
                                    </span>
                                    @if($timeKey === 'Libur')
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-100/30 dark:border-rose-900/30 uppercase">Libur</span>
                                    @else
                                        <span class="font-mono text-[10px] font-bold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/80 border border-slate-200/50 dark:border-slate-800/40 px-2 py-0.5 rounded-lg whitespace-nowrap">
                                            {{ $timeKey }}
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
                        <button type="button" @click="confirmDelete({{ json_encode($shift) }})" class="h-8 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                    <i data-lucide="clock" class="w-8 h-8 mx-auto text-slate-400 mb-2"></i>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada template shift kerja yang terdaftar.</p>
                </div>
            @endforelse
        </section>

        <script>
            function toggleModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                const content = modal.firstElementChild;
                if (modal.classList.contains('hidden')) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.style.opacity = '1';
                        content.style.opacity = '1';
                        content.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    content.style.opacity = '0';
                    content.style.transform = 'scale(0.95)';
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 200);
                }
            }
        </script>

        <!-- ADD MODAL -->
        <template x-teleport="body">
            <div id="add-shift-modal" onclick="if(event.target === this) toggleModal('add-shift-modal')" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity text-left" style="z-index: 9999;">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden text-left transform transition-all scale-95 opacity-0 duration-200 text-xs">
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 shrink-0">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-650 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 6v6l4 2"/></svg>
                            <span>Tambah Template Shift Baru</span>
                        </h3>
                        <button type="button" onclick="toggleModal('add-shift-modal')" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
 
                    <form method="POST" action="{{ route('working-shifts.store') }}" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        <div class="flex-1 overflow-y-auto p-5 space-y-5 text-left">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Shift</label>
                                <input type="text" name="name" required placeholder="Contoh: Salehmart Shift 1" class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all">
                            </div>
 
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Unik Shift</label>
                                    <input type="text" name="code" required placeholder="Contoh: salehmart_s1" class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all font-mono">
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Singkat (Max 5)</label>
                                    <input type="text" name="short_code" maxlength="5" placeholder="Cth: S1, P, M" class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all font-mono uppercase">
                                </div>
                                <div class="flex items-center pt-1 col-span-2">
                                    <input type="checkbox" id="is_shift" name="is_shift" value="1" class="rounded border-slate-300 dark:border-slate-700 text-indigo-650 focus:ring-indigo-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                                    <label for="is_shift" class="ml-2 font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Merupakan Shift Bergulir (Gantian)</label>
                                </div>
                            </div>
 
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
                                <textarea name="description" rows="2" placeholder="Keterangan singkat jam kerja..." class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all"></textarea>
                            </div>
 
                            <!-- Day Configs -->
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-2.5 uppercase tracking-wide text-[10px]">Konfigurasi Hari & Jam Kerja</label>
                                
                                <!-- Header labels for desktop -->
                                <div class="grid grid-cols-12 gap-3 text-[9px] uppercase font-bold text-slate-400 dark:text-slate-500 px-4 mb-2 hidden sm:grid">
                                    <div class="col-span-3">Hari</div>
                                    <div class="col-span-3">Status</div>
                                    <div class="col-span-3 text-center">Jam Masuk</div>
                                    <div class="col-span-3 text-center">Jam Pulang</div>
                                </div>
 
                                <div class="space-y-3 bg-slate-50/50 dark:bg-slate-950/20 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/80">
                                    <template x-for="(day, index) in days" :key="index">
                                        <div class="flex flex-col sm:grid sm:grid-cols-12 items-start sm:items-center gap-2 sm:gap-3 border-b border-slate-100/85 dark:border-slate-800/60 last:border-0 pb-3 last:pb-0 transition-opacity"
                                            :class="day.is_off ? 'opacity-60' : ''">
                                            
                                            <!-- Day Name -->
                                            <div class="col-span-3 font-bold sm:font-semibold text-slate-800 dark:text-slate-200" x-text="day.name"></div>
                                            
                                            <!-- Off status toggle -->
                                            <div class="col-span-3 flex items-center mt-0.5 sm:mt-0">
                                                <input type="checkbox" :name="`days[${index}][is_off]`" value="1" x-model="day.is_off" class="rounded border-slate-300 dark:border-slate-700 text-rose-500 w-4.5 h-4.5 focus:ring-rose-400 focus:ring-offset-0 cursor-pointer">
                                                <span class="ml-2 text-rose-600 dark:text-rose-400 font-semibold uppercase text-[9px] tracking-wider cursor-pointer" @click="day.is_off = !day.is_off">Libur</span>
                                            </div>
                                            
                                            <!-- Time selectors -->
                                            <div class="col-span-6 grid grid-cols-2 gap-2 w-full mt-1.5 sm:mt-0">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[8px] text-slate-400 uppercase font-bold sm:hidden">Jam Masuk</span>
                                                    <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" :disabled="day.is_off" class="w-full text-xs px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-center disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-950/80 disabled:cursor-not-allowed font-mono focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all">
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[8px] text-slate-400 uppercase font-bold sm:hidden">Jam Pulang</span>
                                                    <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" :disabled="day.is_off" class="w-full text-xs px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-center disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-950/80 disabled:cursor-not-allowed font-mono focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
 
                        <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex gap-2.5 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button" onclick="toggleModal('add-shift-modal')" class="h-9 px-4 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                                Simpan Shift
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
 
        <!-- EDIT MODAL -->
        <template x-teleport="body">
            <div id="edit-shift-modal" onclick="if(event.target === this) toggleModal('edit-shift-modal')" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-opacity text-left" style="z-index: 9999;">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden text-left transform transition-all scale-95 opacity-0 duration-200 text-xs">
                    <!-- Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 shrink-0">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 font-nasalization flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-650 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M12 6v6l4 2"/></svg>
                            <span>Edit Template Shift</span>
                        </h3>
                        <button type="button" onclick="toggleModal('edit-shift-modal')" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 bg-transparent border-0 cursor-pointer flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
 
                    <form method="POST" :action="`{{ url('working-shifts') }}/${editId}`" class="flex flex-col flex-1 overflow-hidden">
                        @csrf
                        @method('PUT')
                        <div class="flex-1 overflow-y-auto p-5 space-y-5 text-left">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Shift</label>
                                <input type="text" name="name" required x-model="editName" class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all">
                            </div>
 
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Unik Shift</label>
                                    <input type="text" name="code" required x-model="editCode" disabled class="w-full text-xs px-3.5 py-2 bg-slate-100 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 focus:outline-none font-mono cursor-not-allowed">
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Singkat (Max 5)</label>
                                    <input type="text" name="short_code" maxlength="5" x-model="editShortCode" placeholder="Cth: S1, P, M" class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all font-mono uppercase">
                                </div>
                                <div class="flex items-center pt-1 col-span-2">
                                    <input type="checkbox" id="edit_is_shift" name="is_shift" value="1" x-model="editIsShift" class="rounded border-slate-300 dark:border-slate-700 text-indigo-650 focus:ring-indigo-500 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                                    <label for="edit_is_shift" class="ml-2 font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">Merupakan Shift Bergulir (Gantian)</label>
                                </div>
                            </div>
 
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
                                <textarea name="description" rows="2" x-model="editDescription" class="w-full text-xs px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all"></textarea>
                            </div>
 
                            <!-- Day Configs -->
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-2.5 uppercase tracking-wide text-[10px]">Konfigurasi Hari & Jam Kerja</label>
                                
                                <!-- Header labels for desktop -->
                                <div class="grid grid-cols-12 gap-3 text-[9px] uppercase font-bold text-slate-400 dark:text-slate-500 px-4 mb-2 hidden sm:grid">
                                    <div class="col-span-3">Hari</div>
                                    <div class="col-span-3">Status</div>
                                    <div class="col-span-3 text-center">Jam Masuk</div>
                                    <div class="col-span-3 text-center">Jam Pulang</div>
                                </div>
 
                                <div class="space-y-3 bg-slate-50/50 dark:bg-slate-950/20 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/80">
                                    <template x-for="(day, index) in days" :key="index">
                                        <div class="flex flex-col sm:grid sm:grid-cols-12 items-start sm:items-center gap-2 sm:gap-3 border-b border-slate-100/85 dark:border-slate-800/60 last:border-0 pb-3 last:pb-0 transition-opacity"
                                            :class="day.is_off ? 'opacity-60' : ''">
                                            
                                            <!-- Day Name -->
                                            <div class="col-span-3 font-bold sm:font-semibold text-slate-800 dark:text-slate-200" x-text="day.name"></div>
                                            
                                            <!-- Off status toggle -->
                                            <div class="col-span-3 flex items-center mt-0.5 sm:mt-0">
                                                <!-- Hidden input to guarantee value submits when unchecked -->
                                                <input type="hidden" :name="`days[${index}][is_off]`" value="0">
                                                <input type="checkbox" :name="`days[${index}][is_off]`" value="1" x-model="day.is_off" class="rounded border-slate-300 dark:border-slate-700 text-rose-500 w-4.5 h-4.5 focus:ring-rose-400 focus:ring-offset-0 cursor-pointer">
                                                <span class="ml-2 text-rose-600 dark:text-rose-400 font-semibold uppercase text-[9px] tracking-wider cursor-pointer" @click="day.is_off = !day.is_off">Libur</span>
                                            </div>
                                            
                                            <!-- Time selectors -->
                                            <div class="col-span-6 grid grid-cols-2 gap-2 w-full mt-1.5 sm:mt-0">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[8px] text-slate-400 uppercase font-bold sm:hidden">Jam Masuk</span>
                                                    <input type="time" :name="`days[${index}][start_time]`" x-model="day.start_time" :disabled="day.is_off" class="w-full text-xs px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-center disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-950/80 disabled:cursor-not-allowed font-mono focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all">
                                                </div>
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[8px] text-slate-400 uppercase font-bold sm:hidden">Jam Pulang</span>
                                                    <input type="time" :name="`days[${index}][end_time]`" x-model="day.end_time" :disabled="day.is_off" class="w-full text-xs px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-center disabled:opacity-50 disabled:bg-slate-100 dark:disabled:bg-slate-950/80 disabled:cursor-not-allowed font-mono focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950/30 transition-all">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
 
                        <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex gap-2.5 justify-end bg-slate-50 dark:bg-slate-900/40 shrink-0">
                            <button type="button" onclick="toggleModal('edit-shift-modal')" class="h-9 px-4 bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- DELETE MODAL -->
        <template x-teleport="body">
            <div id="delete-shift-modal" onclick="if(event.target === this) toggleModal('delete-shift-modal')" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm hidden transition-opacity text-left">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-sm w-full overflow-hidden transform transition-all scale-95 opacity-0 duration-200 text-xs">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-50 mb-2">Hapus Shift Kerja?</h3>
                        <p class="text-[13px] text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                            Anda yakin ingin menghapus template shift <strong class="text-slate-700 dark:text-slate-300" x-text="deleteShift ? deleteShift.name : ''"></strong>? Data yang telah dihapus tidak dapat dikembalikan.
                        </p>
                        
                        <div class="flex justify-center gap-3">
                            <button type="button" onclick="toggleModal('delete-shift-modal')" class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold rounded-xl cursor-pointer transition-colors shadow-sm">
                                Batal
                            </button>
                            <form id="delete-shift-form" method="POST" :action="deleteShift ? `{{ url('working-shifts') }}/${deleteShift.id}` : ''" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-xs px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl cursor-pointer transition-colors shadow-sm flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    Ya, Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-admin-layout>
