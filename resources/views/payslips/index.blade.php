<x-admin-layout>
    <div class="p-6 space-y-6">
        <!-- HEADER -->
        <section class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Data Slip Gaji</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Unggah dan kelola slip gaji (PDF) per pegawai.</p>
            </div>
        </section>

        <!-- FILTERS -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-4 w-full">
            <form method="GET" action="{{ route('payslips.index') }}" class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-end gap-3 w-full">

                <!-- Periode Bulan -->
                <div class="space-y-1 w-full sm:w-40">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Periode Bulan</label>
                    <input type="month" name="month" value="{{ $month }}" class="w-full text-xs h-9 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50" onchange="this.form.submit()">
                </div>

                <!-- Filter Unit -->
                <div class="space-y-1 w-full sm:w-48">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Filter Unit</label>
                    <select name="unit_id" class="w-full text-xs h-9 pl-3 pr-8 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 text-ellipsis overflow-hidden whitespace-nowrap" onchange="this.form.submit()">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                @if(in_array(auth()->user()->role, ['super_admin', 'hrd']))
                <div class="space-y-1 w-full sm:w-60">
                    <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1">Cari Pegawai</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Ketik nama..." class="w-full text-xs h-9 pl-9 pr-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    </div>
                </div>
                @endif

            </form>
        </section>

        <!-- ALERTS -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-sm flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MAIN TABLE -->
        <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full p-0">
            <div class="overflow-x-auto" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 uppercase font-semibold border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
                        <tr>
                            <th class="px-6 py-4 w-64">Nama Pegawai</th>
                            <th class="px-6 py-4">Asal Unit</th>
                            <th class="px-6 py-4">Periode</th>
                            <th class="px-6 py-4 text-center">Status File</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($employees as $emp)
                            <tr class="group hover:bg-slate-50/60 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $emp['name'] }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $emp['nik'] ?? 'No NIK' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $emp['unit_name'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($emp['payslip'])
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            Kosong
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($emp['payslip'])
                                            <a href="{{ Storage::url($emp['payslip']->file_path) }}" target="_blank"
                                               class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Lihat Slip">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <form method="POST" action="{{ route('payslips.destroy', $emp['payslip']->id) }}" onsubmit="return confirm('Hapus slip gaji ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="openUploadModal('{{ $emp['id'] }}', '{{ $emp['unit_id'] }}', '{{ addslashes($emp['name']) }}')"
                                                    class="inline-flex items-center gap-1.5 h-9 px-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-semibold rounded-lg hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors">
                                                <i data-lucide="upload" class="w-4 h-4"></i>
                                                Upload
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                        <i data-lucide="file-search" class="w-12 h-12 mb-4 text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-sm font-medium">Tidak ada data pegawai yang ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300" id="uploadModalContent">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Upload Slip Gaji</h3>
                <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="uploadForm" method="POST" action="{{ route('payslips.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="employee_id" id="modal_employee_id">
                <input type="hidden" name="school_unit_id" id="modal_unit_id">
                <input type="hidden" name="period" value="{{ $month }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Pegawai</label>
                    <div id="modal_employee_name" class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg text-slate-900 dark:text-slate-200 font-semibold border border-slate-200 dark:border-slate-700"></div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">File PDF <span class="text-rose-500">*</span></label>
                    <input type="file" name="payslip_file" accept=".pdf" required
                           class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer bg-white dark:bg-slate-900">
                    <p class="mt-2 text-xs text-slate-500">Hanya file PDF (Maks. 1MB)</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium text-sm">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">Upload File</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(empId, unitId, empName) {
            document.getElementById('modal_employee_id').value = empId;
            document.getElementById('modal_unit_id').value = unitId;
            document.getElementById('modal_employee_name').innerText = empName;

            const modal = document.getElementById('uploadModal');
            const content = document.getElementById('uploadModalContent');

            modal.classList.remove('hidden');
            void modal.offsetWidth;
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }

        function closeUploadModal() {
            const modal = document.getElementById('uploadModal');
            const content = document.getElementById('uploadModalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('uploadForm').reset();
            }, 300);
        }
    </script>

    <script>
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                let filter = this.value.toLowerCase();
                let rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    if (row.children.length > 1) {
                        let name = row.children[0].innerText.toLowerCase();
                        if (name.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }
    </script>
</x-admin-layout>
