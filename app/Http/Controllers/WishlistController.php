<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->header('Authorization');

        if ($userId) {
            $data = Wishlist::where('email', $userId)
                ->orWhereNull('email')
                ->get()
                ->map(function ($item) use ($userId) {
                    $item->mine = $item->email === $userId ? 1 : 0;
                    return $item;
                });
        } else {
            $data = Wishlist::whereNull('email')
                ->get()
                ->map(function ($item) {
                    $item->mine = 0;
                    return $item;
                });
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $email = $request->header('Authorization'); // <- ambil dari header
        if($email){
            $request->validate([
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string|max:255',
                'gambar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $path = $request->file('gambar')->store('gambar-wishlist', 'public');

            Wishlist::create([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'gambar' => $path,
                'email' => $email,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil ditambahkan.'
            ]);
        
        }
    }

    public function update(Request $request, $id)
    {
        $email = $request->header('Authorization'); // Ambil email dari header

        // Validasi data input
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Cari data berdasarkan id dan email
        $wishlist = Wishlist::where('id', $id)
            ->where('email', $email)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        // Update nama
        $wishlist->judul = $request->judul;
        $wishlist->deskripsi = $request->deskripsi;

        // Jika ada gambar baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($wishlist->gambar && Storage::disk('public')->exists($wishlist->gambar)) {
                Storage::disk('public')->delete($wishlist->gambar);
            }

            // Simpan gambar baru
            $path = $request->file('gambar')->store('gambar-wishlist', 'public');
            $wishlist->gambar = $path;
        }

        // Simpan perubahan
        $wishlist->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $email = $request->header('Authorization'); // Ambil email dari header

        // Cari data berdasarkan id dan email
        $wishlist = Wishlist::where('id', $id)
            ->where('email', $email)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        // Hapus file gambar jika ada
        if ($wishlist->gambar && Storage::disk('public')->exists($wishlist->gambar)) {
            Storage::disk('public')->delete($wishlist->gambar);
        }

        // Hapus data dari database
        $wishlist->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.'
        ]);
    }
}
