<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        editId: null,
        editName: '',
        editApiUrl: '',
        editApiToken: '',
        editIsActive: true,
        openEdit(unit) {
            this.editId = unit.id;
            this.editName = unit.name;
            this.editApiUrl = unit.api_url;
            this.editApiToken = unit.api_token;
            this.editIsActive = !!unit.is_active;
            this.showEditModal = true;
        }
    }">

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

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Integrasi API Unit</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola koneksi REST API dan token otentikasi dari masing-masing unit sekolah untuk aggregator pusat.</p>
            </div>
            <div>
                <button @click="showAddModal = true" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Unit Baru
                </button>
            </div>
        </header>

        <!-- DATA LIST (CARDS GRID) -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
            @forelse($units as $unit)
                <div class="animate-card bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-sm relative">
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-650 dark:text-indigo-400 flex items-center justify-center shrink-0 font-bold">
                                    {{ substr($unit->name, 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $unit->name }}</h4>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">ID: {{ $unit->id }}</span>
                                </div>
                            </div>
                            <!-- Status Indicator -->
                            @if($unit->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30 uppercase">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-250/30 dark:border-rose-900/30 uppercase">Non-Aktif</span>
                            @endif
                        </div>

                        <div class="space-y-2 border-t border-slate-100 dark:border-slate-900 pt-3 text-xs">
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">REST Endpoint URL</span>
                                <code class="block font-mono text-[11px] text-slate-700 dark:text-slate-300 break-all bg-slate-50 dark:bg-slate-900/50 p-1.5 rounded border border-slate-100 dark:border-slate-900 mt-0.5">{{ $unit->api_url }}</code>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Authentication Token</span>
                                <div class="flex items-center gap-2 mt-0.5" x-data="{ showToken: false }">
                                    <code class="font-mono text-[11px] text-slate-600 dark:text-slate-400" x-text="showToken ? '{{ $unit->api_token }}' : '••••••••••••••••'"></code>
                                    <button @click="showToken = !showToken" type="button" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                                        <i :data-lucide="showToken ? 'eye-off' : 'eye'" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2.5 mt-5 border-t border-slate-50 dark:border-slate-900/60 pt-4 justify-end">
                        <button @click="openEdit({{ $unit }})" class="h-8 px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                            Edit
                        </button>
                        <form action="{{ route('school-units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit sekolah ini?')">
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
                <div class="col-span-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-12 text-center text-slate-400">
                    <div class="flex flex-col items-center justify-center gap-2.5 max-w-sm mx-auto">
                        <i data-lucide="settings-2" class="w-8 h-8 text-slate-300 dark:text-slate-700"></i>
                        <p class="text-xs font-semibold text-slate-900 dark:text-slate-50">Belum Ada Unit Sekolah Terdaftar</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Hubungkan API Unit Sekolah baru untuk mulai menarik data absensi secara gabungan.</p>
                        <button @click="showAddModal = true" class="mt-2 h-8 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg cursor-pointer">Tambah Unit Baru</button>
                    </div>
                </div>
            @endforelse
        </section>

        <!-- ADD MODAL -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs" style="display: none;" x-transition>
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md shadow-2xl p-6 relative text-left">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Tambah Unit Sekolah Baru</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Hubungkan unit sekolah baru ke dalam portal aggregator.</p>

                <form method="POST" action="{{ route('school-units.store') }}" class="mt-4 space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Unit</label>
                        <input type="text" name="name" required placeholder="Contoh: SD Unit, SMP Unit"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Endpoint API URL</label>
                        <input type="url" name="api_url" required placeholder="http://localhost:8000/api/v1/hrd"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Token API Otentikasi</label>
                        <input type="text" name="api_token" required placeholder="rahasia_sd_123"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="add_is_active" value="1" checked
                            class="rounded border-slate-350 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                        <label for="add_is_active" class="font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Aktifkan integrasi ini sekarang</label>
                    </div>

                    <div class="flex gap-2.5 pt-4 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-750 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 cursor-pointer">Batal</button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg cursor-pointer">Simpan Unit</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs" style="display: none;" x-transition>
            <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl w-full max-w-md shadow-2xl p-6 relative text-left">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">Edit Unit Sekolah</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Modifikasi parameter REST API unit sekolah yang terhubung.</p>

                <form method="POST" :action="`{{ url('school-units') }}/${editId}`" class="mt-4 space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Nama Unit</label>
                        <input type="text" name="name" required x-model="editName"
                            class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Endpoint API URL</label>
                        <input type="url" name="api_url" required x-model="editApiUrl"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-1">Token API Otentikasi</label>
                        <input type="text" name="api_token" required x-model="editApiToken"
                            class="w-full h-9 px-3 font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editIsActive"
                            class="rounded border-slate-350 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                        <label for="edit_is_active" class="font-medium text-slate-700 dark:text-slate-300 cursor-pointer">Aktifkan integrasi ini sekarang</label>
                    </div>

                    <div class="flex gap-2.5 pt-4 justify-end">
                        <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-750 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 cursor-pointer">Batal</button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-semibold rounded-lg cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
