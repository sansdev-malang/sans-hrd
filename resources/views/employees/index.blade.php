<x-admin-layout>
    <div class="p-6 space-y-6">

        <!-- SUCCESS ALERT -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Sukses!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- ERROR ALERT -->
        @if($errors->any())
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Terjadi Kesalahan!</h5>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Pegawai Terintegrasi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar gabungan dan manajemen guru/staf dari seluruh unit sekolah yang terhubung.</p>
            </div>
            <div>
                <a href="{{ route('employees.create') }}" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Tambah Pegawai
                </a>
            </div>
        </header>

        <!-- DATA TABLE -->
        <section class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
            <div class="p-5 border-b border-slate-100 dark:border-slate-900 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Daftar Pegawai Gabungan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Tipe Pegawai</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">NUPTK/NIP/NIK</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36">Unit Sekolah</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-40 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($employees as $index => $emp)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900 dark:text-slate-50">{{ $emp['name'] }}</div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">{{ $emp['subject_position'] }} • {{ $emp['gender'] == 'Male' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ $emp['employee_type']['name'] ?? 'Pegawai' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $emp['nuptk_nip_nik'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/50 uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                        {{ $emp['unit_name'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if(($emp['status'] ?? '') == 'Active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/40 dark:border-emerald-800/40 uppercase">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/40 dark:border-rose-800/40 uppercase">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('employees.edit', [$emp['unit_id'], $emp['id']]) }}" class="h-8 px-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            Edit
                                        </a>
                                        <form action="{{ route('employees.destroy', [$emp['unit_id'], $emp['id']]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-8 px-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="users-2" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-xs">Tidak ada data pegawai yang dapat ditampilkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
