<x-admin-layout>
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">Data Slip Gaji</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Unggah dan kelola slip gaji (PDF) per pegawai.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-sm mb-6 flex flex-col md:flex-row md:items-end gap-4">
        @if(in_array(auth()->user()->role, ['super_admin', 'hrd']))
        <div class="w-full md:w-64">
            <label for="searchInput" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 uppercase tracking-wide">Cari Pegawai</label>
            <input type="text" id="searchInput" placeholder="Ketik nama..." class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-blue-500 focus:border-blue-500">
        </div>
        @endif
        
        <form method="GET" action="{{ route('payslips.index') }}" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="w-full md:w-64">
                <label for="month" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 uppercase tracking-wide">Periode Bulan</label>
                <input type="month" id="month" name="month" value="{{ $month }}" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
            </div>
            
            <div class="w-full md:w-64">
                <label for="unit_id" class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1 uppercase tracking-wide">Filter Unit</label>
                <select id="unit_id" name="unit_id" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-sm">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Table List -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden w-full text-left">
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
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-900/30 transition-colors">
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
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Kosong
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($emp['payslip'])
                                        <a href="{{ Storage::url($emp['payslip']->file_path) }}" target="_blank" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Lihat Slip">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('payslips.destroy', $emp['payslip']->id) }}" onsubmit="return confirm('Hapus slip gaji ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors" title="Hapus">
                                                <i data-lucide="x" class="w-4 h-4 mr-2"></i> Kosong
                                            </button>
                                        </form>
                                    @else
                                        <button onclick="openUploadModal('{{ $emp['id'] }}', '{{ $emp['unit_id'] }}', '{{ addslashes($emp['name']) }}')" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-medium rounded-lg hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors">
                                            <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Upload
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data pegawai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800 transform scale-95 opacity-0 transition-all duration-300" id="uploadModalContent">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Upload Slip Gaji</h3>
            <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                <input type="file" name="payslip_file" accept=".pdf" required class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-400 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer bg-white dark:bg-slate-900">
                <p class="mt-2 text-xs text-slate-500">Hanya file PDF (Maks. 1MB)</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm">Upload File</button>
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
        // trigger reflow
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
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (row.children.length > 1) { // Skip empty state row
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
