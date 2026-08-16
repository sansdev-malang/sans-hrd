<x-admin-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Detail ' . $rosterName) }}
    </h2>
</x-slot>
<style>
    @foreach($shifts as $shift)
        .shift-color-{{ $shift->id }} {
            background-color: {{ $shift->hex_bg }};
            color: {{ $shift->hex_text }};
        }
        .dark .shift-color-{{ $shift->id }} {
            background-color: {{ $shift->hex_bg }}40;
            color: {{ $shift->hex_text }};
        }
    @endforeach
    [x-cloak] {
        display: none !important;
    }
</style>
<div class="p-6 space-y-6 animate-fade-in" x-data="{ 
        openExportModal: false, 
        exportType: 'pdf',
        notes: '',
        isExporting: false,
        showToast: false,
        toastMessage: '',
        selectedUnitId: '{{ $selectedUnitId }}',
        month: '{{ $month }}',
        year: '{{ $year }}',
        rosterName: '{{ addslashes($rosterName) }}',
        
        openPublishModal: false,
        isPublishing: false,
        openPersonalPublishModal: false,
        selectedPersonalEmpName: '',
        selectedPersonalEmpId: '',
        
        submitExport() {
            this.isExporting = true;
            
            const url = `{{ route('employee-working-shifts.export-roster') }}?unit_id=${this.selectedUnitId}&month=${this.month}&year=${this.year}&roster_name=${encodeURIComponent(this.rosterName)}&type=${this.exportType}&notes=${encodeURIComponent(this.notes)}`;
            
            window.location.href = url;
            
            this.toastMessage = 'Proses download dimulai...';
            this.showToast = true;
            
            setTimeout(() => {
                this.isExporting = false;
                this.showToast = false;
                this.openExportModal = false;
            }, 3000);
        },
        
        publishPersonal() {
            this.isPublishing = true;
            setTimeout(() => {
                this.isPublishing = false;
                this.openPersonalPublishModal = false;
                this.toastMessage = '[Tahap Pengembangan] Notifikasi personal ke ' + this.selectedPersonalEmpName + ' berhasil disimulasikan!';
                this.showToast = true;
                setTimeout(() => this.showToast = false, 3000);
            }, 1500);
        },
        
        publishAll() {
            this.isPublishing = true;
            setTimeout(() => {
                this.isPublishing = false;
                this.openPublishModal = false;
                this.toastMessage = '[Tahap Pengembangan] Publikasi roster massal berhasil disimulasikan!';
                this.showToast = true;
                setTimeout(() => this.showToast = false, 3000);
            }, 2000);
        }
    }">
    
    <!-- Header & Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
        <div class="flex items-center gap-3 text-left">
            <a href="{{ route('employee-working-shifts.index', ['tab' => 'roster', 'unit_id' => $selectedUnitId]) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-3xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-50 font-nasalization tracking-wide flex items-center gap-2.5">
                    <span>{{ $rosterName }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider">Detail Roster</span>
                </h2>
                <!-- Context Badges -->
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Unit:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 font-bold text-[10px]">{{ $units->firstWhere('id', $selectedUnitId)->name ?? '' }}</span>
                    <span class="text-slate-300 dark:text-slate-700 text-[10px]">•</span>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Periode:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border border-emerald-100/30 dark:border-emerald-900/30 font-bold text-[10px]">
                        @php
                            $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        @endphp
                        {{ $bulanIndo[(int)$month] }} {{ $year }}
                    </span>
                    <span class="text-slate-300 dark:text-slate-700 text-[10px]">•</span>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Jumlah:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/40 dark:border-slate-700/40 font-bold text-[10px]">{{ count($employees) }} Orang</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" @click="openPublishModal = true" class="inline-flex items-center gap-2 h-9 px-3.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition-all hover:scale-105 font-bold text-xs shadow-3xs cursor-pointer border-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                <span>Kirim ke Pegawai</span>
            </button>
            <button type="button" @click="openExportModal = true; exportType = 'pdf'; notes = '';" class="inline-flex items-center gap-2 h-9 px-3.5 rounded-xl bg-rose-50/60 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 hover:bg-rose-100 border border-rose-200/50 dark:border-rose-900/30 transition-all hover:scale-105 font-bold text-xs shadow-3xs cursor-pointer">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Export PDF
            </button>
            <button type="button" @click="openExportModal = true; exportType = 'excel'; notes = '';" class="inline-flex items-center gap-2 h-9 px-3.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 border border-emerald-200/50 dark:border-emerald-900/30 transition-all hover:scale-105 font-bold text-xs shadow-3xs cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Roster Grid Read Only -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto relative">
            <table class="w-full text-sm text-left border-collapse min-w-max">
                <thead class="text-xs text-slate-700 dark:text-slate-300 uppercase bg-slate-50 dark:bg-slate-900/80 sticky top-0 z-20 shadow-sm">
                    <tr>
                        <th scope="col" class="px-4 py-3 border-b border-r border-slate-200 dark:border-slate-800 sticky left-0 bg-slate-50 dark:bg-slate-900/90 z-30 shadow-[1px_0_0_0_rgba(226,232,240,1)] dark:shadow-[1px_0_0_0_rgba(30,41,59,1)] min-w-[200px]">
                            Pegawai
                        </th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $timestamp = mktime(0,0,0,$month,$d,$year);
                                $isWeekend = (date('D', $timestamp) == 'Sun');
                                
                                $hariIndo = [
                                    'Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 
                                    'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'
                                ];
                                $dayName = $hariIndo[date('D', $timestamp)];
                            @endphp
                            <th scope="col" class="px-1 py-2 border-b border-r border-slate-200 dark:border-slate-800 text-center min-w-[48px] {{ $isWeekend ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400' : '' }}">
                                <div class="flex flex-col items-center">
                                    <span class="text-[9px] font-medium opacity-70">{{ $dayName }}</span>
                                    <span class="text-sm font-bold">{{ $d }}</span>
                                </div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($employees as $index => $emp)
                        @php
                            $empId = $emp['id'];
                            $rowData = $rosterData[$empId] ?? null;
                            $schemaId = $rowData['bonus_schema_id'] ?? '';
                            $schemaName = '-';
                            if($schemaId) {
                                $schema = collect($bonusSchemas)->firstWhere('id', $schemaId);
                                $schemaName = $schema ? $schema->name : '-';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-colors group">
                            <td class="px-4 py-2 border-r border-slate-200 dark:border-slate-800 sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-900/50 transition-colors z-10 shadow-[1px_0_0_0_rgba(226,232,240,1)] dark:shadow-[1px_0_0_0_rgba(30,41,59,1)]">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ $emp['name'] }}</span>
                                    <button type="button" @click="selectedPersonalEmpName = '{{ addslashes($emp['name']) }}'; selectedPersonalEmpId = '{{ $empId }}'; openPersonalPublishModal = true;"
                                        class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-450 hover:text-indigo-650 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors border-0 bg-transparent cursor-pointer shrink-0"
                                        title="Kirim notifikasi personal ke {{ $emp['name'] }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                                    </button>
                                </div>
                            </td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $shiftId = $rowData['days'][$d] ?? '';
                                    $shiftCode = '';
                                    $shiftColor = '';
                                    if($shiftId) {
                                        $shift = collect($shifts)->firstWhere('id', $shiftId);
                                        $shiftCode = $shift ? ($shift->short_code ?: strtoupper(last(explode('_', $shift->code)))) : '';
                                        $shiftColor = $shift ? $shift->color : '';
                                    }
                                    $timestamp = mktime(0,0,0,$month,$d,$year);
                                    $isWeekend = (date('D', $timestamp) == 'Sun');
                                @endphp
                                <td class="p-1 border-r border-slate-200 dark:border-slate-800 text-center relative select-none transition-colors {{ $isWeekend ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
                                    @if($shiftCode)
                                        <div class="{{ $shiftColor }} text-[10.5px] font-black rounded-lg border border-transparent shadow-3xs w-full h-8 flex items-center justify-center transition-all hover:scale-105 duration-150">
                                            {{ $shiftCode }}
                                        </div>
                                    @else
                                        <div class="bg-slate-100/80 text-slate-500 border border-slate-200/60 dark:bg-slate-800/40 dark:text-slate-400 dark:border-slate-700/60 text-[10.5px] font-black rounded-lg shadow-3xs w-full h-8 flex items-center justify-center transition-all hover:scale-105 duration-150">
                                            OFF
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 2 }}" class="p-8 text-center text-slate-500 text-sm">
                                Tidak ada data jadwal pegawai untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <!-- Export Options Modal -->
    <div x-show="openExportModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="openExportModal" 
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
            class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>

        <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="openExportModal" @click.away="openExportModal = false"
                    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                    
                    <div>
                        <div class="bg-white dark:bg-slate-900 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-slate-100" id="modal-title">
                                        Opsi Export <span x-text="exportType === 'pdf' ? 'PDF' : 'Excel'" class="uppercase"></span>
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                                            Anda dapat menambahkan catatan atau keterangan tambahan (opsional) yang akan ditampilkan di bagian bawah dokumen hasil ekspor.
                                        </p>
                                        
                                        <div class="w-full">
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan Tambahan (Opsional)</label>
                                            <textarea name="notes" x-model="notes" rows="3" class="w-full text-sm px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="Ketik catatan di sini..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 px-4 py-3 flex flex-col sm:flex-row sm:px-6 border-t border-slate-200 dark:border-slate-800 gap-3">
                            <button type="button" @click="submitExport()" :disabled="isExporting" class="inline-flex flex-1 justify-center items-center rounded-lg bg-indigo-600 px-3 h-10 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer border-0">
                                <i x-show="!isExporting" data-lucide="download" class="w-4 h-4 mr-2"></i> 
                                <svg x-show="isExporting" style="display: none;" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="isExporting ? 'Sedang memproses...' : 'Download'"></span>
                            </button>
                            <button type="button" @click="openExportModal = false" :disabled="isExporting" class="inline-flex flex-1 justify-center items-center rounded-lg bg-white dark:bg-slate-800 px-3 h-10 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors disabled:opacity-70 cursor-pointer">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Publikasikan Roster ke Pegawai -->
    <div x-show="openPublishModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="openPublishModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
        <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="openPublishModal" @click.away="openPublishModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800">
                    <div class="bg-white dark:bg-slate-900 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                           <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                               <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 mb-4 shrink-0">
                                   <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                               </div>
                               <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-slate-100 text-center" id="modal-title">
                                   Kirim Jadwal Roster ke Portal Pegawai
                               </h3>
                               <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 text-center space-y-2 leading-relaxed">
                                   <p>
                                       Mempublikasikan roster ini akan memunculkan jadwal kerja secara langsung pada portal akun masing-masing pegawai yang terdaftar.
                                   </p>
                                   
                                   <div class="p-3 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400 rounded-xl border border-amber-250 dark:border-amber-900/30 text-[11px] font-medium flex items-start gap-2.5 text-left mt-3">
                                       <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z"/></svg>
                                       <span><strong>Perhatian:</strong> Fitur publikasi ke portal pegawai saat ini masih dalam tahap pengembangan (tidak dikirimkan secara nyata).</span>
                                   </div>

                                   <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200/50 dark:border-slate-800/40 text-left mt-3">
                                       <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                           <input type="checkbox" checked class="rounded border-slate-350 text-indigo-650 w-4 h-4 cursor-pointer mt-0.5">
                                           <div class="flex flex-col">
                                               <span class="font-bold text-slate-700 dark:text-slate-300">Kirim Notifikasi Portal / Email</span>
                                               <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Beritahu pegawai melalui pesan notifikasi otomatis bahwa jadwal roster baru bulan ini telah terbit.</span>
                                           </div>
                                       </label>
                                    </div>
                               </div>
                           </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-4 py-3 flex flex-col sm:flex-row sm:px-6 border-t border-slate-200 dark:border-slate-800 gap-3">
                        <button type="button" @click="publishAll()" :disabled="isPublishing" class="inline-flex flex-1 justify-center items-center rounded-lg bg-indigo-600 px-3 h-10 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer border-0">
                            <svg x-show="isPublishing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isPublishing ? 'Sedang mengirim...' : 'Kirim Sekarang'"></span>
                        </button>
                        <button type="button" @click="openPublishModal = false" :disabled="isPublishing" class="inline-flex flex-1 justify-center items-center rounded-lg bg-white dark:bg-slate-800 px-3 h-10 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors disabled:opacity-70 cursor-pointer">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kirim Notifikasi Personal -->
    <div x-show="openPersonalPublishModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div x-show="openPersonalPublishModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
        <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="openPersonalPublishModal" @click.away="openPersonalPublishModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-slate-800">
                    <div class="bg-white dark:bg-slate-900 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                           <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                               <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-950/30 text-indigo-650 dark:text-indigo-400 mb-4 shrink-0">
                                   <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.53 16.211 3 14.162 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg>
                               </div>
                               <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-slate-100 text-center" id="modal-title">
                                   Kirim Notifikasi Personal
                               </h3>
                               <div class="mt-3 text-xs text-slate-500 dark:text-slate-400 text-center space-y-2 leading-relaxed">
                                   <p>
                                       Kirimkan pesan pemberitahuan personal ke akun portal pegawai bernama <strong class="text-slate-700 dark:text-slate-200" x-text="selectedPersonalEmpName"></strong> untuk menginformasikan jadwal kerjanya secara khusus?
                                   </p>
                                   
                                   <div class="p-3 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400 rounded-xl border border-amber-250 dark:border-amber-900/30 text-[11px] font-medium flex items-start gap-2.5 text-left mt-3">
                                       <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z"/></svg>
                                       <span><strong>Perhatian:</strong> Fitur kirim notifikasi personal saat ini masih dalam tahap pengembangan (tidak dikirimkan secara nyata).</span>
                                   </div>
                               </div>
                           </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-4 py-3 flex flex-col sm:flex-row sm:px-6 border-t border-slate-200 dark:border-slate-800 gap-3">
                        <button type="button" @click="publishPersonal()" :disabled="isPublishing" class="inline-flex flex-1 justify-center items-center rounded-lg bg-indigo-600 px-3 h-10 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer border-0">
                            <svg x-show="isPublishing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isPublishing ? 'Sedang mengirim...' : 'Kirim Notifikasi'"></span>
                        </button>
                        <button type="button" @click="openPersonalPublishModal = false" :disabled="isPublishing" class="inline-flex flex-1 justify-center items-center rounded-lg bg-white dark:bg-slate-800 px-3 h-10 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors disabled:opacity-70 cursor-pointer">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification (z-[99999] fix) -->
    <div x-show="showToast" style="display: none;" 
        x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0" 
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
        class="fixed bottom-4 right-4 z-[99999] pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white dark:bg-slate-800 shadow-lg ring-1 ring-black ring-opacity-5 border border-slate-200 dark:border-slate-700">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="p-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100" x-text="toastMessage"></p>
                </div>
                <div class="ml-4 flex flex-shrink-0">
                    <button @click="showToast = false" type="button" class="inline-flex rounded-md bg-white dark:bg-slate-800 text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>