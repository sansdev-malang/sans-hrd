<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-[60] md:z-20 flex flex-col w-64 h-full md:h-screen max-h-screen bg-white dark:bg-[#09090b] border-r border-slate-200 dark:border-slate-800 p-3 shrink-0 transition-transform duration-300 -translate-x-full md:translate-x-0 md:relative shadow-sm md:shadow-none overflow-hidden">

    <style>
        .sidebar-collapsed .sidebar-dropdown-content {
            display: none !important;
        }
    </style>

    <!-- App Brand / Dashboard Link -->
    <a href="{{ route('dashboard') }}"
        class="workspace-selector flex items-center justify-between p-2 mb-3 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg cursor-pointer transition-colors relative group shrink-0">
        <div class="flex items-center gap-2.5">
            @if (setting('app_logo'))
                <img src="{{ asset('storage/' . setting('app_logo')) }}" alt="Logo" class="w-8 h-8 rounded-lg object-cover shrink-0 shadow-sm">
            @else
                <div class="w-8 h-8 rounded-lg logo-bg flex items-center justify-center shrink-0 shadow-sm">
                    <span class="text-white text-lg font-bold" style="font-family: 'Nasalization Rg', sans-serif; font-weight: 400;">
                        {{ substr(setting('app_name', 'SANS HRD'), 0, 1) }}
                    </span>
                </div>
            @endif
            <div class="school-info overflow-hidden">
                <h1 class="text-lg text-slate-900 dark:text-slate-50 truncate leading-normal tracking-wide" style="font-family: 'Nasalization Rg', sans-serif; font-weight: 400;">
                    {{ setting('app_name', 'SANS HRD') }}
                </h1>
            </div>
        </div>

        <!-- Tooltip for collapsed view -->
        <span
            class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50" style="font-family: 'Nasalization Rg', sans-serif; font-weight: 400;">
            {{ setting('app_name', 'SANS HRD') }}
        </span>
    </a>

    <!-- Navigation Links -->
    <div id="sidebar-nav-container" class="flex-1 min-h-0 space-y-4 overflow-y-auto px-1 py-1 custom-sidebar-scroll">
        <!-- Main: Dashboard -->
        <div>
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('dashboard') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="layout-dashboard" class="menu-icon w-4 h-4 shrink-0"></i>
                    <span class="menu-text">Dashboard</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Dashboard
                    </span>
                </a>
            </nav>
        </div>

        <!-- Group 1: MANAJEMEN PEGAWAI -->
        <div>
            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                Manajemen Pegawai
            </h3>
            <nav class="space-y-1">
                <!-- Data Pegawai -->
                <a href="{{ route('employees.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('employees.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="users-2" class="menu-icon w-4 h-4 shrink-0"></i>
                        <span class="menu-text">Data Pegawai</span>
                    </div>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Data Pegawai
                    </span>
                </a>

                <!-- Rapor Kinerja -->
                <a href="{{ route('performance-reports.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('performance-reports.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="bar-chart-2" class="menu-icon w-4 h-4 shrink-0"></i>
                        <span class="menu-text">Rapor Kinerja</span>
                    </div>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Rapor Kinerja
                    </span>
                </a>
            </nav>
        </div>

        <!-- Group 2: PRESENSI & KEHADIRAN -->
        <div>
            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                Presensi & Kehadiran
            </h3>
            <nav class="space-y-1">
                <!-- Dropdown: Kehadiran Pegawai -->
                <div x-data="{ openAtt: {{ Request::routeIs('attendance-logs.*', 'attendance-history.*', 'attendance-percentage-reports.*') ? 'true' : 'false' }} }">
                    <button @click="openAtt = !openAtt"
                        class="menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors text-xs font-medium relative group cursor-pointer {{ Request::routeIs('attendance-logs.*', 'attendance-history.*', 'attendance-percentage-reports.*') ? 'text-slate-900 dark:text-slate-50 bg-slate-100 dark:bg-slate-800 font-medium' : '' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="scan-face" class="menu-icon w-4 h-4 shrink-0"></i>
                            <span class="menu-text">Kehadiran Pegawai</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron-icon w-3.5 h-3.5 transition-transform duration-200"
                            :style="openAtt ? 'transform: rotate(90deg);' : ''"></i>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                            Kehadiran Pegawai
                        </span>
                    </button>

                    <!-- Dropdown content with line connector -->
                    <div x-show="openAtt" x-collapse
                        class="sidebar-dropdown-content mt-1 ml-5 pl-4 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <a href="{{ route('attendance-logs.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('attendance-logs.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Data Kehadiran</span>
                        </a>
                        <a href="{{ route('attendance-history.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('attendance-history.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Riwayat Kehadiran</span>
                        </a>
                        <a href="{{ route('attendance-percentage-reports.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('attendance-percentage-reports.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Persentase Kehadiran</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Izin & Cuti Pegawai -->
                <div x-data="{ openLeave: {{ Request::routeIs('leave-approvals.*', 'leave-types.*') ? 'true' : 'false' }} }">
                    <button @click="openLeave = !openLeave"
                        class="menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors text-xs font-medium relative group cursor-pointer {{ Request::routeIs('leave-approvals.*', 'leave-types.*') ? 'text-slate-900 dark:text-slate-50 bg-slate-100 dark:bg-slate-800 font-medium' : '' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="file-check" class="menu-icon w-4 h-4 shrink-0"></i>
                            <span class="menu-text">Izin & Cuti</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron-icon w-3.5 h-3.5 transition-transform duration-200"
                            :style="openLeave ? 'transform: rotate(90deg);' : ''"></i>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                            Izin & Cuti
                        </span>
                    </button>

                    <!-- Dropdown content with line connector -->
                    <div x-show="openLeave" x-collapse
                        class="sidebar-dropdown-content mt-1 ml-5 pl-4 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <a href="{{ route('leave-approvals.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('leave-approvals.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Persetujuan Izin</span>
                        </a>
                        <a href="{{ route('leave-types.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('leave-types.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Tipe Izin</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Uang Kehadiran -->
                <div x-data="{ openBonus: {{ (Request::routeIs('bonus-schemas.*') || Request::routeIs('bonus-reports.*')) ? 'true' : 'false' }} }">
                    <button @click="openBonus = !openBonus"
                        class="menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors text-xs font-medium relative group cursor-pointer {{ (Request::routeIs('bonus-schemas.*') || Request::routeIs('bonus-reports.*')) ? 'text-slate-900 dark:text-slate-50 bg-slate-100 dark:bg-slate-800 font-medium' : '' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="award" class="menu-icon w-4 h-4 shrink-0"></i>
                            <span class="menu-text">Uang Kehadiran</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron-icon w-3.5 h-3.5 transition-transform duration-200"
                            :style="openBonus ? 'transform: rotate(90deg);' : ''"></i>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                            Uang Kehadiran
                        </span>
                    </button>

                    <!-- Dropdown content with line connector -->
                    <div x-show="openBonus" x-collapse
                        class="sidebar-dropdown-content mt-1 ml-5 pl-4 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <a href="{{ route('bonus-reports.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('bonus-reports.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Rekap Uang Kehadiran</span>
                        </a>
                        <a href="{{ route('bonus-schemas.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('bonus-schemas.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Skema Uang Kehadiran</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Jadwal & Shift Kerja -->
                <div x-data="{ openShift: {{ (Request::routeIs('working-shifts.*') || Request::routeIs('employee-working-shifts.*') || Request::routeIs('picket-schedules.*') || Request::routeIs('holidays.*')) ? 'true' : 'false' }} }">
                    <button @click="openShift = !openShift"
                        class="menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors text-xs font-medium relative group cursor-pointer {{ (Request::routeIs('working-shifts.*') || Request::routeIs('employee-working-shifts.*') || Request::routeIs('picket-schedules.*') || Request::routeIs('holidays.*')) ? 'text-slate-900 dark:text-slate-50 bg-slate-100 dark:bg-slate-800 font-medium' : '' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="calendar-clock" class="menu-icon w-4 h-4 shrink-0"></i>
                            <span class="menu-text">Jadwal & Shift Kerja</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron-icon w-3.5 h-3.5 transition-transform duration-200"
                            :style="openShift ? 'transform: rotate(90deg);' : ''"></i>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                            Jadwal & Shift Kerja
                        </span>
                    </button>

                    <!-- Dropdown content with line connector -->
                    <div x-show="openShift" x-collapse
                        class="sidebar-dropdown-content mt-1 ml-5 pl-4 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <a href="{{ route('working-shifts.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('working-shifts.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Shift Kerja</span>
                        </a>
                        <a href="{{ route('employee-working-shifts.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('employee-working-shifts.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Jadwal Pegawai</span>
                        </a>
                        <a href="{{ route('picket-schedules.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('picket-schedules.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Jadwal Piket Unit</span>
                        </a>
                        <a href="{{ route('holidays.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('holidays.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Hari Libur & Kalender</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Group 3: PENGGAJIAN & FINANSIAL -->
        <div>
            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                Penggajian
            </h3>
            <nav class="space-y-1">
                <!-- Slip Gaji -->
                <a href="{{ route('payslips.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('payslips.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="wallet" class="menu-icon w-4 h-4 shrink-0"></i>
                        <span class="menu-text">Slip Gaji</span>
                    </div>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Slip Gaji
                    </span>
                </a>

                <!-- Setting Cut-OFF -->
                <a href="{{ route('cutoff-settings.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('cutoff-settings.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="scissors" class="menu-icon w-4 h-4 shrink-0"></i>
                        <span class="menu-text">Setting Cut-OFF</span>
                    </div>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Setting Cut-OFF
                    </span>
                </a>
            </nav>
        </div>

        <!-- Group 4: LAYANAN & INFORMASI -->
        <div>
            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                Layanan & Informasi
            </h3>
            <nav class="space-y-1">
                <!-- Pengumuman -->
                <a href="{{ route('announcements.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('announcements.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="megaphone" class="menu-icon w-4 h-4 shrink-0"></i>
                        <span class="menu-text">Pengumuman</span>
                    </div>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Pengumuman
                    </span>
                </a>

                <!-- Layanan SDM -->
                <a href="{{ route('layanan-sdm.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('layanan-sdm.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="help-circle" class="menu-icon w-4 h-4 shrink-0"></i>
                        <span class="menu-text">Layanan SDM</span>
                    </div>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Layanan SDM
                    </span>
                </a>
            </nav>
        </div>

        <!-- Group 5: PENGATURAN SISTEM (Khusus Super Admin) -->
        @if(auth()->user()->hasRole('super_admin'))
        <div>
            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                Pengaturan Sistem
            </h3>
            <nav class="space-y-1">
                <!-- Dropdown: Integrasi & Mesin -->
                <div x-data="{ openDevices: {{ Request::routeIs('school-units.*', 'zkteco-devices.*', 'settings.adms', 'raw-attendance-logs.*') ? 'true' : 'false' }} }">
                    <button @click="openDevices = !openDevices"
                        class="menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors text-xs font-medium relative group cursor-pointer {{ Request::routeIs('school-units.*', 'zkteco-devices.*', 'settings.adms', 'raw-attendance-logs.*') ? 'text-slate-900 dark:text-slate-50 bg-slate-100 dark:bg-slate-800 font-medium' : '' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="network" class="menu-icon w-4 h-4 shrink-0"></i>
                            <span class="menu-text">Integrasi & Mesin</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron-icon w-3.5 h-3.5 transition-transform duration-200"
                            :style="openDevices ? 'transform: rotate(90deg);' : ''"></i>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                            Integrasi & Mesin
                        </span>
                    </button>

                    <!-- Dropdown content with line connector -->
                    <div x-show="openDevices" x-collapse
                        class="sidebar-dropdown-content mt-1 ml-5 pl-4 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <a href="{{ route('school-units.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('school-units.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Integrasi API Unit</span>
                        </a>
                        <a href="{{ route('zkteco-devices.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('zkteco-devices.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Mesin Absensi</span>
                        </a>
                        <a href="{{ route('settings.adms') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('settings.adms') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Sinkronisasi ADMS</span>
                        </a>
                        <a href="{{ route('raw-attendance-logs.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('raw-attendance-logs.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Log Absensi Mentah</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Konfigurasi Sistem -->
                <div x-data="{ openSys: {{ Request::routeIs('users.*', 'settings.index', 'system-logs.*') ? 'true' : 'false' }} }">
                    <button @click="openSys = !openSys"
                        class="menu-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors text-xs font-medium relative group cursor-pointer {{ Request::routeIs('users.*', 'settings.index', 'system-logs.*') ? 'text-slate-900 dark:text-slate-50 bg-slate-100 dark:bg-slate-800 font-medium' : '' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="settings" class="menu-icon w-4 h-4 shrink-0"></i>
                            <span class="menu-text">Konfigurasi Sistem</span>
                        </div>
                        <i data-lucide="chevron-right" class="chevron-icon w-3.5 h-3.5 transition-transform duration-200"
                            :style="openSys ? 'transform: rotate(90deg);' : ''"></i>
                        <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                            Konfigurasi Sistem
                        </span>
                    </button>

                    <!-- Dropdown content with line connector -->
                    <div x-show="openSys" x-collapse
                        class="sidebar-dropdown-content mt-1 ml-5 pl-4 border-l border-slate-200 dark:border-slate-800 space-y-1">
                        <a href="{{ route('users.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('users.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Manajemen User</span>
                        </a>
                        <a href="{{ route('settings.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('settings.index') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Setting Aplikasi</span>
                        </a>
                        <a href="{{ route('system-logs.index') }}"
                            class="flex items-center justify-between gap-2 py-1 text-xs font-medium {{ Request::routeIs('system-logs.*') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }} transition-colors">
                            <span>Log Sistem</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        @endif
    </div>

    <!-- Bottom User Account Profile Menu -->
    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 relative" x-data="{ open: false }">
        <!-- Dropdown menu -->
        <div x-show="open" @click.outside="open = false"
            class="absolute bottom-full left-0 w-60 mb-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1.5 z-50 transition-all origin-bottom-left"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95" style="display: none;">

            <!-- Profil Saya -->
            <a href="{{ route('profile.edit') }}"
                class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 rounded-lg transition-colors m-1">
                <i data-lucide="user" class="w-4 h-4 text-slate-500"></i>
                <span>Profil Saya</span>
            </a>

            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>

            <!-- Log Out -->
            <form action="{{ route('logout') }}" method="POST" class="p-1">
                @csrf
                <button type="submit"
                    class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4 text-rose-500 dark:text-rose-400"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>

        <div @click="open = !open"
            class="user-selector flex items-center justify-between p-2 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg cursor-pointer transition-colors relative group">
            <div class="flex items-center gap-2.5 overflow-hidden">
                @php
                    $nameParts = explode(' ', Auth::user()->name);
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    $photo = null;
                    if (method_exists(Auth::user(), 'employee') && Auth::user()->employee) {
                        $photo = Auth::user()->employee->photo;
                    } elseif (isset(Auth::user()->photo)) {
                        $photo = Auth::user()->photo;
                    }
                @endphp

                @if($photo)
                    <img src="{{ asset('storage/' . $photo) }}" alt="Avatar" class="w-7 h-7 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-800 shrink-0">
                @else
                    <div class="w-7 h-7 rounded-lg bg-indigo-900/30 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ $initials }}
                    </div>
                @endif
                <div class="user-info overflow-hidden">
                    <h4 class="text-xs font-semibold text-slate-900 dark:text-slate-50 truncate leading-none">
                        {{ Auth::user()->name }}
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-1">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <i data-lucide="chevrons-up-down" class="chevron-icon w-4 h-4 text-slate-400 shrink-0 ml-1"></i>

            <!-- Tooltip for collapsed view -->
            <span
                class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                {{ Auth::user()->name }}
            </span>
        </div>
    </div>

</aside>
