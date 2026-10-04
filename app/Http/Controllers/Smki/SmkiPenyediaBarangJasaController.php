<?php

namespace App\Http\Controllers\Smki;

use App\Http\Controllers\Controller;
use App\Models\SmkiKategoriRuangLingkup;
use App\Models\SmkiPenyediaBarangJasa;
use App\Models\SmkiRuangLingkup;
use App\Services\SmkiPenyediaBarangJasaDocxService;
use Illuminate\Http\Request;

class SmkiPenyediaBarangJasaController extends Controller
{
    protected SmkiPenyediaBarangJasaDocxService $docxService;

    public function __construct(SmkiPenyediaBarangJasaDocxService $docxService)
    {
        $this->docxService = $docxService;
    }

    /**
     * Display a listing of penyedia with filters, stats, and pagination.
     */
    public function index(Request $request)
    {
        $query = SmkiPenyediaBarangJasa::with(['kategoriRuangLingkup', 'ruangLingkup', 'user:id,name,email'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('no_kontrak', 'like', "%{$search}%")
                  ->orWhereHas('ruangLingkup', fn($r) => $r->where('nama_ruang_lingkup', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_ruang_lingkup_id', $request->input('kategori_id'));
        }

        if ($request->filled('ruang_lingkup_id')) {
            $query->where('ruang_lingkup_id', $request->input('ruang_lingkup_id'));
        }

        if ($request->filled('berita_acara')) {
            $query->where('berita_acara', $request->input('berita_acara'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage === -1) {
            $items = $query->get();
            $paginated = null;
        } else {
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
        }

        $base = SmkiPenyediaBarangJasa::query();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'stats'   => [
                'total_penyedia' => (clone $base)->count(),
                'ba_lengkap'     => (clone $base)->where('berita_acara', 'Tersedia')->count(),
                'ba_menunggu'    => (clone $base)->where('berita_acara', 'Tidak Ada')->count(),
                'scope_berbeda'  => (clone $base)->whereNotNull('ruang_lingkup_id')->distinct('ruang_lingkup_id')->count('ruang_lingkup_id'),
            ],
            'meta' => $paginated ? [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ] : ['total' => count($items)],
        ]);
    }

    /**
     * Master lookup: Kategori Ruang Lingkup beserta Ruang Lingkup anaknya.
     */
    public function lookup()
    {
        return response()->json([
            'success'       => true,
            'kategori'      => SmkiKategoriRuangLingkup::orderBy('nama_kategori')->get(['id', 'nama_kategori']),
            'ruang_lingkup' => SmkiRuangLingkup::orderBy('nama_ruang_lingkup')->get(['id', 'nama_ruang_lingkup', 'kategori_ruang_lingkup_id']),
        ]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'nama_perusahaan'           => 'required|string|max:255',
            'alamat'                    => 'required|string',
            'no_kontrak'                => 'nullable|string|max:255',
            'kategori_ruang_lingkup_id' => 'nullable|exists:smki_kategori_ruang_lingkups,id',
            'ruang_lingkup_id'          => 'nullable|exists:smki_ruang_lingkups,id',
            'contact_person'            => 'required|string|max:255',
            'no_telp'                   => 'required|string|max:50',
            'berita_acara'              => 'required|in:Tersedia,Tidak Ada',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $penyedia = SmkiPenyediaBarangJasa::create(array_merge($validated, [
            'user_id'   => auth()->id(),
            'bidang_id' => auth()->user()?->bidang_id,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Data penyedia baru berhasil ditambahkan!',
            'data'    => $penyedia->load(['kategoriRuangLingkup', 'ruangLingkup']),
        ], 201);
    }

    public function show($id)
    {
        $penyedia = SmkiPenyediaBarangJasa::with(['kategoriRuangLingkup', 'ruangLingkup', 'user:id,name,email'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $penyedia]);
    }

    public function update(Request $request, $id)
    {
        $penyedia = SmkiPenyediaBarangJasa::findOrFail($id);
        $validated = $this->validatePayload($request);

        $penyedia->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data penyedia berhasil diperbarui!',
            'data'    => $penyedia->load(['kategoriRuangLingkup', 'ruangLingkup']),
        ]);
    }

    public function destroy($id)
    {
        $penyedia = SmkiPenyediaBarangJasa::findOrFail($id);
        $penyedia->delete();

        return response()->json(['success' => true, 'message' => 'Data penyedia berhasil dihapus!']);
    }

    /**
     * Export daftar penyedia ke template resmi FR-020.
     * - Jika `ids` dikirim: hanya penyedia terpilih yang diekspor.
     * - Jika tidak: semua data sesuai filter aktif.
     */
    public function exportDocx(Request $request)
    {
        try {
            $query = SmkiPenyediaBarangJasa::with(['kategoriRuangLingkup', 'ruangLingkup']);

            // Data terpilih: ?ids[]=1&ids[]=3 atau ?ids=1,3
            $ids = $request->input('ids', []);
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids))));

            if (!empty($ids)) {
                // Admin memilih baris tertentu -> hanya data tersebut yang diekspor.
                $query->whereIn('id', $ids);
            } else {
                // Tanpa pilihan -> ekspor semua data sesuai filter aktif.
                if ($request->filled('search')) {
                    $search = trim($request->input('search'));
                    $query->where(function ($q) use ($search) {
                        $q->where('nama_perusahaan', 'like', "%{$search}%")
                          ->orWhere('contact_person', 'like', "%{$search}%")
                          ->orWhere('no_kontrak', 'like', "%{$search}%")
                          ->orWhereHas('ruangLingkup', fn($r) => $r->where('nama_ruang_lingkup', 'like', "%{$search}%"));
                    });
                }
                if ($request->filled('kategori_id')) {
                    $query->where('kategori_ruang_lingkup_id', $request->input('kategori_id'));
                }
                if ($request->filled('ruang_lingkup_id')) {
                    $query->where('ruang_lingkup_id', $request->input('ruang_lingkup_id'));
                }
                if ($request->filled('berita_acara')) {
                    $query->where('berita_acara', $request->input('berita_acara'));
                }
            }

            $items = $query->orderBy('id', 'asc')->get();

            $options = [
                'no_dokumen'      => $request->input('no_dokumen', 'FR-20/KOM.03.05/SANDIKAMI'),
                'no_revisi'       => $request->input('no_revisi', '1.0'),
                'tanggal_berlaku' => $request->input('tanggal_berlaku', '07 Juli 2022'),
                'periode'         => $request->input('periode', date('Y')),
            ];

            $docxPath = $this->docxService->generateDocx($items, $options);
            $fileName = 'FR-020_Daftar_Penyedia_' . date('Y-m-d') . '.docx';

            return response()->download($docxPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak dokumen. Silakan coba beberapa saat lagi. (' . $e->getMessage() . ')',
            ], 500);
        }
    }
}
