<?php

namespace App\Http\Controllers\Smki;

use App\Http\Controllers\Controller;
use App\Models\DetailAudit;
use App\Models\Auditor;
use App\Models\Auditee;
use App\Models\BidangAuditee;
use App\Models\LokasiAuditee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmkiRencanaAuditController extends Controller
{
    /**
     * Menampilkan daftar Rencana Audit (F05-SMKI) dengan live search,
     * filter status, paginasi, dan statistik ringkasan KPI.
     */
    public function index(Request $request)
    {
        $query = DetailAudit::with([
            'auditor',
            'auditee.bidang',
            'auditee.lokasi',
        ])
        ->orderBy('tanggal_audit', 'asc')
        ->orderBy('id_detail_audit', 'desc');

        // Live Search Multi-kolom
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('kontrol_SMKI', 'like', "%{$search}%")
                  ->orWhere('kode_prosedur', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%")
                  ->orWhereHas('auditor', function ($a) use ($search) {
                      $a->where('nama_auditor', 'like', "%{$search}%")
                        ->orWhere('nip_auditor', 'like', "%{$search}%");
                  })
                  ->orWhereHas('auditee.bidang', function ($b) use ($search) {
                      $b->where('bidang_auditee', 'like', "%{$search}%");
                  })
                  ->orWhereHas('auditee.lokasi', function ($l) use ($search) {
                      $l->where('lokasi_auditee', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Status
        if ($request->filled('status') && $request->input('status') !== 'ALL') {
            $query->where('status', $request->input('status'));
        }

        // Filter Auditor
        if ($request->filled('id_auditor')) {
            $query->where('id_auditor', $request->input('id_auditor'));
        }

        // Filter Bidang Auditee
        if ($request->filled('id_bidang_auditee')) {
            $query->whereHas('auditee', function ($a) use ($request) {
                $a->where('id_bidang_auditee', $request->input('id_bidang_auditee'));
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('tanggal_audit', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tanggal_audit', '<=', $request->input('date_to'));
        }

        // Paginasi
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage === -1) {
            $items = $query->get();
            $paginated = null;
        } else {
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
        }

        // Statistik Ringkasan Dashboard KPI
        $totalScheduled = DetailAudit::where('status', 'SCHEDULED')->count();
        $inProgress = DetailAudit::where('status', 'IN PROGRESS')->count();
        $needAttention = DetailAudit::where('status', 'PENDING')->count();
        $completed = DetailAudit::where('status', 'COMPLETED')->count();
        $totalAll = DetailAudit::count();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'stats'   => [
                'total_terjadwal' => $totalScheduled,
                'dalam_proses'    => $inProgress,
                'butuh_perhatian' => $needAttention,
                'selesai'         => $completed,
                'total_aktif'     => $totalAll,
            ],
            'metadata' => [
                'no_dokumen'      => 'F05-SMKI-APTIKA',
                'no_revisi'       => '1.0',
                'tanggal_berlaku' => '20 Mei 2024',
                'judul_formulir'  => 'Formulir Rencana Audit Internal SMKI',
            ],
            'meta'    => $paginated ? [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ] : [
                'total' => count($items),
            ],
        ]);
    }

    /**
     * Mengambil master lookup dropdown (Auditor, Bidang Auditee, Lokasi Auditee, Klausul Rekomendasi).
     */
    public function lookup()
    {
        $auditors = Auditor::select('id_auditor', 'nama_auditor', 'nip_auditor')
            ->orderBy('nama_auditor', 'asc')
            ->get();

        $bidangList = BidangAuditee::select('id_bidang_auditee', 'bidang_auditee')
            ->orderBy('bidang_auditee', 'asc')
            ->get();

        $lokasiList = LokasiAuditee::select('id_lokasi_auditee', 'lokasi_auditee')
            ->orderBy('lokasi_auditee', 'asc')
            ->get();

        $standardClauses = [
            'Klausul 4.1 - Memahami Organisasi dan Konteksnya',
            'Klausul 5.1 - Kepemimpinan dan Komitmen',
            'Klausul 5.2 - Kebijakan Keamanan Informasi',
            'Klausul 6.1 - Tindakan untuk Mengatasi Risiko dan Peluang',
            'Klausul 7.2 - Kompetensi Sumber Daya Manusia',
            'Klausul 7.5 - Informasi Terdokumentasi',
            'Klausul 8.1 - Perencanaan dan Pengendalian Operasional',
            'Klausul 9.2 - Audit Internal & Tinjauan Manajemen',
            'Klausul 10.1 - Peningkatan Berkelanjutan & Tindakan Korektif',
            'A.5.1 - Kebijakan Keamanan Informasi & Prosedur Evaluasi Keamanan',
            'A.8.1 - Tanggung Jawab Aset & Klasifikasi Informasi',
            'A.9.2 - Pengelolaan Akses Pengguna & Otentikasi Terpusat',
            'A.12.1 - Prosedur Operasi Keamanan',
            'A.13.1 - Manajemen Keamanan Jaringan Komunikasi',
            'A.14.2 - Keamanan dalam Pengembangan dan Dukungan Sistem',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'auditors'         => $auditors,
                'bidang_auditee'   => $bidangList,
                'lokasi_auditee'   => $lokasiList,
                'standard_clauses' => $standardClauses,
                'default_meta'     => [
                    'no_dokumen'      => 'F05-SMKI-APTIKA',
                    'no_revisi'       => '1.0',
                    'tanggal_berlaku' => '20 Mei 2024',
                ],
            ],
        ]);
    }

    /**
     * Menyimpan data Rencana Audit baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kontrol_SMKI'      => 'required|string',
            'tanggal_audit'     => 'required|date',
            'id_auditor'        => 'nullable|exists:auditor,id_auditor',
            'nama_auditor'      => 'nullable|string|max:255',
            'nip_auditor'       => 'nullable|string|max:100',
            'id_bidang_auditee' => 'nullable|exists:bidang_auditee,id_bidang_auditee',
            'bidang_auditee'    => 'nullable|string|max:255',
            'id_lokasi_auditee' => 'nullable|exists:lokasi_auditee,id_lokasi_auditee',
            'lokasi_auditee'    => 'nullable|string|max:255',
            'kode_prosedur'     => 'nullable|string|max:100',
            'status'            => 'nullable|string|in:SCHEDULED,IN PROGRESS,PENDING,COMPLETED',
            'catatan'           => 'nullable|string',
        ], [
            'kontrol_SMKI.required'  => 'Persyaratan / Kontrol / Prosedur SMKI wajib diisi.',
            'tanggal_audit.required' => 'Tanggal audit wajib diisi.',
            'tanggal_audit.date'     => 'Format tanggal audit tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Resolve Auditor
            $auditorId = $request->input('id_auditor');
            if (!$auditorId && $request->filled('nama_auditor')) {
                $auditor = Auditor::firstOrCreate(
                    ['nama_auditor' => trim($request->input('nama_auditor'))],
                    ['nip_auditor' => trim($request->input('nip_auditor', ''))]
                );
                $auditorId = $auditor->id_auditor;
            }

            if (!$auditorId) {
                // Default ke auditor pertama jika tidak dipilih
                $firstAuditor = Auditor::first();
                $auditorId = $firstAuditor ? $firstAuditor->id_auditor : Auditor::create([
                    'nama_auditor' => 'Auditor Internal SMKI',
                    'nip_auditor'  => '-',
                ])->id_auditor;
            }

            // 2. Resolve Bidang Auditee
            $bidangId = $request->input('id_bidang_auditee');
            if (!$bidangId && $request->filled('bidang_auditee')) {
                $bidang = BidangAuditee::firstOrCreate([
                    'bidang_auditee' => trim($request->input('bidang_auditee')),
                ]);
                $bidangId = $bidang->id_bidang_auditee;
            }
            if (!$bidangId) {
                $firstBidang = BidangAuditee::first();
                $bidangId = $firstBidang ? $firstBidang->id_bidang_auditee : BidangAuditee::create([
                    'bidang_auditee' => 'Bidang Aptika',
                ])->id_bidang_auditee;
            }

            // 3. Resolve Lokasi Auditee
            $lokasiId = $request->input('id_lokasi_auditee');
            if (!$lokasiId && $request->filled('lokasi_auditee')) {
                $lokasi = LokasiAuditee::firstOrCreate([
                    'lokasi_auditee' => trim($request->input('lokasi_auditee')),
                ]);
                $lokasiId = $lokasi->id_lokasi_auditee;
            }
            if (!$lokasiId) {
                $firstLokasi = LokasiAuditee::first();
                $lokasiId = $firstLokasi ? $firstLokasi->id_lokasi_auditee : LokasiAuditee::create([
                    'lokasi_auditee' => 'Gedung Diskominfo',
                ])->id_lokasi_auditee;
            }

            // 4. Resolve Auditee (Pasangan Bidang & Lokasi)
            $auditee = Auditee::firstOrCreate([
                'id_bidang_auditee' => $bidangId,
                'id_lokasi_auditee' => $lokasiId,
            ]);

            // Auto-generate kode prosedur jika tidak ada
            $kodeProsedur = $request->input('kode_prosedur');
            if (empty($kodeProsedur)) {
                $count = DetailAudit::count() + 1;
                $kodeProsedur = 'SMKI-PROC-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            // 5. Create Detail Audit
            $detailAudit = DetailAudit::create([
                'id_auditor'    => $auditorId,
                'id_auditee'    => $auditee->id_auditee,
                'kontrol_SMKI'  => trim($request->input('kontrol_SMKI')),
                'tanggal_audit' => $request->input('tanggal_audit'),
                'kode_prosedur' => $kodeProsedur,
                'status'        => $request->input('status', 'SCHEDULED'),
                'catatan'       => $request->input('catatan'),
            ]);

            DB::commit();

            $detailAudit->load(['auditor', 'auditee.bidang', 'auditee.lokasi']);

            return response()->json([
                'success' => true,
                'message' => 'Rencana Audit berhasil ditambahkan!',
                'data'    => $detailAudit,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan rencana audit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan data detail rencana audit tertentu.
     */
    public function show($id)
    {
        $audit = DetailAudit::with(['auditor', 'auditee.bidang', 'auditee.lokasi'])->find($id);

        if (!$audit) {
            return response()->json([
                'success' => false,
                'message' => 'Data Rencana Audit tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $audit,
        ]);
    }

    /**
     * Memperbarui data Rencana Audit.
     */
    public function update(Request $request, $id)
    {
        $detailAudit = DetailAudit::find($id);

        if (!$detailAudit) {
            return response()->json([
                'success' => false,
                'message' => 'Data Rencana Audit tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kontrol_SMKI'      => 'required|string',
            'tanggal_audit'     => 'required|date',
            'id_auditor'        => 'nullable|exists:auditor,id_auditor',
            'nama_auditor'      => 'nullable|string|max:255',
            'id_bidang_auditee' => 'nullable|exists:bidang_auditee,id_bidang_auditee',
            'bidang_auditee'    => 'nullable|string|max:255',
            'id_lokasi_auditee' => 'nullable|exists:lokasi_auditee,id_lokasi_auditee',
            'lokasi_auditee'    => 'nullable|string|max:255',
            'kode_prosedur'     => 'nullable|string|max:100',
            'status'            => 'nullable|string|in:SCHEDULED,IN PROGRESS,PENDING,COMPLETED',
            'catatan'           => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update Auditor jika ada perubahan
            if ($request->filled('id_auditor')) {
                $detailAudit->id_auditor = $request->input('id_auditor');
            } elseif ($request->filled('nama_auditor')) {
                $auditor = Auditor::firstOrCreate([
                    'nama_auditor' => trim($request->input('nama_auditor')),
                ]);
                $detailAudit->id_auditor = $auditor->id_auditor;
            }

            // Update Auditee jika bidang atau lokasi diganti
            $currentAuditee = $detailAudit->auditee;
            $bidangId = $request->input('id_bidang_auditee', $currentAuditee?->id_bidang_auditee);
            if (!$bidangId && $request->filled('bidang_auditee')) {
                $bidang = BidangAuditee::firstOrCreate(['bidang_auditee' => trim($request->input('bidang_auditee'))]);
                $bidangId = $bidang->id_bidang_auditee;
            }

            $lokasiId = $request->input('id_lokasi_auditee', $currentAuditee?->id_lokasi_auditee);
            if (!$lokasiId && $request->filled('lokasi_auditee')) {
                $lokasi = LokasiAuditee::firstOrCreate(['lokasi_auditee' => trim($request->input('lokasi_auditee'))]);
                $lokasiId = $lokasi->id_lokasi_auditee;
            }

            if ($bidangId && $lokasiId) {
                $auditee = Auditee::firstOrCreate([
                    'id_bidang_auditee' => $bidangId,
                    'id_lokasi_auditee' => $lokasiId,
                ]);
                $detailAudit->id_auditee = $auditee->id_auditee;
            }

            // Update field lainnya
            $detailAudit->kontrol_SMKI  = trim($request->input('kontrol_SMKI'));
            $detailAudit->tanggal_audit = $request->input('tanggal_audit');
            if ($request->filled('kode_prosedur')) {
                $detailAudit->kode_prosedur = trim($request->input('kode_prosedur'));
            }
            if ($request->filled('status')) {
                $detailAudit->status = $request->input('status');
            }
            if ($request->has('catatan')) {
                $detailAudit->catatan = $request->input('catatan');
            }

            $detailAudit->save();

            DB::commit();

            $detailAudit->load(['auditor', 'auditee.bidang', 'auditee.lokasi']);

            return response()->json([
                'success' => true,
                'message' => 'Rencana Audit berhasil diperbarui!',
                'data'    => $detailAudit,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui rencana audit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus data Rencana Audit (Soft Delete).
     */
    public function destroy($id)
    {
        $detailAudit = DetailAudit::find($id);

        if (!$detailAudit) {
            return response()->json([
                'success' => false,
                'message' => 'Data Rencana Audit tidak ditemukan.',
            ], 404);
        }

        try {
            $detailAudit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data Rencana Audit berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
