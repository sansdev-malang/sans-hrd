<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showRejectModal: false,
        selectedLeaveId: '',
        selectedLeaveEmployee: '',
        showEmpDetailModal: false,
        selectedEmp: null
    }">

        <!-- SUCCESS/ERROR ALERTS -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
                <div class="text-left">
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Perhatian!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left ">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Persetujuan Izin / Cuti</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tinjau dan setujui pengajuan surat sakit, izin, dan cuti dari seluruh pegawai unit sekolah.</p>
            </div>
        </header>

        <!-- PENDING REQUESTS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Menunggu Persetujuan</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3 text-left">Pegawai</th>
                            <th class="px-6 py-3 text-left">Unit</th>
                            <th class="px-6 py-3 text-center">Jenis Izin</th>
                            <th class="px-6 py-3 text-center">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-center">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-left">Alasan</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @php $pendingCount = 0; @endphp
                        @foreach($leaves->where('status', 'Pending') as $leave)
                            @php $pendingCount++; @endphp
                            <tr>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($leave->employee_photo) && !empty($leave->employee_unit_url))
                                            @php
                                                $photoPath = str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo;
                                                $photoUrl = rtrim($leave->employee_unit_url, '/') . '/storage/' . $photoPath;
                                            @endphp
                                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                                                {{ strtoupper(substr($leave->employee_name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span @click="selectedEmp = {
                                                name: '{{ $leave->employee_name }}',
                                                nuptk_nip_nik: '{{ $leave->employee_nip }}',
                                                subject_position: '{{ $leave->employee_position }}',
                                                unit: '{{ strtoupper($leave->schoolUnit ? $leave->schoolUnit->name : '-') }}',
                                                email: '{{ $leave->employee_email }}',
                                                gender: '{{ $leave->employee_gender }}',
                                                employment_status: '{{ $leave->employee_status }}',
                                                photo_url: '{{ !empty($leave->employee_photo) && !empty($leave->employee_unit_url) ? rtrim($leave->employee_unit_url, '/') . '/storage/' . (str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo) : '' }}',
                                                leave_type: '{{ $leave->type }}',
                                                leave_start: '{{ $leave->start_date->format('d M Y') }}',
                                                leave_end: '{{ $leave->end_date->format('d M Y') }}',
                                                leave_reason: '{{ addslashes($leave->reason ?? '-') }}',
                                                leave_attachment: '{{ $leave->attachment ?? '' }}'
                                            }; showEmpDetailModal = true" class="text-slate-900 dark:text-slate-50 font-bold tracking-tight block cursor-pointer  hover:text-indigo-600 dark:hover:text-indigo-400">{{ $leave->employee_name }}</span>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">NIP/NIK: {{ $leave->employee_nip }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30 uppercase">
                                        {{ $leave->schoolUnit ? $leave->schoolUnit->name : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200/45 dark:border-slate-800 uppercase">
                                        {{ $leave->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $leave->start_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $leave->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="text-slate-600 dark:text-slate-400 block font-semibold">{{ $leave->reason ?? '-' }}</span>
                                    @if($leave->attachment)
                                        <div class="mt-1">
                                            <a href="{{ $leave->attachment }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold ">
                                                <i data-lucide="paperclip" class="w-3 h-3"></i>
                                                Lihat Lampiran
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2.5">
                                    <form action="{{ route('leave-approvals.approve', $leave->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="h-7 px-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-semibold rounded border border-emerald-200/30 dark:border-emerald-900/30 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            Setujui
                                        </button>
                                    </form>
                                    <button @click="selectedLeaveId = '{{ $leave->id }}'; selectedLeaveEmployee = '{{ $leave->employee_name }}'; showRejectModal = true" class="h-7 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-[10px] font-semibold rounded border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        Tolak
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if($pendingCount === 0)
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    Tidak ada pengajuan izin yang memerlukan persetujuan saat ini.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PROCESSED REQUESTS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Riwayat Keputusan</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3 text-left">Pegawai</th>
                            <th class="px-6 py-3 text-left">Unit</th>
                            <th class="px-6 py-3 text-center">Jenis Izin</th>
                            <th class="px-6 py-3 text-center">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-center">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-center">Keputusan</th>
                            <th class="px-6 py-3 text-left">Catatan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @php $processedCount = 0; @endphp
                        @foreach($leaves->whereIn('status', ['Approved', 'Rejected']) as $leave)
                            @php $processedCount++; @endphp
                            <tr>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($leave->employee_photo) && !empty($leave->employee_unit_url))
                                            @php
                                                $photoPath = str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo;
                                                $photoUrl = rtrim($leave->employee_unit_url, '/') . '/storage/' . $photoPath;
                                            @endphp
                                            <img src="{{ $photoUrl }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200/50 dark:border-slate-800/40">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                                                {{ strtoupper(substr($leave->employee_name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span @click="selectedEmp = {
                                                name: '{{ $leave->employee_name }}',
                                                nuptk_nip_nik: '{{ $leave->employee_nip }}',
                                                subject_position: '{{ $leave->employee_position }}',
                                                unit: '{{ strtoupper($leave->schoolUnit ? $leave->schoolUnit->name : '-') }}',
                                                email: '{{ $leave->employee_email }}',
                                                gender: '{{ $leave->employee_gender }}',
                                                employment_status: '{{ $leave->employee_status }}',
                                                photo_url: '{{ !empty($leave->employee_photo) && !empty($leave->employee_unit_url) ? rtrim($leave->employee_unit_url, '/') . '/storage/' . (str_contains($leave->employee_photo, 'photos/') ? $leave->employee_photo : 'photos/' . $leave->employee_photo) : '' }}',
                                                leave_type: '{{ $leave->type }}',
                                                leave_start: '{{ $leave->start_date->format('d M Y') }}',
                                                leave_end: '{{ $leave->end_date->format('d M Y') }}',
                                                leave_reason: '{{ addslashes($leave->reason ?? '-') }}',
                                                leave_attachment: '{{ $leave->attachment ?? '' }}'
                                            }; showEmpDetailModal = true" class="text-slate-900 dark:text-slate-50 font-bold tracking-tight block cursor-pointer  hover:text-indigo-600 dark:hover:text-indigo-400">{{ $leave->employee_name }}</span>
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">NIP/NIK: {{ $leave->employee_nip }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30 uppercase">
                                        {{ $leave->schoolUnit ? $leave->schoolUnit->name : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200/45 dark:border-slate-800 uppercase">
                                        {{ $leave->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $leave->start_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $leave->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($leave->status === 'Approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30 uppercase">Disetujui</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border border-rose-200/30 dark:border-rose-900/30 uppercase">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <div class="text-slate-500 dark:text-slate-400 italic">{{ $leave->notes ?? '-' }}</div>
                                    @if($leave->attachment)
                                        <div class="mt-1">
                                            <a href="{{ $leave->attachment }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold ">
                                                <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                                                Lihat Lampiran
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if($processedCount === 0)
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400 ">
                                    Belum ada data pengajuan izin yang diproses.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- REJECT MODAL -->
        <template x-teleport="body">
            <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display: none; margin-top: 0px !important; z-index: 9999;">
            <div @click.outside="showRejectModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4 ">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Tolak Pengajuan Izin</h3>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5" x-text="selectedLeaveEmployee"></p>
                    </div>
                    <button @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('leave-approvals') }}/${selectedLeaveId}/reject`" class="space-y-4 text-xs ">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alasan Penolakan</label>
                        <textarea name="notes" required rows="3" placeholder="Masukkan alasan penolakan izin..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-880 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showRejectModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Tolak Izin
                        </button>
                    </div>
                </form>
            </div>
        </template>

        <!-- MODAL DETAIL PEGAWAI -->
        <template x-teleport="body">
            <div x-show="showEmpDetailModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs text-left" style="display: none; margin-top: 0px !important; z-index: 9999;">
            <div @click.outside="showEmpDetailModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden text-xs">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-55 font-nasalization flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Profil Pegawai
                    </h3>
                    <button @click="showEmpDetailModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="p-5 space-y-6">
                    <div class="flex items-center gap-4">
                        <!-- Photo / Initials -->
                        <div class="shrink-0">
                            <template x-if="selectedEmp && selectedEmp.photo_url">
                                <img :src="selectedEmp.photo_url" class="w-16 h-16 rounded-xl object-cover border border-slate-200 dark:border-slate-800 shadow-sm">
                            </template>
                            <template x-if="!selectedEmp || !selectedEmp.photo_url">
                                <div class="w-16 h-16 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 font-bold flex items-center justify-center text-2xl uppercase shadow-sm">
                                    <span x-text="selectedEmp ? selectedEmp.name.substring(0,2) : ''"></span>
                                </div>
                            </template>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization" x-text="selectedEmp ? selectedEmp.name : ''"></h4>
                            <p class="text-slate-400 dark:text-slate-500 font-mono" x-text="selectedEmp ? 'NIP/NUPTK: ' + (selectedEmp.nuptk_nip_nik || '-') : ''"></p>
                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 uppercase" x-text="selectedEmp ? selectedEmp.subject_position : ''"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-[11px] pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Unit Kerja</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200 uppercase" x-text="selectedEmp ? selectedEmp.unit : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Email</span>
                            <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.email : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jenis Kelamin</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? (selectedEmp.gender === 'Male' ? 'Laki-laki' : 'Perempuan') : ''"></span>
                        </div>
                        <div>
                            <span class="block text-slate-400 text-[9px] uppercase font-semibold">Status Pegawai</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.employment_status : ''"></span>
                        </div>
                    </div>

                    <!-- Detail Pengajuan Izin -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[10px]">Detail Pengajuan Izin</h4>
                        <div class="grid grid-cols-2 gap-4 text-[11px]">
                            <div>
                                <span class="block text-slate-400 text-[9px] uppercase font-semibold">Jenis Izin</span>
                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30 uppercase" x-text="selectedEmp ? selectedEmp.leave_type : ''"></span>
                            </div>
                            <div>
                                <span class="block text-slate-400 text-[9px] uppercase font-semibold">Rentang Tanggal</span>
                                <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.leave_start + ' - ' + selectedEmp.leave_end : ''"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="block text-slate-400 text-[9px] uppercase font-semibold">Alasan</span>
                                <span class="font-medium text-slate-700 dark:text-slate-200" x-text="selectedEmp ? selectedEmp.leave_reason : ''"></span>
                            </div>
                            <template x-if="selectedEmp && selectedEmp.leave_attachment">
                                <div class="col-span-2">
                                    <span class="block text-slate-400 text-[9px] uppercase font-semibold">Lampiran</span>
                                    <a :href="selectedEmp.leave_attachment" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold  mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                        Lihat Lampiran Dokumen
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button @click="showEmpDetailModal = false" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">Tutup</button>
                </div>
            </div>
        </template>
    </div>
</x-admin-layout>
