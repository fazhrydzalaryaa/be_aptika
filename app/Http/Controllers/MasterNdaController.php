<?php

namespace App\Http\Controllers;

use App\Models\MasterNda;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterNdaController extends Controller
{
    public function index()
    {
        try {
            $items = MasterNda::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data master NDA berhasil diambil.',
                'data' => $items,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data master NDA.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'                  => 'required|string|max:255',
            'nama_pihak_pertama'     => 'required|string|max:255',
            'nip_pihak_pertama'      => 'nullable|string|max:100',
            'jabatan_pihak_pertama'  => 'nullable|string|max:255',
            'instansi_pihak_pertama' => 'nullable|string|max:255',
            'klausul_perjanjian'     => 'nullable|string',
            'is_active'              => 'nullable|boolean',
        ]);

        try {
            if (!isset($validated['instansi_pihak_pertama']) || empty($validated['instansi_pihak_pertama'])) {
                $validated['instansi_pihak_pertama'] = 'Dinas Komunikasi dan Informatika Provinsi Jawa Barat';
            }
            if (!isset($validated['is_active'])) {
                $validated['is_active'] = true;
            }

            $item = DB::transaction(function () use ($validated) {
                return MasterNda::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Master NDA berhasil ditambahkan.',
                'data' => $item,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat master NDA.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = MasterNda::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data master NDA ditemukan.',
                'data' => $item,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Master NDA tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data master NDA.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $item = MasterNda::findOrFail($id);

        $validated = $request->validate([
            'judul'                  => 'required|string|max:255',
            'nama_pihak_pertama'     => 'required|string|max:255',
            'nip_pihak_pertama'      => 'nullable|string|max:100',
            'jabatan_pihak_pertama'  => 'nullable|string|max:255',
            'instansi_pihak_pertama' => 'nullable|string|max:255',
            'klausul_perjanjian'     => 'nullable|string',
            'is_active'              => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($item, $validated) {
                $item->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Master NDA berhasil diperbarui.',
                'data' => $item->fresh(),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Master NDA tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui master NDA.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = MasterNda::findOrFail($id);

            DB::transaction(function () use ($item) {
                $item->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Master NDA berhasil dihapus.',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Master NDA tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus master NDA.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}

