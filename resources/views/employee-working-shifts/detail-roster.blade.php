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
</style>
<div class="p-6 space-y-6" x-data="{ 
        openExportModal: false, 
        exportType: 'pdf',
        notes: '',
        isExporting: false,
        showToast: false,
        toastMessage: '',
        
        startExport() {
            setTimeout(() => {
                this.toastMessage = 'Proses download dimulai...';
                this.showToast = true;
                setTimeout(() => {
                    this.showToast = false;
                    this.openExportModal = false;
                }, 3000);
            }, 100);
        }
    }">
    
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('employee-working-shifts.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">{{ $rosterName }}</h2>
                @php
                    $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                @endphp
                <p class="text-xs text-slate-500 dark:text-slate-400">Unit: {{ $units->firstWhere('id', $selectedUnitId)->name ?? '' }} | Periode: {{ $bulanIndo[(int)$month] }} {{ $year }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" @click="openExportModal = true; exportType = 'pdf'; notes = '';" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors font-bold text-xs shadow-sm">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Export PDF
            </button>
            <button type="button" @click="openExportModal = true; exportType = 'excel'; notes = '';" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors font-bold text-xs shadow-sm">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Roster Grid Read Only -->
    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex flex-col">
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
                            <td class="px-4 py-2 border-r border-slate-200 dark:border-slate-800 sticky left-0 bg-white dark:bg-slate-950 group-hover:bg-slate-50 dark:group-hover:bg-slate-900/50 transition-colors z-10 shadow-[1px_0_0_0_rgba(226,232,240,1)] dark:shadow-[1px_0_0_0_rgba(30,41,59,1)]">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $emp['name'] }}</span>
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
                                <td class="px-1 py-1 border-r border-slate-200 dark:border-slate-800 text-center hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors {{ $isWeekend && !$shiftCode ? 'bg-rose-50 dark:bg-rose-900/20 text-rose-600' : '' }}">
                                    @if($shiftCode)
                                        <div class="{{ $shiftColor }} text-[10px] font-bold px-1 py-1 rounded shadow-sm w-full flex items-center justify-center min-h-[28px]">
                                            {{ $shiftCode }}
                                        </div>
                                    @else
                                        <div class="bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 text-[10px] font-bold px-1 py-1 rounded shadow-sm w-full flex items-center justify-center min-h-[28px]">
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
                    
                    <form method="GET" action="{{ route('employee-working-shifts.export-roster') }}" target="_blank" @submit="startExport()">
                        <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="roster_name" value="{{ $rosterName }}">
                        <input type="hidden" name="type" x-model="exportType">

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
                                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-350 mb-1.5">Catatan Tambahan (Opsional)</label>
                                            <textarea name="notes" x-model="notes" rows="3" class="w-full text-sm px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" placeholder="Ketik catatan di sini..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 px-4 py-3 flex flex-col sm:flex-row sm:px-6 border-t border-slate-200 dark:border-slate-800 gap-3">
                            <button type="submit" :disabled="isExporting" class="inline-flex flex-1 justify-center items-center rounded-lg bg-indigo-600 px-3 h-10 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors disabled:opacity-70 disabled:cursor-not-allowed">
                                <i x-show="!isExporting" data-lucide="download" class="w-4 h-4 mr-2"></i> 
                                <svg x-show="isExporting" style="display: none;" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="isExporting ? 'Sedang memproses...' : 'Download'"></span>
                            </button>
                            <button type="button" @click="openExportModal = false" :disabled="isExporting" class="inline-flex flex-1 justify-center items-center rounded-lg bg-white dark:bg-slate-800 px-3 h-10 text-sm font-semibold text-slate-900 dark:text-slate-300 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors disabled:opacity-70">
                                Batal
                            </button>
                        </div>
                    </form>
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