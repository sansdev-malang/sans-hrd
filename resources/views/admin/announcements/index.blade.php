<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showDetailModal: false, 
        selectedAnnouncement: {} 
    }">
        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Pengumuman Pusat</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola dan sebar luaskan pengumuman ke seluruh atau unit sekolah tertentu (SD, PAUD, SMP) secara instan.</p>
            </div>
            <div>
                <a href="{{ route('announcements.create') }}" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Pengumuman Baru
                </a>
            </div>
        </header>

        <!-- TABLE CARD -->
        <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 dark:border-slate-900">
                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Pengumuman</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-150 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3 text-left">Judul Pengumuman</th>
                            <th class="px-6 py-3 text-center">Kategori</th>
                            <th class="px-6 py-3 text-left">Unit Target</th>
                            <th class="px-6 py-3 text-center">Tanggal Terbit</th>
                            <th class="px-6 py-3 text-center">Pembuat</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($announcements as $announcement)
                            <tr>
                                <td class="px-6 py-4 text-left">
                                    <div class="font-bold text-slate-900 dark:text-slate-50" title="{{ $announcement->title }}">{{ \Illuminate\Support\Str::limit($announcement->title, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $catColor = [
                                            'umum' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/30',
                                            'akademik' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/30',
                                            'kepegawaian' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30',
                                            'penting' => 'bg-rose-50 text-rose-700 dark:bg-rose-955/20 dark:text-rose-400 border border-rose-200/50 dark:border-rose-900/30',
                                        ][$announcement->category] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $catColor }} uppercase">
                                        {{ $announcement->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $targets = $announcement->target_units ?? [];
                                        @endphp
                                        @forelse($targets as $t)
                                            @php
                                                $unit = \App\Models\SchoolUnit::find($t['school_unit_id']);
                                                $audienceMap = [
                                                    'global' => 'Semua',
                                                    'teacher' => 'Guru',
                                                    'employee' => 'Staf',
                                                    'student' => 'Siswa',
                                                    'parent' => 'Ortu',
                                                    'management' => 'Manajemen'
                                                ];
                                                $audiences = explode(',', $t['target_audience']);
                                                $translatedAudiences = array_map(function($aud) use ($audienceMap) {
                                                    return $audienceMap[trim($aud)] ?? trim($aud);
                                                }, $audiences);
                                                $audienceLabel = implode(', ', $translatedAudiences);
                                            @endphp
                                            @if($unit)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30 text-[9px] font-bold">
                                                    {{ $unit->name }} ({{ $audienceLabel }})
                                                </span>
                                            @endif
                                        @empty
                                            <span class="text-slate-400 dark:text-slate-600 text-[10px]">Internal HRD Only</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-[11px]">
                                    {{ $announcement->publish_date ? $announcement->publish_date->format('d M Y, H:i') : $announcement->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">
                                    {{ $announcement->creator->name ?? 'Admin' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($announcement->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-800">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <!-- View Link -->
                                    <a href="{{ route('announcements.show', $announcement) }}" class="h-7 w-7 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-650 dark:text-slate-350 border border-slate-200 dark:border-slate-800 rounded-lg flex items-center justify-center transition" title="Lihat Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Edit Link -->
                                    <a href="{{ route('announcements.edit', $announcement) }}" class="h-7 w-7 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-650 dark:text-slate-350 border border-slate-200 dark:border-slate-800 rounded-lg flex items-center justify-center transition" title="Edit">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <!-- Delete Link -->
                                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini? Tindakan ini akan menghapusnya dari seluruh unit sekolah target.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="h-7 w-7 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-955/20 text-rose-700 dark:text-rose-455 border border-rose-200/30 dark:border-rose-900/30 rounded-lg flex items-center justify-center transition cursor-pointer" title="Hapus">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada pengumuman terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-900">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
