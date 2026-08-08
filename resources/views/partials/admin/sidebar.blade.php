<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 bg-white dark:bg-[#09090b] border-r border-slate-200 dark:border-slate-800 p-3 shrink-0 transition-transform duration-300 -translate-x-full md:translate-x-0 md:relative md:z-20 shadow-sm md:shadow-none">

    <!-- Workspace Selector -->
    <div
        class="workspace-selector flex items-center justify-between p-2 mb-4 hover:bg-slate-100 dark:hover:bg-slate-900 rounded-lg cursor-pointer transition-colors relative group">
        <div class="flex items-center gap-2.5">
            @if (setting('app_logo'))
                <img src="{{ asset('storage/' . setting('app_logo')) }}" alt="Logo" class="w-8 h-8 rounded-lg logo-bg p-1.5 object-contain shrink-0 shadow-sm">
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
        <!-- Dropdown selector arrow -->
        <i data-lucide="chevrons-up-down" class="chevron-icon w-4 h-4 text-slate-400 shrink-0 ml-1"></i>

        <!-- Tooltip for collapsed view -->
        <span
            class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50" style="font-family: 'Nasalization Rg', sans-serif; font-weight: 400;" >
            {{ setting('app_name', 'SANS HRD') }}
        </span>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 space-y-4 overflow-y-auto px-1 py-2 no-scrollbar">
        <div>
            <nav class="space-y-1 mb-4">
                <a href="{{ route('dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('dashboard') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="layout-dashboard" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Dashboard</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Dashboard
                    </span>
                </a>
            </nav>

            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2 mt-4">MANAJEMEN PEGAWAI</h3>
            <nav class="space-y-1 mb-4">
                <a href="{{ route('employees.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('employees.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="users-2" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Data Pegawai</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Data Pegawai
                    </span>
                </a>
                <a href="{{ route('announcements.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('announcements.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="megaphone" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Pengumuman</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Pengumuman
                    </span>
                </a>
                <a href="{{ route('coming-soon') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg {{ Request::routeIs('coming-soon') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }} text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="award" class="menu-icon w-4 h-4"></i>
                        <span class="menu-text">Sertifikat Pegawai</span>
                    </div>
                    <span class="menu-text text-[9px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded uppercase tracking-wider">Dev</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Sertifikat Pegawai
                    </span>
                </a>
                <a href="{{ route('coming-soon') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg {{ Request::routeIs('coming-soon') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }} text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="star" class="menu-icon w-4 h-4"></i>
                        <span class="menu-text">Prestasi Pegawai</span>
                    </div>
                    <span class="menu-text text-[9px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded uppercase tracking-wider">Dev</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Prestasi Pegawai
                    </span>
                </a>
                <a href="{{ route('performance-reports.index') }}" class="menu-item flex items-center justify-between gap-3 px-3 py-2 rounded-lg {{ Request::routeIs('performance-reports.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }} text-xs relative group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="bar-chart-2" class="menu-icon w-4 h-4"></i>
                        <span class="menu-text">Rapor Kinerja</span>
                    </div>
                    <span class="menu-text text-[9px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded uppercase tracking-wider">Dev</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Rapor Kinerja
                    </span>
                </a>
            </nav>

            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2 mt-4">MANAJEMEN ABSENSI</h3>
            <nav class="space-y-1 mb-4">
                <a href="{{ route('attendance-logs.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('attendance-logs.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="scan-face" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Data Absensi</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Data Absensi
                    </span>
                </a>
                <a href="{{ route('leave-approvals.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('leave-approvals.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="file-check" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Riwayat Izin</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Riwayat Izin
                    </span>
                </a>
                <a href="{{ route('working-shifts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('working-shifts.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="clock" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Shift Kerja</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Shift Kerja
                    </span>
                </a>
                <a href="{{ route('employee-working-shifts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('employee-working-shifts.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="calendar" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Penjadwalan Shift</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Penjadwalan Shift
                    </span>
                </a>
                <a href="{{ route('holidays.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('holidays.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="plane" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Hari Libur</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Hari Libur
                    </span>
                </a>
                <a href="{{ route('bonus-schemas.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('bonus-schemas.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="coins" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Skema Bonus</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Skema Bonus
                    </span>
                </a>
                <a href="{{ route('attendance-percentage-reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('attendance-percentage-reports.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="percent" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Persentase Kehadiran</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Persentase Kehadiran
                    </span>
                </a>
            </nav>

            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2 mt-4">PENGGAJIAN</h3>
            <nav class="space-y-1 mb-4">
                <a href="{{ route('cutoff-settings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('cutoff-settings.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="scissors" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Pengaturan Cut-off</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Pengaturan Cut-off
                    </span>
                </a>
                <a href="{{ route('bonus-reports.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('bonus-reports.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-950 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="calculator" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Rekap Bonus</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Rekap Bonus
                    </span>
                </a>
                <a href="{{ route('payslips.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('payslips.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="file-text" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Slip Gaji</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Slip Gaji
                    </span>
                </a>
            </nav>

            <h3 class="school-info px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2 mt-4">MANAJEMEN SISTEM</h3>
            <nav class="space-y-1 mb-4">
                @if(auth()->user()->hasRole('super_admin'))
                <a href="{{ route('school-units.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('school-units.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="network" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Integrasi API Unit</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Integrasi API Unit
                    </span>
                </a>
                @endif
                
                <a href="{{ route('settings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('settings.index') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="settings" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Setting Aplikasi</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Setting Aplikasi
                    </span>
                </a>
                @if(auth()->user()->hasRole('super_admin'))
                <a href="{{ route('zkteco-devices.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('zkteco-devices.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="fingerprint" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Mesin Absensi</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Mesin Absensi
                    </span>
                </a>
                <a href="{{ route('raw-attendance-logs.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('raw-attendance-logs.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="file-json-2" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Log Mentah Mesin</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Log Mentah Mesin
                    </span>
                </a>
                <a href="{{ route('settings.adms') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('settings.adms') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="radio" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Sinkronisasi ADMS</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Sinkronisasi ADMS
                    </span>
                </a>
                <a href="{{ route('users.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-lg
                    {{ Request::routeIs('users.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-50 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-900/50' }}
                    text-xs relative group">
                    <i data-lucide="user-cog" class="menu-icon w-4 h-4"></i>
                    <span class="menu-text">Manajemen User</span>
                    <span class="sidebar-tooltip absolute left-full ml-3 px-2 py-1 bg-slate-900 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-50 dark:text-slate-100 text-xs font-semibold rounded-md shadow-md opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all origin-left duration-100 pointer-events-none whitespace-nowrap z-50">
                        Manajemen User
                    </span>
                </a>
                @endif
            </nav>
        </div>
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

            <!-- Account Info -->
            <a href="{{ route('profile.edit') }}"
                class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900/50 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                <i data-lucide="badge-check" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                <span>Profil</span>
            </a>

            <div class="border-t border-slate-100 dark:border-slate-900 my-1"></div>

            <!-- Log Out -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4 text-red-500 dark:text-red-400"></i>
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
