<?php

namespace App\Http\Controllers\SPD;

use App\Http\Controllers\Controller;
use App\Models\AlatAngkutan;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlatAngkutanController extends Controller
{
    public function index()
    {
        try {
            $items = AlatAngkutan::orderBy('nama', 'asc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data alat angkutan berhasil diambil.',
                'data' => $items,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data alat angkutan.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:alat_angkutans,nama',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        try {
            $item = DB::transaction(function () use ($validated) {
                return AlatAngkutan::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Alat angkutan berhasil ditambahkan.',
                'data' => $item,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat alat angkutan.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = AlatAngkutan::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data alat angkutan ditemukan.',
                'data' => $item,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat angkutan tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data alat angkutan.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $item = AlatAngkutan::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:100|unique:alat_angkutans,nama,' . $item->id,
            'deskripsi' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($item, $validated) {
                $item->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Alat angkutan berhasil diperbarui.',
                'data' => $item->fresh(),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat angkutan tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui alat angkutan.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = AlatAngkutan::findOrFail($id);

            DB::transaction(function () use ($item) {
                $item->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Alat angkutan berhasil dihapus.',
                'data' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alat angkutan tidak ditemukan.',
                'errors' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus alat angkutan.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
