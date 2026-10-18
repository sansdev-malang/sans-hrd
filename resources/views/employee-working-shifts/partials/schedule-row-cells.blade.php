@if ($activeTab === 'roster')
    <td class="px-4 py-2.5 text-left">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100/20 dark:border-indigo-900/10">
                <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
            </div>
            <div class="flex flex-col gap-0.5">
                <span @click="openModal({{ json_encode($batch) }})" class="font-semibold text-slate-800 dark:text-slate-200 text-xs cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors duration-150" title="Klik untuk melihat rincian daftar pegawai">{{ !empty($batch['roster_name']) ? $batch['roster_name'] : 'Roster Shift Bulanan' }}</span>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex w-max px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wide">Roster</span>
                    <span class="inline-flex items-center gap-1 w-max px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/40 dark:border-amber-900/30" title="Skema Uang Kehadiran Terpasang"><i data-lucide="award" class="w-2.5 h-2.5"></i> {{ (($batch['bonus_schema_mode'] ?? '') === 'early_arrival' ? '⚡ ' : '⏱️ ') . ($batch['bonus_schema_name'] ?? 'Default') }}</span>
                    @if(!empty($batch['is_expired']))
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 uppercase tracking-wide">Selesai</span>
                    @endif
                </div>
            </div>
        </div>
    </td>
    <td class="px-4 py-2.5 text-left whitespace-nowrap">
        <span class="font-semibold text-slate-700 dark:text-slate-350 text-xs">{{ $batch['unit_name'] }}</span>
    </td>
    <td class="px-4 py-2.5 text-center whitespace-nowrap">
        <div class="flex items-center justify-center gap-1.5">
            <span class="text-xs font-semibold text-slate-900 dark:text-slate-200">{{ \Carbon\Carbon::create($batch['year'], $batch['month'], 1)->translatedFormat('F Y') }}</span>
            <span class="px-1.5 py-0.2 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[9px] font-bold uppercase tracking-wider border border-blue-100/30 dark:border-blue-900/30">Roster</span>
            @if(!empty($batch['is_expired']))
                <span class="px-1.5 py-0.2 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[9px] font-bold uppercase tracking-wider border border-slate-200 dark:border-slate-700">Terlewat</span>
            @endif
        </div>
    </td>
    <td class="px-4 py-2.5 text-center whitespace-nowrap">
        <div @click="openModal({{ json_encode($batch) }})" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-100/30 dark:border-indigo-900/30 cursor-pointer shadow-2xs font-bold text-xs whitespace-nowrap transition-colors" title="Klik untuk melihat rincian daftar pegawai">
            <i data-lucide="users" class="w-3 h-3"></i>
            <span>{{ count($batch['employees']) }} Orang</span>
        </div>
    </td>
    <td class="px-4 py-2.5">
        <div class="flex items-center justify-end gap-1">
            <a href="{{ route('employee-working-shifts.detail-roster', [
                'unit_id' => $batch['school_unit_id'],
                'month' => $batch['month'],
                'year' => $batch['year'],
                'roster_name' => $batch['roster_name'] ?? '',
                'bonus_schema_id' => $batch['bonus_schema_id'] ?? '',
            ]) }}"
                class="h-7 w-7 inline-flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-600 dark:hover:bg-indigo-650 hover:text-white text-indigo-700 dark:text-indigo-400 rounded-lg border border-indigo-200/30 dark:border-indigo-900/30 transition-all hover:shadow-xs cursor-pointer"
                title="Lihat Detail Roster">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
            </a>
            <a href="{{ route('employee-working-shifts.roster', [
                'unit_id' => $batch['school_unit_id'],
                'month' => $batch['month'],
                'year' => $batch['year'],
                'roster_name' => $batch['roster_name'] ?? '',
                'bonus_schema_id' => $batch['bonus_schema_id'] ?? '',
            ]) }}"
                class="h-7 w-7 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all hover:shadow-xs"
                title="Edit Roster">
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
            </a>
            <button type="button"
                @click="confirmDeleteBatch({{ json_encode($batch) }})"
                class="h-7 w-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-450 hover:text-white rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all hover:shadow-xs cursor-pointer"
                title="Hapus Roster">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </td>
