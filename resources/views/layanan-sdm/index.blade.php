<x-admin-layout>
    <div class="p-6 space-y-6" x-data="{
        activeTab: 'all',
        showDrawer: false,
        selectedTicket: null,
        replyMessage: '',
        tickets: [
            {
                id: 'SDM-001',
                sender: 'Anonim',
                is_anonymous: true,
                unit: 'SD SANS',
                category: 'Kritik',
                subject: 'Kebijakan pembagian jadwal piket pagi guru',
                message: 'Mohon ditinjau kembali pembagian jadwal piket guru di pagi hari karena beberapa guru yang bertugas piket juga memiliki jadwal mengajar jam pertama di kelas yang cukup jauh dari pintu gerbang utama.',
                date: '16 Agu 2026',
                status: 'Baru',
                chat: [
                    { sender: 'Pegawai (Anonim)', time: '16 Agu 2026, 08:30', text: 'Mohon ditinjau kembali pembagian jadwal piket guru di pagi hari karena beberapa guru yang bertugas piket juga memiliki jadwal mengajar jam pertama di kelas yang cukup jauh dari pintu gerbang utama.' }
                ]
            },
            {
                id: 'SDM-002',
                sender: 'Ahmad Faisal',
                is_anonymous: false,
                nip: '1988040212',
                position: 'Guru Matematika',
                unit: 'SMP SANS',
                category: 'Permintaan',
                subject: 'Permohonan Surat Keterangan Kerja (SKK) untuk KPR',
                message: 'Selamat pagi bagian HRD, saya mengajukan permohonan penerbitan Surat Keterangan Kerja (SKK) resmi untuk keperluan pengajuan KPR Bank. Berkas akan saya ambil sendiri jika sudah selesai ditandatangani kepala yayasan.',
                date: '15 Agu 2026',
                status: 'Selesai',
                chat: [
                    { sender: 'Ahmad Faisal', time: '15 Agu 2026, 09:15', text: 'Selamat pagi bagian HRD, saya mengajukan permohonan penerbitan Surat Keterangan Kerja (SKK) resmi untuk keperluan pengajuan KPR Bank. Berkas akan saya ambil sendiri jika sudah selesai ditandatangani kepala yayasan.' },
                    { sender: 'HRD Pusat', time: '15 Agu 2026, 14:00', text: 'Selamat siang Pak Ahmad Faisal, SKK Anda telah diterbitkan dan ditandatangani. Berkas fisik dapat diambil di meja administrasi HRD Pusat lantai 2. Terima kasih.' }
                ]
            },
            {
                id: 'SDM-003',
                sender: 'Anonim',
                is_anonymous: true,
                unit: 'PAUD SANS',
                category: 'Aduan',
                subject: 'Fasilitas AC ruang kelas terapis bocor',
                message: 'Kami ingin melaporkan bahwa AC di ruang kelas terapis anak berkebutuhan khusus PAUD bocor air dan tidak dingin sejak 2 hari lalu. Kasihan anak-anak menjadi tidak fokus saat sesi terapi.',
                date: '14 Agu 2026',
                status: 'Diproses',
                chat: [
                    { sender: 'Pegawai (Anonim)', time: '14 Agu 2026, 10:20', text: 'Kami ingin melaporkan bahwa AC di ruang kelas terapis anak berkebutuhan khusus PAUD bocor air dan tidak dingin sejak 2 hari lalu. Kasihan anak-anak menjadi tidak fokus saat sesi terapi.' },
                    { sender: 'HRD Pusat', time: '14 Agu 2026, 11:30', text: 'Aduan telah kami terima. Teknisi pemeliharaan gedung telah dijadwalkan untuk melakukan perbaikan pada hari Senin pagi besok pukul 09:00. Mohon kerja samanya.' }
                ]
            },
            {
                id: 'SDM-004',
                sender: 'Siti Rahma',
                is_anonymous: false,
                nip: '1992051233',
                position: 'Wali Kelas 2',
                unit: 'SD SANS',
                category: 'Saran',
                subject: 'Penyediaan dispenser air minum di koridor kelas',
                message: 'Alangkah baiknya jika di setiap koridor kelas disediakan dispenser air minum isi ulang untuk guru dan siswa. Hal ini bisa mengurangi penggunaan botol plastik sekali pakai dan mempermudah guru yang mengajar berturut-turut.',
                date: '12 Agu 2026',
                status: 'Selesai',
                chat: [
                    { sender: 'Siti Rahma', time: '12 Agu 2026, 11:05', text: 'Alangkah baiknya jika di setiap koridor kelas disediakan dispenser air minum isi ulang untuk guru dan siswa. Hal ini bisa mengurangi penggunaan botol plastik sekali pakai dan mempermudah guru yang mengajar berturut-turut.' },
                    { sender: 'HRD Pusat', time: '13 Agu 2026, 09:00', text: 'Saran yang sangat baik Ibu Siti Rahma. Usulan ini telah kami catat dan akan diajukan dalam rapat anggaran pengadaan sarana prasarana sekolah triwulan depan.' }
                ]
            },
            {
                id: 'SDM-005',
                sender: 'Budi Santoso',
                is_anonymous: false,
                nip: '1985011902',
                position: 'Staf IT Support',
                unit: 'SMP SANS',
                category: 'Pertanyaan',
                subject: 'Kejelasan jadwal pencairan tunjangan sertifikasi',
                message: 'Saya ingin menanyakan kapan perkiraan jadwal pencairan dana tunjangan profesi/sertifikasi guru triwulan kedua untuk unit SMP? Karena beberapa rekan menanyakan hal ini.',
                date: '16 Agu 2026',
                status: 'Baru',
                chat: [
                    { sender: 'Budi Santoso', time: '16 Agu 2026, 16:45', text: 'Saya ingin menanyakan kapan perkiraan jadwal pencairan dana tunjangan profesi/sertifikasi guru triwulan kedua untuk unit SMP? Karena beberapa rekan menanyakan hal ini.' }
                ]
            }
        ],
        openTicket(ticket) {
            this.selectedTicket = JSON.parse(JSON.stringify(ticket));
            this.showDrawer = true;
            this.replyMessage = '';
        },
        submitReply() {
            if (this.replyMessage.trim() === '') return;
            
            // Add message to local mockup model
            const now = new Date();
            const dateStr = now.getDate() + ' ' + now.toLocaleString('id-ID', { month: 'short' }) + ' ' + now.getFullYear() + ', ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
            
            // Push to local ticket chat
            this.selectedTicket.chat.push({
                sender: 'HRD Pusat',
                time: dateStr,
                text: this.replyMessage
            });
            
            // If ticket was 'Baru', update status to 'Diproses'
            if (this.selectedTicket.status === 'Baru') {
                this.selectedTicket.status = 'Diproses';
            }
            
            // Update master ticket list for this mockup session
            const masterTicket = this.tickets.find(t => t.id === this.selectedTicket.id);
            if (masterTicket) {
                masterTicket.chat.push({
                    sender: 'HRD Pusat',
                    time: dateStr,
                    text: this.replyMessage
                });
                masterTicket.status = this.selectedTicket.status;
            }
            
            this.replyMessage = '';
            
            // Notify simulated success
            alert('Simulasi balasan HRD berhasil dikirim! Status tiket disesuaikan.');
        },
        resolveTicket() {
            this.selectedTicket.status = 'Selesai';
            const masterTicket = this.tickets.find(t => t.id === this.selectedTicket.id);
            if (masterTicket) {
                masterTicket.status = 'Selesai';
            }
            alert('Simulasi tiket telah ditandai sebagai Selesai.');
        }
    }">

        <!-- DEVELOPER NOTE BANNER -->
        <div class="bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-850 rounded-xl p-4 flex items-start gap-3 text-left shadow-2xs">
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-5 h-5 animate-pulse"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-xs font-bold text-indigo-900 dark:text-indigo-300">Modul Layanan Kepegawaian SDM (Tahap Pengembangan)</h4>
                <p class="text-[11px] leading-relaxed text-indigo-700/90 dark:text-indigo-400/90">
                    Halaman ini merupakan tampilan purwarupa (*mockup / interactive prototype*). Layanan pengaduan, kritik, saran, serta pertanyaan ditarik secara terpusat dari masing-masing portal unit sekolah. Untuk saat ini data yang ditampilkan menggunakan data statis simulasi.
                </p>
            </div>
        </div>

        <!-- HEADER -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 w-full text-left">
            <div class="flex flex-col gap-0.5">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-200 font-nasalization">Layanan Kepegawaian SDM</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Himpunan aduan, kritik, saran, dan pertanyaan pegawai yang diajukan melalui portal unit sekolah.</p>
            </div>
        </header>

        <!-- STATS CARDS -->
        <section class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <!-- Total Masuk -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Total Aduan & Layanan</span>
                    <span class="text-2xl font-bold text-slate-950 dark:text-slate-50 mt-1 block" x-text="tickets.length">0</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0 border border-slate-200/40 dark:border-slate-800/40">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Tiket Baru -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Aduan Baru</span>
                    <span class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1 block" x-text="tickets.filter(t => t.status === 'Baru').length">0</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-455 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Sedang Diproses -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Sedang Diproses</span>
                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1 block" x-text="tickets.filter(t => t.status === 'Diproses').length">0</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-455 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- Selesai -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm text-left flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Terselesaikan</span>
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-455 mt-1 block" x-text="tickets.filter(t => t.status === 'Selesai').length">0</span>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-455 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </section>

        <!-- CONTAINER MAIN -->
        <div class="space-y-4">
            <!-- TAB FILTER -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 border-b border-slate-200 dark:border-slate-800/80 w-full">
                <button type="button" @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'border-indigo-600 dark:border-indigo-500 text-indigo-650 dark:text-indigo-400 font-bold border-b-2' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-350'"
                        class="h-8 px-4 inline-flex items-center justify-center text-xs font-semibold transition-all cursor-pointer">
                    Semua Layanan
                </button>
                <button type="button" @click="activeTab = 'Kritik'"
                        :class="activeTab === 'Kritik' ? 'border-indigo-600 dark:border-indigo-500 text-indigo-650 dark:text-indigo-400 font-bold border-b-2' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-350'"
                        class="h-8 px-4 inline-flex items-center justify-center text-xs font-semibold transition-all cursor-pointer">
                    Kritik
                </button>
                <button type="button" @click="activeTab = 'Saran'"
                        :class="activeTab === 'Saran' ? 'border-indigo-600 dark:border-indigo-500 text-indigo-650 dark:text-indigo-400 font-bold border-b-2' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-350'"
                        class="h-8 px-4 inline-flex items-center justify-center text-xs font-semibold transition-all cursor-pointer">
                    Saran
                </button>
                <button type="button" @click="activeTab = 'Aduan'"
                        :class="activeTab === 'Aduan' ? 'border-indigo-600 dark:border-indigo-500 text-indigo-650 dark:text-indigo-400 font-bold border-b-2' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-350'"
                        class="h-8 px-4 inline-flex items-center justify-center text-xs font-semibold transition-all cursor-pointer">
                    Aduan/Komplain
                </button>
                <button type="button" @click="activeTab = 'Permintaan'"
                        :class="activeTab === 'Permintaan' ? 'border-indigo-600 dark:border-indigo-500 text-indigo-650 dark:text-indigo-400 font-bold border-b-2' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-350'"
                        class="h-8 px-4 inline-flex items-center justify-center text-xs font-semibold transition-all cursor-pointer">
                    Permintaan
                </button>
                <button type="button" @click="activeTab = 'Pertanyaan'"
                        :class="activeTab === 'Pertanyaan' ? 'border-indigo-600 dark:border-indigo-500 text-indigo-650 dark:text-indigo-400 font-bold border-b-2' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-350'"
                        class="h-8 px-4 inline-flex items-center justify-center text-xs font-semibold transition-all cursor-pointer">
                    Pertanyaan
                </button>
            </div>

            <!-- TABLE BOARD -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="px-6 py-4">Kode / Pengaju</th>
                                <th class="px-6 py-4">Unit Sekolah</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4">Subjek / Masalah</th>
                                <th class="px-6 py-4 text-center">Tanggal Masuk</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <template x-for="ticket in tickets" :key="ticket.id">
                                <tr x-show="activeTab === 'all' || ticket.category === activeTab"
                                    class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors group">
                                    
                                    <!-- Kode / Pengaju -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-extrabold text-[10px] text-slate-400 dark:text-slate-500" x-text="ticket.id"></span>
                                            <span class="font-bold text-slate-850 dark:text-slate-200" x-text="ticket.sender"></span>
                                        </div>
                                    </td>
                                    
                                    <!-- Unit Sekolah -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-slate-700 dark:text-slate-350" x-text="ticket.unit"></span>
                                    </td>
                                    
                                    <!-- Kategori -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30': ticket.category === 'Kritik',
                                            'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-100 dark:border-blue-900/30': ticket.category === 'Saran',
                                            'bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-900/30': ticket.category === 'Aduan',
                                            'bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border-purple-100 dark:border-purple-900/30': ticket.category === 'Permintaan',
                                            'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/30': ticket.category === 'Pertanyaan'
                                        }" class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase border" x-text="ticket.category"></span>
                                    </td>
                                    
                                    <!-- Subjek / Masalah -->
                                    <td class="px-6 py-4 max-w-xs truncate">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-semibold text-slate-900 dark:text-slate-200" x-text="ticket.subject"></span>
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="ticket.message"></span>
                                        </div>
                                    </td>
                                    
                                    <!-- Tanggal Masuk -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-slate-500 dark:text-slate-400 font-medium" x-text="ticket.date"></td>
                                    
                                    <!-- Status -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span :class="{
                                            'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30': ticket.status === 'Baru',
                                            'bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-900/30': ticket.status === 'Diproses',
                                            'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30': ticket.status === 'Selesai'
                                        }" class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase border" x-text="ticket.status"></span>
                                    </td>
                                    
                                    <!-- Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button type="button" @click="openTicket(ticket)"
                                            class="h-7 px-3 inline-flex items-center gap-1 bg-slate-100 hover:bg-indigo-600 hover:text-white dark:bg-slate-800 dark:hover:bg-indigo-700 text-slate-700 dark:text-slate-300 rounded-md font-bold text-[10px] transition-colors cursor-pointer">
                                            <span>Buka Tiket</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DETAIL DRAWER (Alpine.js overlay + slide-out panel) -->
        <div x-show="showDrawer" x-cloak
            class="fixed inset-0 z-[100] flex justify-end"
            x-transition:enter="transition ease-out duration-300"
            x-transition:leave="transition ease-in duration-200">
            
            <!-- Backdrop Overlay -->
            <div x-show="showDrawer" 
                class="absolute inset-0 bg-slate-950/40 backdrop-blur-xs"
                @click="showDrawer = false"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"></div>

            <!-- Slide-out Drawer Panel -->
            <div x-show="showDrawer"
                class="relative w-full max-w-lg h-full bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-xl flex flex-col justify-between text-left p-6 space-y-6"
                x-transition:enter="transition-transform ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full">
                
                <!-- Drawer Header -->
                <div class="flex justify-between items-start border-b border-slate-100 dark:border-slate-800 pb-4 shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-xs text-slate-400" x-text="selectedTicket ? selectedTicket.id : ''"></span>
                            <span :class="{
                                'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-455 border-rose-100/30 dark:border-rose-900/30': selectedTicket && selectedTicket.status === 'Baru',
                                'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-100/30 dark:border-amber-900/30': selectedTicket && selectedTicket.status === 'Diproses',
                                'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-100/30 dark:border-emerald-900/30': selectedTicket && selectedTicket.status === 'Selesai'
                            }" class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase border" x-text="selectedTicket ? selectedTicket.status : ''"></span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-50 mt-1" x-text="selectedTicket ? selectedTicket.subject : ''">Detail Layanan SDM</h3>
                    </div>
                    <button type="button" @click="showDrawer = false" class="p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Drawer Content / Ticket details & Chat timeline -->
                <div class="flex-1 overflow-y-auto space-y-6 pr-1 no-scrollbar">
                    
                    <!-- Sender Profile Card -->
                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-150 dark:border-slate-800 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center shrink-0 font-bold text-xs uppercase"
                                x-text="selectedTicket ? selectedTicket.sender.substr(0,2) : ''"></div>
                            <div>
                                <span class="block font-bold text-slate-850 dark:text-slate-100 text-xs" x-text="selectedTicket ? selectedTicket.sender : ''"></span>
                                <span class="block text-[10px] text-slate-400 dark:text-slate-500" x-text="selectedTicket && selectedTicket.is_anonymous ? 'Pengirim Anonim' : (selectedTicket ? selectedTicket.position : '')"></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 border-t border-slate-200/50 dark:border-slate-800/80 pt-3 text-[11px]">
                            <div>
                                <span class="text-slate-400 block">Unit Sekolah</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedTicket ? selectedTicket.unit : ''"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Kategori Layanan</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300" x-text="selectedTicket ? selectedTicket.category : ''"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Chat / Message History Timeline -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                            Histori Percakapan
                        </h4>
                        
                        <div class="space-y-3">
                            <template x-for="(msg, index) in (selectedTicket ? selectedTicket.chat : [])" :key="index">
                                <div :class="msg.sender === 'HRD Pusat' ? 'items-end' : 'items-start'" class="flex flex-col gap-1.5 w-full">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[9.5px] font-bold text-slate-450" x-text="msg.sender"></span>
                                        <span class="text-[9px] text-slate-400" x-text="msg.time"></span>
                                    </div>
                                    <div :class="msg.sender === 'HRD Pusat' ? 'bg-indigo-600 text-white rounded-l-xl rounded-tr-xl' : 'bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-200 rounded-r-xl rounded-tl-xl border border-slate-150 dark:border-slate-800'"
                                        class="px-4 py-2.5 text-xs max-w-[85%] leading-relaxed" x-text="msg.text"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                <!-- Drawer Footer (Simulated Reply Form) -->
                <div class="border-t border-slate-100 dark:border-slate-800 pt-4 shrink-0 space-y-3">
                    <template x-if="selectedTicket && selectedTicket.status !== 'Selesai'">
                        <div class="space-y-3">
                            <textarea x-model="replyMessage" placeholder="Tulis tanggapan / solusi dari HRD..." rows="3"
                                class="w-full p-3 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-slate-900 dark:text-slate-100"></textarea>
                            
                            <div class="flex justify-between items-center">
                                <button type="button" @click="resolveTicket()" class="h-8 px-4 bg-emerald-50 dark:bg-emerald-950/20 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 font-bold text-xs rounded-lg border-0 transition-colors cursor-pointer">
                                    Tandai Selesai
                                </button>
                                
                                <button type="button" @click="submitReply()" class="h-8 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm border-0 transition-all hover:translate-y-[-1px] cursor-pointer flex items-center gap-1.5">
                                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                    Kirim Tanggapan
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="selectedTicket && selectedTicket.status === 'Selesai'">
                        <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-900/30 rounded-xl text-center">
                            <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-400">Aduan/Layanan ini telah berstatus Selesai dan ditutup.</span>
                        </div>
                    </template>
                </div>

            </div>

        </div>

    </div>
</x-admin-layout>
