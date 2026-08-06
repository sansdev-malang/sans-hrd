<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showEditModal: false,
        editId: null,
        editName: '',
        editIsActive: true,
        tiers: [
            { tier_level: 1, nominal: 10000, max_late_minutes: 0, max_absent_days: 0 },
            { tier_level: 2, nominal: 9000, max_late_minutes: 5, max_absent_days: 0 },
            { tier_level: 3, nominal: 8000, max_late_minutes: 10, max_absent_days: 0 }
        ],
        addTier() {
            let nextLevel = this.tiers.length + 1;
            let lastNominal = this.tiers.length > 0 ? this.tiers[this.tiers.length - 1].nominal : 10000;
            this.tiers.push({
                tier_level: nextLevel,
                nominal: Math.max(0, lastNominal - 1000),
                max_late_minutes: this.tiers.length > 0 ? this.tiers[this.tiers.length - 1].max_late_minutes + 5 : 5,
                max_absent_days: 0
            });
        },
        removeTier(index) {
            this.tiers.splice(index, 1);
            // Re-index levels
            this.tiers.forEach((t, i) => {
                t.tier_level = i + 1;
            });
        },
        openEdit(schema) {
            this.editId = schema.id;
            this.editName = schema.name;
            this.editIsActive = !!schema.is_active;
            this.tiers = schema.tiers.map(t => ({
                tier_level: t.tier_level,
                nominal: parseFloat(t.nominal),
                max_late_minutes: t.max_late_minutes,
                max_absent_days: t.max_absent_days
            }));
            this.showEditModal = true;
        },
        resetAdd() {
            this.editName = '';
            this.tiers = [
                { tier_level: 1, nominal: 10000, max_late_minutes: 0, max_absent_days: 0 },
                { tier_level: 2, nominal: 9000, max_late_minutes: 5, max_absent_days: 0 },
                { tier_level: 3, nominal: 8000, max_late_minutes: 10, max_absent_days: 0 },
                { tier_level: 4, nominal: 7000, max_late_minutes: 15, max_absent_days: 0 },
                { tier_level: 5, nominal: 6000, max_late_minutes: 20, max_absent_days: 0 },
                { tier_level: 6, nominal: 5000, max_late_minutes: 25, max_absent_days: 0 },
                { tier_level: 7, nominal: 4000, max_late_minutes: 30, max_absent_days: 0 },
                { tier_level: 8, nominal: 3000, max_late_minutes: 40, max_absent_days: 0 }
            ];
            this.showAddModal = true;
        }
    }">

        <!-- SUCCESS ALERT -->
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

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Skema Bonus Kehadiran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola jenjang nominal bonus harian pegawai berdasarkan tingkat keterlambatan absensi masuk.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('bonus-schemas.sync') }}" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-lg shadow-sm border border-slate-200 dark:border-slate-800 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Sync Ulang ke Unit
                </a>
                <button @click="resetAdd()" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Buat Skema Baru
                </button>
            </div>
        </header>

        <!-- CARDS GRID -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-left">
            @forelse($schemas as $schema)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-sm">
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $schema->name }}</h4>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">ID: {{ $schema->id }}</span>
                            </div>
                            @if($schema->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30 uppercase">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400 border border-rose-200/30 dark:border-rose-900/30 uppercase">Non-Aktif</span>
                            @endif
                        </div>

                        <!-- Tiers Table -->
                        <div class="border border-slate-100 dark:border-slate-800 rounded-lg overflow-hidden mt-3">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Tingkat (Tier)</th>
                                        <th class="px-4 py-2 text-center">Batas Telat</th>
                                        <th class="px-4 py-2 text-right">Nominal Bonus</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-900 text-slate-700 dark:text-slate-300 font-medium">
                                    @foreach($schema->tiers->sortBy('tier_level') as $tier)
                                        <tr>
                                            <td class="px-4 py-2.5 text-left flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center">
                                                    {{ $tier->tier_level }}
                                                </span>
                                                Tier {{ $tier->tier_level }}
                                            </td>
                                            <td class="px-4 py-2.5 text-center font-mono text-slate-800 dark:text-slate-200">
                                                @if($tier->max_late_minutes == 0)
                                                    Tepat Waktu (0 menit)
                                                @else
                                                    &le; {{ $tier->max_late_minutes }} menit
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($tier->nominal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex gap-2.5 mt-5 border-t border-slate-50 dark:border-slate-900/60 pt-4 justify-end">
                        <button @click="openEdit({{ json_encode($schema) }})" class="h-8 px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer flex items-center gap-1.5">
                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                            Edit Skema
                        </button>
                        <form action="{{ route('bonus-schemas.destroy', $schema->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skema bonus ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-8 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-lg border border-rose-200/30 dark:border-rose-900/30 transition-all cursor-pointer flex items-center gap-1.5">
                                <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900">
                    <i data-lucide="award" class="w-8 h-8 mx-auto text-slate-400 mb-2"></i>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada skema bonus yang terdaftar.</p>
                </div>
            @endforelse
        </section>

        <!-- ADD MODAL -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showAddModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Buat Skema Bonus Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('bonus-schemas.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Skema</label>
                        <input type="text" name="name" required placeholder="Contoh: Skema Guru & Staff" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <label for="is_active" class="ml-2 font-semibold text-slate-750 dark:text-slate-300">Skema Aktif</label>
                    </div>

                    <!-- Dynamic Tiers List -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block font-bold text-slate-750 dark:text-slate-300 uppercase tracking-wide text-[10px]">Tingkatan Bonus (Tiers)</label>
                            <button type="button" @click="addTier()" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer flex items-center gap-1">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                Tambah Tier
                            </button>
                        </div>
                        
                        <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-900">
                            <template x-for="(tier, index) in tiers" :key="index">
                                                                <div class="flex flex-row items-end gap-3 pb-3 border-b border-slate-100 dark:border-slate-900 last:border-0 last:pb-0">
                                    <div class="flex-none w-8 flex flex-col items-center justify-center pb-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-400" x-text="tier.tier_level"></div>
                                        <input type="hidden" :name="`tiers[${index}][tier_level]`" x-model="tier.tier_level">
                                    </div>
                                    
                                    <div class="flex-1">
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                                        <input type="number" :name="`tiers[${index}][nominal]`" x-model="tier.nominal" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-right font-mono font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                    </div>
                                    
                                    <div class="flex-1">
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Maks. Telat (Mnt)</label>
                                        <input type="number" :name="`tiers[${index}][max_late_minutes]`" x-model="tier.max_late_minutes" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                    </div>
                                    
                                    <div class="flex-none w-20">
                                        <button type="button" @click="removeTier(index)" class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 border border-rose-200/50 dark:border-rose-900/30 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer mb-0.5">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Skema
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" style="display: none;">
            <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 text-left">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Edit Skema Bonus Kehadiran</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('bonus-schemas') }}/${editId}`" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Skema</label>
                        <input type="text" name="name" required x-model="editName" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editIsActive" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <label for="edit_is_active" class="ml-2 font-semibold text-slate-750 dark:text-slate-300">Skema Aktif</label>
                    </div>

                    <!-- Dynamic Tiers List -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block font-bold text-slate-750 dark:text-slate-300 uppercase tracking-wide text-[10px]">Tingkatan Bonus (Tiers)</label>
                            <button type="button" @click="addTier()" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline cursor-pointer flex items-center gap-1">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                Tambah Tier
                            </button>
                        </div>
                        
                        <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-900">
                            <template x-for="(tier, index) in tiers" :key="index">
                                                                <div class="flex flex-row items-end gap-3 pb-3 border-b border-slate-100 dark:border-slate-900 last:border-0 last:pb-0">
                                    <div class="flex-none w-8 flex flex-col items-center justify-center pb-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-400" x-text="tier.tier_level"></div>
                                        <input type="hidden" :name="`tiers[${index}][tier_level]`" x-model="tier.tier_level">
                                    </div>
                                    
                                    <div class="flex-1">
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                                        <input type="number" :name="`tiers[${index}][nominal]`" x-model="tier.nominal" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-right font-mono font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                    </div>
                                    
                                    <div class="flex-1">
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Maks. Telat (Mnt)</label>
                                        <input type="number" :name="`tiers[${index}][max_late_minutes]`" x-model="tier.max_late_minutes" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                    </div>
                                    
                                    <div class="flex-none w-20">
                                        <button type="button" @click="removeTier(index)" class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 border border-rose-200/50 dark:border-rose-900/30 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer mb-0.5">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                        <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
