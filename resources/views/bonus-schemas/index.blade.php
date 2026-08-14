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

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2.5">
                    <span>Skema Bonus Kehadiran</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 border border-indigo-100/30 dark:border-indigo-900/30 uppercase tracking-wider shrink-0 font-sans">Bonus</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola jenjang nominal bonus harian pegawai berdasarkan tingkat keterlambatan absensi masuk.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bonus-schemas.sync') }}" class="h-9 px-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-350 text-xs font-bold rounded-xl shadow-3xs border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    <span>Sync Ulang ke Unit</span>
                </a>
                <button @click="resetAdd()" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-105 duration-150 flex items-center gap-1.5 border-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Buat Skema Baru</span>
                </button>
            </div>
        </header>

        <!-- CARDS GRID -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-left">
            @forelse($schemas as $schema)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $schema->name }}</h4>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">ID: {{ $schema->id }}</span>
                            </div>
                            @if($schema->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-450 border border-emerald-100/30 dark:border-emerald-900/30 uppercase">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-650 dark:text-rose-455 border border-rose-100/30 dark:border-rose-900/30 uppercase">Non-Aktif</span>
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

                    <div class="flex gap-2.5 mt-5 border-t border-slate-100 dark:border-slate-900 pt-4 justify-end">
                        <button @click="openEdit({{ json_encode($schema) }})" class="h-8 px-3.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-855 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-350 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-800 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                            Edit Skema
                        </button>
                        <form action="{{ route('bonus-schemas.destroy', $schema->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus skema bonus ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-8 px-3.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 text-rose-650 dark:text-rose-400 text-xs font-bold rounded-lg border border-rose-100/30 dark:border-rose-900/30 transition-all hover:scale-105 duration-150 flex items-center gap-1.5 cursor-pointer border-0">
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
        <div x-show="showAddModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAddModal" @click.away="showAddModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-xl p-6 text-left flex flex-col max-h-[90vh]">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Buat Skema Bonus Baru</h3>
                            <button @click="showAddModal = false" class="text-slate-450 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('bonus-schemas.store') }}" class="space-y-4 text-xs overflow-y-auto pr-1">
                            @csrf
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Skema</label>
                                <input type="text" name="name" required placeholder="Contoh: Skema Guru & Staff" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                <label for="is_active" class="ml-2 font-semibold text-slate-750 dark:text-slate-300 select-none">Skema Aktif</label>
                            </div>

                            <!-- Dynamic Tiers List -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block font-bold text-slate-750 dark:text-slate-300 uppercase tracking-wide text-[10px]">Tingkatan Bonus (Tiers)</label>
                                    <button type="button" @click="addTier()" class="text-indigo-650 dark:text-indigo-400 font-bold hover:underline cursor-pointer border-0 bg-transparent flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Tambah Tier</span>
                                    </button>
                                </div>
                                
                                <div class="space-y-3 bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/40">
                                    <template x-for="(tier, index) in tiers" :key="index">
                                        <div class="flex flex-row items-end gap-3 pb-3 border-b border-slate-200/50 dark:border-slate-800/40 last:border-0 last:pb-0">
                                            <div class="flex-none w-8 flex flex-col items-center justify-center pb-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-400" x-text="tier.tier_level"></div>
                                                <input type="hidden" :name="`tiers[${index}][tier_level]`" x-model="tier.tier_level">
                                            </div>
                                            
                                            <div class="flex-1">
                                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                                                <input type="number" :name="`tiers[${index}][nominal]`" x-model="tier.nominal" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-right font-mono font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                            </div>
                                            
                                            <div class="flex-1">
                                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Maks. Telat (Mnt)</label>
                                                <input type="number" :name="`tiers[${index}][max_late_minutes]`" x-model="tier.max_late_minutes" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                            </div>
                                            
                                            <div class="flex-none w-20">
                                                <button type="button" @click="removeTier(index)" class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-455 bg-rose-50 dark:bg-rose-900/20 border border-rose-200/50 dark:border-rose-900/30 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer mb-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m12 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Skema
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="relative z-50" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity z-50"></div>
            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showEditModal" @click.away="showEditModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-xl p-6 text-left flex flex-col max-h-[90vh]">
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-900 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Edit Skema Bonus Kehadiran</h3>
                            <button @click="showEditModal = false" class="text-slate-450 hover:text-slate-650 transition-colors border-0 bg-transparent cursor-pointer">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <form method="POST" :action="`{{ url('bonus-schemas') }}/${editId}`" class="space-y-4 text-xs overflow-y-auto pr-1">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Skema</label>
                                <input type="text" name="name" required x-model="editName" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editIsActive" class="rounded border-slate-300 dark:border-slate-800 text-indigo-650 focus:ring-indigo-500 w-4 h-4 bg-white dark:bg-slate-950">
                                <label for="edit_is_active" class="ml-2 font-semibold text-slate-750 dark:text-slate-300 select-none">Skema Aktif</label>
                            </div>

                            <!-- Dynamic Tiers List -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block font-bold text-slate-750 dark:text-slate-300 uppercase tracking-wide text-[10px]">Tingkatan Bonus (Tiers)</label>
                                    <button type="button" @click="addTier()" class="text-indigo-650 dark:text-indigo-400 font-bold hover:underline cursor-pointer border-0 bg-transparent flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        <span>Tambah Tier</span>
                                    </button>
                                </div>
                                
                                <div class="space-y-3 bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/40">
                                    <template x-for="(tier, index) in tiers" :key="index">
                                        <div class="flex flex-row items-end gap-3 pb-3 border-b border-slate-200/50 dark:border-slate-800/40 last:border-0 last:pb-0">
                                            <div class="flex-none w-8 flex flex-col items-center justify-center pb-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-slate-400" x-text="tier.tier_level"></div>
                                                <input type="hidden" :name="`tiers[${index}][tier_level]`" x-model="tier.tier_level">
                                            </div>
                                            
                                            <div class="flex-1">
                                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                                                <input type="number" :name="`tiers[${index}][nominal]`" x-model="tier.nominal" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-right font-mono font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                            </div>
                                            
                                            <div class="flex-1">
                                                <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Maks. Telat (Mnt)</label>
                                                <input type="number" :name="`tiers[${index}][max_late_minutes]`" x-model="tier.max_late_minutes" required class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-center font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                            </div>
                                            
                                            <div class="flex-none w-20">
                                                <button type="button" @click="removeTier(index)" class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-455 bg-rose-50 dark:bg-rose-900/20 border border-rose-200/50 dark:border-rose-900/30 rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors cursor-pointer mb-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.78 0L9 9m12 6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-900 justify-end">
                                <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-white dark:bg-slate-900 border border-slate-350 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl shadow-3xs transition-all cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit" class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all hover:scale-[1.02] duration-150 border-0 cursor-pointer">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