@else
    <td class="px-4 py-2.5 text-left">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100/20 dark:border-emerald-900/10">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
            </div>
            <div class="flex flex-col gap-0.5">
                <span @click="openModal({{ json_encode($batch) }})" class="font-semibold text-slate-800 dark:text-slate-200 text-xs cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors duration-150" title="Klik untuk melihat rincian daftar pegawai">{{ $batch['shift_name'] }}</span>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex w-max px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-955/30 text-emerald-700 dark:text-emerald-455 border border-emerald-100/30 dark:border-emerald-900/30 uppercase tracking-wide">Kode: {{ $batch['shift_code'] }}</span>
                    <span class="inline-flex items-center gap-1 w-max px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-50 dark:bg-amber-955/30 text-amber-750 dark:text-amber-400 border border-amber-200/40 dark:border-amber-900/30" title="Skema Uang Kehadiran Terpasang"><i data-lucide="award" class="w-2.5 h-2.5"></i> {{ (($batch['bonus_schema_mode'] ?? '') === 'early_arrival' ? '⚡ ' : '⏱️ ') . ($batch['bonus_schema_name'] ?? 'Default') }}</span>
                    @if(!empty($batch['is_expired']))
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 uppercase tracking-wide">Selesai</span>
                    @endif
                </div>
            </div>
        </div>
    </td>
    <td class="px-4 py-2.5 text-left whitespace-nowrap">
        <span class="font-semibold text-slate-700 dark:text-slate-350 text-xs">{{ $batch['unit_name'] }}</span>
    </td>
    <td class="px-4 py-2.5 text-center whitespace-nowrap">
        <div class="flex items-center justify-center gap-1 text-xs font-semibold text-slate-900 dark:text-slate-200">
            <span>{{ \Carbon\Carbon::parse($batch['start_date'])->translatedFormat('d M Y') }}</span>
            <span class="text-slate-400 dark:text-slate-500 font-normal text-[10px]">s/d</span>
            @if ($batch['end_date'])
                <span>{{ \Carbon\Carbon::parse($batch['end_date'])->translatedFormat('d M Y') }}</span>
                @if(!empty($batch['is_expired']))
                    <span class="ml-1 px-1.5 py-0.2 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[9px] font-bold uppercase tracking-wider border border-slate-200 dark:border-slate-700">Terlewat</span>
                @endif
            @else
                <span class="px-2 py-0.2 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-wider border border-emerald-100/30 dark:border-emerald-900/30 whitespace-nowrap">Seterusnya</span>
            @endif
        </div>
    </td>
    <td class="px-4 py-2.5 text-center whitespace-nowrap">
        <div @click="openModal({{ json_encode($batch) }})" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-100/30 dark:border-indigo-900/30 cursor-pointer shadow-2xs font-bold text-xs whitespace-nowrap transition-colors" title="Klik untuk melihat rincian daftar pegawai">
            <i data-lucide="users" class="w-3 h-3"></i>
            <span>{{ count($batch['employees']) }} Orang</span>
        </div>
    </td>
    <td class="px-4 py-2.5">
        <div class="flex items-center justify-end gap-1">
            <button type="button" @click="openEditModal({{ json_encode($batch) }})"
                class="h-7 w-7 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-lg shadow-2xs transition-all hover:shadow-xs cursor-pointer"
                title="Edit Penugasan">
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
            </button>

            <button type="button"
                @click="confirmDeleteBatch({{ json_encode($batch) }})"
                class="h-7 w-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 dark:bg-rose-950/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-450 hover:text-white rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all hover:shadow-xs cursor-pointer"
                title="Hapus Penugasan">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </td>
@endif
