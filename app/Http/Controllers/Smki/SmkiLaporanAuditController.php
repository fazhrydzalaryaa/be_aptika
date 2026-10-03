<?php

namespace App\Http\Controllers\Smki;

use App\Http\Controllers\Controller;
use App\Models\SmkiLaporanAudit;
use App\Models\SmkiDetailTemuan;
use App\Models\SmkiUnitKerja;
use App\Models\SmkiKategoriTemuan;
use App\Services\SmkiLaporanAuditDocxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmkiLaporanAuditController extends Controller
{
    protected SmkiLaporanAuditDocxService $docxService;

    public function __construct(SmkiLaporanAuditDocxService $docxService)
    {
        $this->docxService = $docxService;
    }

    /**
     * Menampilkan daftar laporan audit dengan filter, paginasi, dan statistik KPI.
     */
    public function index(Request $request)
    {
        $query = SmkiLaporanAudit::with(['unitKerja', 'kategoriTemuan', 'detailTemuans'])
            ->orderBy('id_laporan_audit', 'desc');

        // Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nomor_laporan', 'like', "%{$search}%")
                  ->orWhere('nama_unit_kerja', 'like', "%{$search}%")
                  ->orWhere('auditor', 'like', "%{$search}%")
                  ->orWhere('auditee', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%")
                  ->orWhere('klausul_annex', 'like', "%{$search}%");
            });
        }

        // Filter Unit Kerja
        if ($request->filled('unit_kerja')) {
            $uk = trim($request->input('unit_kerja'));
            if ($uk !== 'Semua Unit' && $uk !== '') {
                $query->where(function ($q) use ($uk) {
                    $q->where('nama_unit_kerja', 'like', "%{$uk}%")
                      ->orWhere('id_unit_kerja', $uk);
                });
            }
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = trim($request->input('status'));
            if ($status !== 'Semua' && $status !== '') {
                $query->where('status', $status);
            }
        }

        // Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->where('tanggal_audit', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('tanggal_audit', '<=', $request->input('date_to'));
        }

        // Ringkasan Statistik Global
        $baseQuery = SmkiLaporanAudit::query();
        $totalLaporan  = (clone $baseQuery)->count();
        $sedangDitinjau= (clone $baseQuery)->where('status', 'Sedang Ditinjau')->count();
        $totalDraft    = (clone $baseQuery)->where('status', 'Draft')->count();
        $totalSelesai  = (clone $baseQuery)->where('status', 'Selesai')->count();

        // Paginasi
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage === -1) {
            $items = $query->get();
            $paginated = null;
        } else {
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
        }

        return response()->json([
            'success' => true,
            'data'    => $items,
            'stats'   => [
                'total_laporan'   => $totalLaporan,
                'sedang_ditinjau' => $sedangDitinjau,
                'total_draft'     => $totalDraft,
                'total_selesai'   => $totalSelesai,
            ],
            'meta' => $paginated ? [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ] : [
                'current_page' => 1,
                'last_page'    => 1,
                'per_page'     => count($items),
                'total'        => count($items),
            ],
        ]);
    }

    /**
     * Master lookup untuk dropdown data (Unit Kerja, Kategori, Klausul/Annex, Auto Nomor).
     */
    public function lookup(Request $request)
    {
        $unitKerjas = SmkiUnitKerja::orderBy('nama_unit_kerja', 'asc')->get();
        $kategoriTemuans = SmkiKategoriTemuan::orderBy('nama_kategori', 'asc')->get();

        // Nomor Laporan Rekomendasi Selanjutnya
        $latest = SmkiLaporanAudit::orderBy('id_laporan_audit', 'desc')->first();
        $nextId = $latest ? ($latest->id_laporan_audit + 1) : 1;
        $suggestedNomor = 'FR-006-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Rekomendasi standar klausul/annex ISO 27001
        $standardClauses = [
            'A.5.1 Policies for Information Security',
            'A.5.9 Inventory of Information and Other Associated Assets',
            'A.5.15 Access Control',
            'A.5.18 Access Rights',
            'A.6.8 Information Security Event Reporting',
            'A.7.4 Physical Security Monitoring',
            'A.8.10 Information Deletion',
            'A.8.12 Data Leakage Prevention',
            'A.8.20 Network Security',
            'A.8.24 Use of Cryptography',
            'A.8.28 Secure Coding',
            'A.12.1 Operational Procedures',
            'Multi-Clause (Lihat Rincian)',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'unit_kerjas'       => $unitKerjas,
                'kategori_temuans'  => $kategoriTemuans,
                'suggested_nomor'   => $suggestedNomor,
                'standard_clauses'  => $standardClauses,
                'statuses'          => ['Draft', 'Sedang Ditinjau', 'Selesai'],
            ],
        ]);
    }

    /**
     * Menampilkan detail satu rekaman laporan audit.
     */
    public function show($id)
    {
        $report = SmkiLaporanAudit::with(['unitKerja', 'kategoriTemuan', 'detailTemuans', 'user'])
            ->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Data Laporan Audit tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $report,
        ]);
    }

    /**
     * Menyimpan laporan audit baru beserta rincian temuannya.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_kerja'     => 'required|string',
            'auditor'        => 'required|string',
            'auditee'        => 'required|string',
            'tanggal_audit'  => 'nullable|date',
            'details'        => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request) {
            $nomorLaporan = $request->input('nomor_laporan');
            if (empty($nomorLaporan)) {
                $latest = SmkiLaporanAudit::orderBy('id_laporan_audit', 'desc')->first();
                $nextId = $latest ? ($latest->id_laporan_audit + 1) : 1;
                $nomorLaporan = 'FR-006-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }

            // Temukan atau cocokkan Unit Kerja
            $unitKerjaName = trim($request->input('unit_kerja'));
            $ukRecord = SmkiUnitKerja::where('nama_unit_kerja', $unitKerjaName)->first();
            $unitKerjaId = $ukRecord ? $ukRecord->id_unit_kerja : null;

            // Rincian temuan
            $details = $request->input('details', []);

            // Hitung temuan otomatis jika tidak ditentukan manual
            $countMajor = 0;
            $countMinor = 0;
            $countOfi   = 0;

            foreach ($details as $d) {
                $kat = strtolower(trim($d['kategori_temuan'] ?? ''));
                if (str_contains($kat, 'major')) {
                    $countMajor++;
                } elseif (str_contains($kat, 'minor')) {
                    $countMinor++;
                } elseif (str_contains($kat, 'ofi')) {
                    $countOfi++;
                }
            }

            $temuanMajor = $request->has('temuan_major') && $request->input('temuan_major') !== '' && $request->input('temuan_major') !== null
                ? (int) $request->input('temuan_major')
                : $countMajor;
            $temuanMinor = $request->has('temuan_minor') && $request->input('temuan_minor') !== '' && $request->input('temuan_minor') !== null
                ? (int) $request->input('temuan_minor')
                : $countMinor;
            $ofi = $request->has('ofi') && $request->input('ofi') !== '' && $request->input('ofi') !== null
                ? (int) $request->input('ofi')
                : $countOfi;

            $status = $request->input('status', 'Draft');

            $report = SmkiLaporanAudit::create([
                'nomor_laporan'   => $nomorLaporan,
                'temuan_major'    => $temuanMajor,
                'temuan_minor'    => $temuanMinor,
                'ofi'             => $ofi,
                'id_unit_kerja'   => $unitKerjaId,
                'nama_unit_kerja' => $unitKerjaName,
                'auditor'         => $request->input('auditor'),
                'auditee'         => $request->input('auditee'),
                'tanggal_audit'   => $request->input('tanggal_audit') ?: Carbon::now()->toDateString(),
                'id_kategori'     => $request->input('id_kategori'),
                'kategori'        => $request->input('kategori', 'Kategori'),
                'klausul_annex'   => $request->input('klausul_annex', 'Multi-Clause (Lihat Rincian)'),
                'latar_belakang'  => $request->input('latar_belakang', 'Audit internal merupakan salah satu persyaratan ISO/IEC 27001:2022 yang harus dipenuhi oleh Dinas Komunikasi dan Informatika Provinsi Jawa Barat sebagai bentuk dari evaluasi kinerja sistem manajemen. Audit internal dimaksudkan untuk meninjau tingkat kesesuaian dan efektivitas penerapan Sistem Manajemen Keamanan Informasi (SMKI) yang telah diimplementasikan.'),
                'tujuan'          => $request->input('tujuan', "1. Memeriksa kesesuaian atau ketidaksesuaian persyaratan standar.\n2. Memeriksa kesesuaian pencapaian tujuan keamanan informasi yang telah ditentukan.\n3. Menemukan peluang perbaikan dari proses implementasi SMKI sehingga tercapai perbaikan berkelanjutan."),
                'ruang_lingkup'   => $request->input('ruang_lingkup', "Pelaksanaan audit internal SMKI Dinas Komunikasi dan Informatika Provinsi Jawa Barat dilakukan oleh Tim Audit Internal. Ruang lingkup audit mencakup ke beberapa unit kerja terkait, diantaranya : {$unitKerjaName}"),
                'status'          => $status,
                'bidang_id'       => $request->user()?->bidang_id,
                'user_id'         => $request->user()?->id,
            ]);

            // Simpan detail temuans
            foreach ($details as $d) {
                if (!empty($d['deskripsi_temuan']) || !empty($d['klausul_annex'])) {
                    SmkiDetailTemuan::create([
                        'id_laporan_audit' => $report->id_laporan_audit,
                        'tanggal_audit'    => !empty($d['tanggal_audit']) ? $d['tanggal_audit'] : $report->tanggal_audit,
                        'id_kategori'      => $d['id_kategori'] ?? null,
                        'kategori_temuan'  => $d['kategori_temuan'] ?? 'Major',
                        'klausul_annex'    => $d['klausul_annex'] ?? '',
                        'deskripsi_temuan' => $d['deskripsi_temuan'] ?? '',
                        'rekomendasi'      => $d['rekomendasi'] ?? '',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan Audit berhasil disimpan.',
                'data'    => $report->load('detailTemuans'),
            ], 201);
        });
    }

    /**
     * Memperbarui laporan audit yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $report = SmkiLaporanAudit::find($id);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Data Laporan Audit tidak ditemukan.',
            ], 404);
        }

        $request->validate([
            'unit_kerja'     => 'required|string',
            'auditor'        => 'required|string',
            'auditee'        => 'required|string',
            'tanggal_audit'  => 'nullable|date',
            'details'        => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request, $report) {
            $unitKerjaName = trim($request->input('unit_kerja'));
            $ukRecord = SmkiUnitKerja::where('nama_unit_kerja', $unitKerjaName)->first();
            $unitKerjaId = $ukRecord ? $ukRecord->id_unit_kerja : null;

            $details = $request->input('details', []);

            // Hitung temuan
            $countMajor = 0;
            $countMinor = 0;
            $countOfi   = 0;

            foreach ($details as $d) {
                $kat = strtolower(trim($d['kategori_temuan'] ?? ''));
                if (str_contains($kat, 'major')) {
                    $countMajor++;
                } elseif (str_contains($kat, 'minor')) {
                    $countMinor++;
                } elseif (str_contains($kat, 'ofi')) {
                    $countOfi++;
                }
            }

            $temuanMajor = $request->has('temuan_major') && $request->input('temuan_major') !== '' && $request->input('temuan_major') !== null
                ? (int) $request->input('temuan_major')
                : $countMajor;
            $temuanMinor = $request->has('temuan_minor') && $request->input('temuan_minor') !== '' && $request->input('temuan_minor') !== null
                ? (int) $request->input('temuan_minor')
                : $countMinor;
            $ofi = $request->has('ofi') && $request->input('ofi') !== '' && $request->input('ofi') !== null
                ? (int) $request->input('ofi')
                : $countOfi;

            $report->update([
                'nomor_laporan'   => $request->input('nomor_laporan', $report->nomor_laporan),
                'temuan_major'    => $temuanMajor,
                'temuan_minor'    => $temuanMinor,
                'ofi'             => $ofi,
                'id_unit_kerja'   => $unitKerjaId,
                'nama_unit_kerja' => $unitKerjaName,
                'auditor'         => $request->input('auditor'),
                'auditee'         => $request->input('auditee'),
                'tanggal_audit'   => $request->input('tanggal_audit', $report->tanggal_audit),
                'id_kategori'     => $request->input('id_kategori', $report->id_kategori),
                'kategori'        => $request->input('kategori', $report->kategori),
                'klausul_annex'   => $request->input('klausul_annex', $report->klausul_annex),
                'latar_belakang'  => $request->input('latar_belakang', $report->latar_belakang),
                'tujuan'          => $request->input('tujuan', $report->tujuan),
                'ruang_lingkup'   => $request->input('ruang_lingkup', $report->ruang_lingkup),
                'status'          => $request->input('status', $report->status),
            ]);

            // Sync rincian temuan: hapus dan simpan kembali
            SmkiDetailTemuan::where('id_laporan_audit', $report->id_laporan_audit)->delete();

            foreach ($details as $d) {
                if (!empty($d['deskripsi_temuan']) || !empty($d['klausul_annex'])) {
                    SmkiDetailTemuan::create([
                        'id_laporan_audit' => $report->id_laporan_audit,
                        'tanggal_audit'    => !empty($d['tanggal_audit']) ? $d['tanggal_audit'] : $report->tanggal_audit,
                        'id_kategori'      => $d['id_kategori'] ?? null,
                        'kategori_temuan'  => $d['kategori_temuan'] ?? 'Major',
                        'klausul_annex'    => $d['klausul_annex'] ?? '',
                        'deskripsi_temuan' => $d['deskripsi_temuan'] ?? '',
                        'rekomendasi'      => $d['rekomendasi'] ?? '',
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Laporan Audit berhasil diperbarui.',
                'data'    => $report->load('detailTemuans'),
            ]);
        });
    }

    /**
     * Menghapus laporan audit beserta rincian temuan terkait.
     */
    public function destroy($id)
    {
        $report = SmkiLaporanAudit::find($id);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Data Laporan Audit tidak ditemukan.',
            ], 404);
        }

        $nomor = $report->nomor_laporan;
        $report->detailTemuans()->delete();
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => "Laporan audit {$nomor} berhasil dihapus.",
        ]);
    }

    /**
     * Ekspor Laporan Audit ke dokumen Word (.docx) resmi FR-006.
     */
    public function exportDocx($id)
    {
        $report = SmkiLaporanAudit::with(['unitKerja', 'detailTemuans'])->find($id);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Data Laporan Audit tidak ditemukan.',
            ], 404);
        }

        $docxPath = $this->docxService->generateDocx($report);
        $fileName = 'FR-006_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $report->nomor_laporan) . '.docx';

        return response()->download($docxPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
