<x-admin-layout>
    <!-- Actions Bar (Hidden on print) -->
    <div class="max-w-4xl mx-auto p-4 flex items-center justify-between gap-4 border-b dark:border-slate-800 border-slate-200 print:hidden text-left">
        <div class="flex items-center gap-3">
            <a href="{{ route('performance-reports.index') }}" class="px-3 py-2 rounded-xl dark:bg-slate-900 bg-white border dark:border-slate-800 border-slate-200 hover:bg-slate-500/10 text-slate-650 dark:text-slate-350 transition-all flex items-center gap-1.5 text-xs font-semibold cursor-pointer shadow-sm">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Kembali
            </a>
            <div>
                <h3 class="text-sm font-bold dark:text-white text-slate-900">Rapor Kinerja: {{ $employee->name }}</h3>
                <p class="text-[10px] dark:text-slate-400 text-slate-500 mt-0.5">Tahun Ajaran {{ $report->academic_year }} - Semester {{ $report->semester }}</p>
            </div>
        </div>
        
        <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-xl shadow-md cursor-pointer flex items-center gap-2 transition-all">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Cetak Rapor (Save PDF)
        </button>
    </div>

    <!-- Printable Area Wrapper -->
    <div class="py-8 px-4 bg-slate-50 dark:bg-slate-950/20 min-h-screen flex justify-center print:bg-white print:p-0 print:min-h-0">
        
        <!-- A4 Page Container -->
        <div class="w-full max-w-[210mm] min-h-[297mm] bg-white text-slate-800 p-[15mm] shadow-lg border border-slate-200 rounded-2xl flex flex-col justify-between print:border-none print:shadow-none print:rounded-none print:p-0 print:max-w-none print:min-h-0 print:bg-white">
            
            <div class="w-full">
                <!-- Kop Surat Yayasan -->
                <div class="w-full flex items-center justify-between border-b-[3px] border-emerald-600 pb-3 mb-6">
                    <div class="flex items-center gap-4">
                        @if(!empty($reportLogoPath))
                            <img src="{{ asset('storage/' . $reportLogoPath) }}" class="w-16 h-16 object-contain">
                        @else
                            <!-- Fallback Logo Vektor SVG -->
                            <div class="w-16 h-16 flex items-center justify-center bg-emerald-50 rounded-xl border border-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-9 h-9 text-emerald-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.9c4.956-2.14 8.798-5.748 8.798-11.112v-2.24A48.74 48.74 0 0 0 12 3.26a48.74 48.74 0 0 0-8.232 4.288Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v6m-3-3h6" />
                                </svg>
                            </div>
                        @endif
                        <div class="text-left">
                            <h2 class="text-base font-extrabold uppercase text-emerald-700 tracking-wide m-0 p-0 leading-tight">
                                {{ $reportYayasanName }}
                            </h2>
                            <div class="text-[10px] text-slate-500 font-medium leading-relaxed whitespace-pre-line mt-1">
                                {{ $reportYayasanAddress }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-lg border border-emerald-100 uppercase tracking-wider">
                            Unit {{ $report->schoolUnit->name ?? 'Sekolah' }}
                        </span>
                    </div>
                </div>

                <!-- Judul Dokumen -->
                <div class="text-center mb-6">
                    <h3 class="text-sm font-extrabold uppercase text-slate-900 tracking-widest leading-none">Rapor Penilaian Kinerja Guru (PKG)</h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wide mt-1.5">
                        Tahun Ajaran {{ $report->academic_year }} &bull; Semester {{ $report->semester == 1 ? 'Ganjil' : 'Genap' }}
                    </p>
                </div>

                <!-- Identitas Guru -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 mb-6 text-left">
                    <table class="w-full text-[11px] leading-relaxed">
                        <tr>
                            <td class="w-[120px] text-slate-400 font-medium pb-2">Nama Lengkap</td>
                            <td class="w-4 text-slate-400 pb-2">:</td>
                            <td class="font-bold text-slate-900 pb-2">{{ $employee->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-slate-400 font-medium pb-2">Email Pegawai</td>
                            <td class="text-slate-400 pb-2">:</td>
                            <td class="font-medium text-slate-700 pb-2">{{ $employee->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-slate-400 font-medium pb-2">Unit Sekolah</td>
                            <td class="text-slate-400 pb-2">:</td>
                            <td class="font-semibold text-slate-800 pb-2">Unit {{ $report->schoolUnit->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-slate-400 font-medium">Jabatan / Tugas</td>
                            <td class="text-slate-400">:</td>
                            <td class="font-semibold text-slate-800 capitalize">{{ strtolower($pos) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Rubrik Nilai Kompetensi -->
                <div class="mb-6">
                    <h4 class="text-[10px] font-extrabold uppercase text-slate-450 tracking-wider mb-2.5 text-left">Rincian Hasil Penilaian Aspek (Skala 0-100)</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[9px]">
                                    <th class="py-2.5 px-4 w-12 text-center">No</th>
                                    <th class="py-2.5 px-2">Dimensi Kompetensi / Kinerja</th>
                                    <th class="py-2.5 px-4 w-28 text-center">Nilai Aspek</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150">
                                <tr>
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">1</td>
                                    <td class="py-3 px-2 font-semibold text-slate-800">Kompetensi Pedagogik</td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm tabular-nums">{{ round($report->score_pedagogik) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">2</td>
                                    <td class="py-3 px-2 font-semibold text-slate-800">Kompetensi Kepribadian</td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm tabular-nums">{{ round($report->score_kepribadian) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">3</td>
                                    <td class="py-3 px-2 font-semibold text-slate-800">Kompetensi Sosial</td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm tabular-nums">{{ round($report->score_sosial) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">4</td>
                                    <td class="py-3 px-2 font-semibold text-slate-800">Kompetensi Profesional</td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm tabular-nums">{{ round($report->score_profesional) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">5</td>
                                    <td class="py-3 px-2 font-semibold text-slate-800">Kedisiplinan & Loyalitas (Absensi Kehadiran)</td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm tabular-nums">{{ round($report->score_discipline) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bagian Nilai Akhir & Predikat -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-left">
                    <!-- Nilai Akhir Box -->
                    <div class="border-2 border-emerald-600 rounded-xl p-4 bg-emerald-50/20 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-extrabold uppercase text-emerald-800 tracking-wider block">Nilai Akhir Rapor</span>
                            <span class="text-[10px] text-slate-500 font-medium block mt-0.5">Rata-rata dari seluruh aspek</span>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-black text-emerald-700 tabular-nums leading-none">{{ round($report->final_score) }}</span>
                            <span class="text-[10px] text-emerald-600/80 font-bold block mt-1">Skala 0-100</span>
                        </div>
                    </div>

                    <!-- Predikat Box -->
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="text-[9px] font-extrabold uppercase text-slate-500 tracking-wider block">Predikat Kinerja</span>
                            <span class="text-[10px] text-slate-500 font-medium block mt-0.5">Berdasarkan Standar Yayasan</span>
                        </div>
                        <div class="text-right flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-50 border-2 border-indigo-500 text-lg font-black text-indigo-700">
                                {{ $predicateLetter }}
                            </span>
                            <span class="text-xs font-bold text-slate-700 block text-left leading-tight max-w-[80px]">
                                {{ $report->predicate ?? 'Kurang Sekali' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Legenda Rentang Nilai Yayasan -->
                <div class="border border-slate-150 rounded-xl p-3 bg-slate-50/30 mb-6 text-left">
                    <span class="text-[9px] font-extrabold uppercase text-slate-450 tracking-wider block mb-2">Standar Predikat Penilaian Yayasan:</span>
                    <div class="grid grid-cols-3 gap-2 text-[9px] text-slate-500 font-medium">
                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 91 - 100 : A (Amat Baik)</div>
                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> 81 - 90 : B+ (Baik Sekali)</div>
                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> 71 - 80 : B (Baik)</div>
                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 61 - 70 : C (Cukup)</div>
                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> 51 - 60 : D (Kurang)</div>
                        <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> 0 - 50 : E (Kurang Sekali)</div>
                    </div>
                </div>

                <!-- Catatan Rekomendasi/Catatan Umum -->
                @if(!empty($report->recommendations))
                    <div class="border border-dashed border-slate-300 rounded-xl p-4 mb-6 text-left">
                        <span class="text-[9px] font-extrabold uppercase text-slate-450 tracking-wider block mb-1">Catatan & Rekomendasi Pimpinan:</span>
                        <p class="text-xs text-slate-700 italic leading-relaxed whitespace-pre-line">
                            "{{ $report->recommendations }}"
                        </p>
                    </div>
                @endif
            </div>

            <!-- Tanda Tangan & Stempel Basah Yayasan -->
            <div class="w-full flex justify-between items-end mt-8 text-left text-[11px] leading-relaxed">
                <!-- Catatan Kaki Tanggal Cetak -->
                <div class="text-slate-400 italic text-[9px] self-end">
                    Rapor disinkronisasi pada: {{ $report->created_at->translatedFormat('d F Y H:i') }}
                </div>

                <!-- Area Tanda Tangan & Stempel -->
                <div class="w-[220px] text-center relative flex flex-col items-center">
                    <p class="text-slate-800 font-medium">Kota Malang, {{ $report->created_at->translatedFormat('d F Y') }}</p>
                    <p class="text-slate-800 font-extrabold uppercase mt-0.5">Direktur Pendidikan,</p>
                    
                    <!-- Space for Stamp and Signature -->
                    <div class="w-full h-24 relative flex items-center justify-center my-1 select-none">
                        <!-- Tanda Tangan / Stempel -->
                        @if(!empty($reportStampPath))
                            <img src="{{ asset('storage/' . $reportStampPath) }}" class="h-20 object-contain z-10 relative">
                        @else
                            <!-- Fallback Vektor SVG Stempel Transparan Hijau khas Yayasan -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-85 select-none pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" class="w-24 h-24 text-emerald-600/70 fill-none stroke-current">
                                    <!-- Double outer circles -->
                                    <circle cx="100" cy="100" r="85" stroke-width="2.5" stroke-dasharray="6 2" />
                                    <circle cx="100" cy="100" r="78" stroke-width="1" />
                                    
                                    <!-- Circular Text paths -->
                                    <path id="stamp-text-path-top" d="M30 100 A70 70 0 0 1 170 100" fill="none" stroke="none" />
                                    <path id="stamp-text-path-bottom" d="M170 100 A70 70 0 0 1 30 100" fill="none" stroke="none" />
                                    
                                    <text font-size="9" font-weight="900" fill="currentColor" tracking="2">
                                        <textPath href="#stamp-text-path-top" startOffset="50%" text-anchor="middle">
                                            YAYASAN PENDIDIKAN ANAK SALEH
                                        </textPath>
                                    </text>
                                    <text font-size="9" font-weight="900" fill="currentColor" tracking="2">
                                        <textPath href="#stamp-text-path-bottom" startOffset="50%" text-anchor="middle">
                                            DIREKTUR PENDIDIKAN
                                        </textPath>
                                    </text>
                                    
                                    <!-- Star separators -->
                                    <text x="26" y="103" font-size="10" fill="currentColor" font-weight="bold">&#9733;</text>
                                    <text x="166" y="103" font-size="10" fill="currentColor" font-weight="bold">&#9733;</text>
                                    
                                    <!-- Inner Shield Graphic -->
                                    <path d="M70 70 L130 70 L130 105 C130 135 100 150 100 150 C100 150 70 135 70 105 Z" stroke-width="1.5" fill="none" />
                                    
                                    <!-- Decorative Check / Emblem -->
                                    <path d="M85 105 L95 115 L115 95" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            
                            <!-- Fake Signature Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center select-none pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 60" class="w-16 h-12 text-indigo-700/80 stroke-current fill-none stroke-[2.5] stroke-linecap-round stroke-linejoin-round">
                                    <path d="M15 45 C30 15, 45 10, 50 25 C55 40, 60 50, 70 30 C80 10, 85 20, 90 25" />
                                    <path d="M35 30 L65 30" stroke-width="1.5" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Nama Direktur -->
                    <p class="font-bold dark:text-slate-200 text-slate-850 underline decoration-emerald-600 decoration-2 underline-offset-4">
                        {{ $reportDirectorName }}
                    </p>
                    <p class="text-[10px] text-slate-500 font-bold tracking-wider mt-0.5 uppercase">Direktur Utama Yayasan</p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Print Styles Override -->
    <style>
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            .print\:hidden {
                display: none !important;
            }
            /* Clean up background colors for print layouts */
            .print\:bg-white {
                background-color: white !important;
                background-image: none !important;
            }
            .print\:border-none {
                border: none !important;
            }
            .print\:shadow-none {
                box-shadow: none !important;
            }
            .print\:p-0 {
                padding: 0 !important;
            }
            /* A4 dimensions override */
            @page {
                size: A4 portrait;
                margin: 15mm !important;
            }
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
            }
        }
    </style>
</x-admin-layout>
