<?php

namespace App\Http\Controllers;

use App\Models\AbsensiLokasi;
use Illuminate\Http\Request;

class AdminLokasiAbsenController extends Controller
{
    public function edit()
    {
        $lokasi = AbsensiLokasi::firstOrCreate([], [
            'latitude' => -7.310823820752337,
            'longitude' => 112.72923730812086,
        ]);

        return view('admin.lokasi.edit', compact('lokasi'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lokasi = AbsensiLokasi::first();
        $lokasi->update($request->only('latitude', 'longitude'));

        return redirect()->back()->with('success', 'Lokasi absen berhasil diperbarui!');
    }
}
