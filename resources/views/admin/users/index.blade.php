<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{ 
        showAddModal: false, 
        showEditModal: false, 
        showDetailModal: false,
        selectedUser: null,
        editUser: { id: '', name: '', email: '', role: '' },
        openEdit(user) {
            this.editUser = { 
                id: user.id, 
                name: user.name, 
                email: user.email, 
                role: user.role
            };
            this.showEditModal = true;
        },
        openDetail(user) {
            this.selectedUser = user;
            this.showDetailModal = true;
        }
    }">

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50 font-nasalization">Manajemen Pengguna</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Kelola akun akses sistem dan hak role internal untuk aplikasi pusat HRD.</p>
            </div>
            <div>
                <button @click="showAddModal = true" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah User Baru
                </button>
            </div>
        </header>

        <!-- FILTERS -->
        <div class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end text-xs">
                <!-- Search Name/Email -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Cari Pengguna</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..." 
                        class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                </div>

                <!-- Filter Role -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Hak Akses / Role</label>
                    <select name="role" class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                        <option value="">Semua Role</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="hrd" {{ request('role') === 'hrd' ? 'selected' : '' }}>Staf HRD</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 h-9 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-semibold rounded-lg shadow-sm transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'role']))
                        <a href="{{ route('users.index') }}" class="h-9 px-3 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-750 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center transition-all cursor-pointer" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- USERS TABLE -->
        <div class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden text-left">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-150 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-6 py-3.5 text-left">Pengguna</th>
                            <th class="px-6 py-3.5 text-left">Email</th>
                            <th class="px-6 py-3.5 text-center">Role / Akses</th>
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-850/60 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/10 transition-colors">
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 font-bold flex items-center justify-center uppercase">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <button @click="openDetail({{ json_encode($user) }})" class="font-bold text-slate-900 dark:text-slate-50 hover:text-indigo-600 dark:hover:text-indigo-400 text-left hover:underline">
                                                {{ $user->name }}
                                            </button>
                                            @if(auth()->id() === $user->id)
                                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[8px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase">Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-left font-mono text-slate-655 dark:text-slate-400">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($user->role === 'super_admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-rose-50 dark:bg-rose-955/20 text-rose-700 dark:text-rose-400 border border-rose-150 dark:border-rose-900/30 uppercase">Super Admin</span>
                                    @elseif($user->role === 'hrd')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 dark:bg-indigo-955/20 text-indigo-700 dark:text-indigo-400 border border-indigo-150 dark:border-indigo-900/30 uppercase">Staf HRD</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 uppercase">{{ $user->role }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <button @click="openEdit({{ json_encode($user) }})" class="w-8 h-8 rounded-lg bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center transition-colors cursor-pointer" title="Edit Akun">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50/40 hover:bg-rose-50 dark:bg-rose-955/10 dark:hover:bg-rose-955/20 border border-rose-150/40 dark:border-rose-900/30 text-rose-600 dark:text-rose-450 flex items-center justify-center transition-colors cursor-pointer" title="Hapus Akun">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="user-x" class="w-8 h-8 text-slate-300 dark:text-slate-700 mb-2"></i>
                                        <p class="font-bold text-sm">Tidak ada user ditemukan</p>
                                        <p class="text-xs opacity-75">Silakan tambahkan baru atau ubah kriteria pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL 1: ADD USER -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-955/60 backdrop-blur-xs text-left" style="display: none;">
            <div @click.outside="showAddModal = false" class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden text-xs">
                <div class="p-5 border-b border-slate-150 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Tambah Akun User Baru
                    </h3>
                    <button @click="showAddModal = false" class="text-slate-455 hover:text-slate-700 dark:hover:text-slate-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form action="{{ route('users.store') }}" method="POST" x-data="{ selectedRole: '' }">
                    @csrf
                    <div class="p-5 space-y-4">
                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                            <input type="text" name="name" placeholder="Nama lengkap..." required 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Alamat Email</label>
                            <input type="email" name="email" placeholder="nama@email.com" required 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Password Akses</label>
                            <input type="password" name="password" placeholder="Minimal 8 karakter..." required 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>

                        <!-- Role -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Hak Akses (Role)</label>
                            <select name="role" x-model="selectedRole" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                <option value="">-- Pilih Role --</option>
                                <option value="hrd">Staf HRD</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-5 border-t border-slate-150 dark:border-slate-850 flex gap-2 justify-end">
                        <button type="button" @click="showAddModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-770 dark:text-slate-300 font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-850 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: EDIT USER -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-955/60 backdrop-blur-xs text-left" style="display: none;">
            <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden text-xs">
                <div class="p-5 border-b border-slate-150 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Edit Akun Pengguna
                    </h3>
                    <button @click="showEditModal = false" class="text-slate-455 hover:text-slate-700 dark:hover:text-slate-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form :action="`{{ url('users') }}/${editUser.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-5 space-y-4">
                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editUser.name" required 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Alamat Email</label>
                            <input type="email" name="email" x-model="editUser.email" required 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Password Baru <span class="text-[10px] text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span></label>
                            <input type="password" name="password" placeholder="Sandi baru..." 
                                class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800">
                        </div>

                        <!-- Role -->
                        <div class="space-y-1.5">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Hak Akses (Role)</label>
                            <select name="role" x-model="editUser.role" required class="w-full h-9 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-805 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:focus:ring-slate-800 cursor-pointer">
                                <option value="hrd">Staf HRD</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-5 border-t border-slate-150 dark:border-slate-850 flex gap-2 justify-end">
                        <button type="button" @click="showEditModal = false" class="h-9 px-4 bg-slate-50 hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-770 dark:text-slate-300 font-semibold rounded-lg border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">Batal</button>
                        <button type="submit" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-850 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: PROFILE DETAIL CARD -->
        <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-955/60 backdrop-blur-xs text-left" style="display: none;">
            <div @click.outside="showDetailModal = false" class="bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl max-w-md w-full overflow-hidden text-xs">
                <div class="p-5 border-b border-slate-150 dark:border-slate-850 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        Profil Pengguna
                    </h3>
                    <button @click="showDetailModal = false" class="text-slate-455 hover:text-slate-700 dark:hover:text-slate-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <div class="p-5 space-y-6">
                    <!-- User Account Card -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-indigo-50 dark:bg-indigo-955/40 text-indigo-650 dark:text-indigo-400 font-bold flex items-center justify-center text-2xl uppercase shadow-sm">
                            <span x-text="selectedUser ? selectedUser.name.substring(0,2) : ''"></span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-slate-50 font-nasalization" x-text="selectedUser ? selectedUser.name : ''"></h4>
                            <p class="text-slate-450 dark:text-slate-500 font-mono" x-text="selectedUser ? selectedUser.email : ''"></p>
                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 uppercase" x-text="selectedUser ? (selectedUser.role === 'super_admin' ? 'Super Admin' : 'Staf HRD') : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-slate-150 dark:border-slate-850 flex justify-end">
                    <button @click="showDetailModal = false" class="h-9 px-4 bg-slate-900 dark:bg-slate-100 hover:bg-slate-850 dark:hover:bg-slate-200 text-white dark:text-slate-900 font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
