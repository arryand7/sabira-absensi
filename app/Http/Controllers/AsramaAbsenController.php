<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\KegiatanAsrama;
use App\Models\JadwalKegiatanAsrama;
use App\Models\AbsensiAsrama;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class AsramaAbsenController extends Controller
{
    public function index()
    {
        return view('organisasi.index'); // halaman pilihan sholat / kegiatan
    }

    public function pilihSholat()
    {
        $kegiatanSholat = KegiatanAsrama::where('jenis', 'sholat')->where('berulang', true)->get();
        $tanggal = \Carbon\Carbon::now()->toDateString();
        $totalSiswa = Student::count();

        $dataSholat = $kegiatanSholat->map(function ($sholat) use ($tanggal, $totalSiswa) {
            $jadwal = JadwalKegiatanAsrama::firstOrCreate(
                [
                    'kegiatan_asrama_id' => $sholat->id,
                    'tanggal' => $tanggal,
                ],
                [
                    'jam_mulai' => null,
                    'jam_selesai' => null,
                    'dibuat_oleh' => auth()->id(),
                ]
            );

            $jumlahAbsen = AbsensiAsrama::where('jadwal_kegiatan_asrama_id', $jadwal->id)->count();
            $sudahAbsenSemua = $jumlahAbsen >= $totalSiswa;

            return [
                'sholat' => $sholat,
                'jadwal' => $jadwal,
                'sudahAbsenSemua' => $sudahAbsenSemua,
            ];
        });

        return view('organisasi.sholat.pilih', compact('dataSholat'));
    }

    public function formAbsenSholat(Request $request, $jenis = null)
    {
        $tanggal = Carbon::now()->toDateString();

        // Ambil jenis kegiatan asrama sholat yang dipilih
        $kegiatan = KegiatanAsrama::where('nama', 'like', '%'.$jenis.'%')
                    ->where('jenis', 'sholat')
                    ->where('berulang', true)
                    ->first();

        if (!$kegiatan) {
            abort(404, "Kegiatan sholat tidak ditemukan.");
        }

        // Cek apakah sudah ada jadwal kegiatan asrama untuk sholat ini di tanggal hari ini
        $jadwal = JadwalKegiatanAsrama::where('kegiatan_asrama_id', $kegiatan->id)
                    ->where('tanggal', $tanggal)
                    ->first();

        // Kalau belum ada jadwal, buatkan otomatis (default jam mulai dan selesai bisa disesuaikan)
        if (!$jadwal) {
            $jadwal = JadwalKegiatanAsrama::create([
                'kegiatan_asrama_id' => $kegiatan->id,
                'tanggal' => $tanggal,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'dibuat_oleh' => auth()->id(),
            ]);
        }

        // Ambil siswa yang sudah absen hari ini pada jadwal ini (untuk menampilkan status absen)
        $absensiHariIni = AbsensiAsrama::where('jadwal_kegiatan_asrama_id', $jadwal->id)
                            ->get()
                            ->keyBy('student_id');

        $search = $request->query('search');
        $students = null;

        if ($search) {
            $students = Student::where('nama_lengkap', 'like', "%$search%")
                        ->orderBy('nama_lengkap')
                        ->get();
        }

        return view('organisasi.sholat.form', compact('jenis', 'tanggal', 'students', 'search', 'jadwal', 'absensiHariIni'));
    }

    public function searchStudent(Request $request, $jenis)
    {
        $keyword = $request->query('keyword');

        if (!$keyword || strlen($keyword) < 3) {
            return response()->json([]);
        }

        $students = Student::where('nis', 'like', "%$keyword%")
                    ->orWhere('nama_lengkap', 'like', "%$keyword%")
                    ->orderBy('nama_lengkap')
                    ->limit(5)
                    ->get(['id', 'nis', 'nama_lengkap']);

        return response()->json($students);
    }


    public function submitAbsenSholat(Request $request, $jenis)
    {
        $tanggal = Carbon::now()->toDateString();

        // Cari kegiatan dan jadwal yang sama seperti di formAbsenSholat
        $kegiatan = KegiatanAsrama::where('nama', 'like', '%' . $jenis . '%')
            ->where('jenis', 'sholat')
            ->where('berulang', true)
            ->first();

        if (!$kegiatan) {
            abort(404, "Kegiatan sholat tidak ditemukan.");
        }

        $jadwal = JadwalKegiatanAsrama::where('kegiatan_asrama_id', $kegiatan->id)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$jadwal) {
            abort(404, "Jadwal kegiatan tidak ditemukan.");
        }

        // Ambil input absen (array of students)
        $inputAbsen = $request->input('students', []);

        foreach ($inputAbsen as $studentId => $status) {
            // Validasi nilai status
            if (!in_array($status, ['hadir', 'alpa'])) {
                $status = 'alpa'; // fallback default
            }

            // Simpan atau update absensi
            AbsensiAsrama::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'jadwal_kegiatan_asrama_id' => $jadwal->id,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return redirect()->route('asrama.sholat')->with('success', 'Absensi sholat berhasil disimpan.');
    }



    public function historySholat(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $students = Student::orderBy('nama_lengkap')->get();
        $tanggal = collect(range(1, Carbon::create($tahun, $bulan)->daysInMonth));
        $sholatList = KegiatanAsrama::where('jenis', 'sholat')->where('berulang', true)->orderBy('id')->get();

        $data = [];

        foreach ($students as $student) {
            foreach ($sholatList as $sholat) {
                $data[$student->id][$sholat->id] = [];

                foreach ($tanggal as $day) {
                    $tanggalLengkap = Carbon::create($tahun, $bulan, $day)->toDateString();

                    $jadwal = JadwalKegiatanAsrama::where('kegiatan_asrama_id', $sholat->id)
                        ->where('tanggal', $tanggalLengkap)
                        ->first();

                    if ($jadwal) {
                        $absen = AbsensiAsrama::where('student_id', $student->id)
                            ->where('jadwal_kegiatan_asrama_id', $jadwal->id)
                            ->first();

                        if ($absen) {
                            $data[$student->id][$sholat->id][$day] = $absen->status;
                        }
                    }
                }
            }
        }

        return view('organisasi.sholat.history', compact('bulan', 'tahun', 'students', 'tanggal', 'sholatList', 'data'));
    }
    
//kegiatannnn
    public function listKegiatan()
    {
        $kegiatan = JadwalKegiatanAsrama::with('kegiatanAsrama')
        ->whereHas('kegiatanAsrama', function ($q) {
            $q->where('jenis', 'kegiatan')->where('berulang', false);
        })
        ->orderBy('tanggal', 'desc')
        ->get();

        return view('organisasi.kegiatan.index', compact('kegiatan'));
    }


    public function createKegiatan(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        // 1. Buat kegiatan baru
        $kegiatan = KegiatanAsrama::create([
            'nama' => $request->nama,
            'jenis' => 'kegiatan',       // otomatis kegiatan
            'berulang' => false,         // otomatis tidak berulang
        ]);

        // 2. Buat jadwal untuk kegiatan ini
        JadwalKegiatanAsrama::create([
            'kegiatan_asrama_id' => $kegiatan->id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'dibuat_oleh' => auth()->id(),
        ]);

        return back()->with('success', 'Kegiatan berhasil dibuat.');
    }


    public function formAbsenKegiatan(Request $request, $id)
    {
        $kegiatan = JadwalKegiatanAsrama::with('kegiatanAsrama')->findOrFail($id);

        $search = $request->query('search');
        $students = null;

        if ($search) {
            $students = Student::where('nama_lengkap', 'like', "%$search%")
                ->orWhere('nis', 'like', "%$search%")
                ->orderBy('nama_lengkap')
                ->get();
        }

        $absensiHariIni = AbsensiAsrama::where('jadwal_kegiatan_asrama_id', $id)
            ->get()
            ->keyBy('student_id');

        return view('organisasi.kegiatan.absen', compact('kegiatan', 'students', 'search', 'absensiHariIni'));
    }

    public function searchStudentKegiatan(Request $request, $id)
    {
        $keyword = $request->query('keyword');

        if (!$keyword || strlen($keyword) < 3) {
            return response()->json([]);
        }

        $students = Student::where('nis', 'like', "%$keyword%")
                    ->orWhere('nama_lengkap', 'like', "%$keyword%")
                    ->orderBy('nama_lengkap')
                    ->limit(5)
                    ->get(['id', 'nis', 'nama_lengkap']);

        return response()->json($students);
    }

    public function submitAbsenKegiatan(Request $request, $id)
    {
        foreach ($request->input('students', []) as $studentId => $status) {
            if (!in_array($status, ['hadir', 'alpa'])) {
                $status = 'alpa';
            }

            AbsensiAsrama::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'jadwal_kegiatan_asrama_id' => $id,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return redirect()->route('asrama.kegiatan')->with('success', 'Absensi kegiatan berhasil disimpan.');
    }

    public function historyKegiatan($id)
    {
        $kegiatan = JadwalKegiatanAsrama::with('kegiatanAsrama')->findOrFail($id);

        // Ambil semua absensi untuk jadwal kegiatan ini, join student untuk info nama
        $absensi = AbsensiAsrama::where('jadwal_kegiatan_asrama_id', $id)
                    ->with('student')
                    ->orderBy('student_id')
                    ->get();

        return view('organisasi.kegiatan.history', compact('kegiatan', 'absensi'));
    }



    //ADMINNNNNN
    public function masterSholat()
    {
        $kegiatanSholat = KegiatanAsrama::where('jenis', 'sholat')
                            ->where('berulang', true)
                            ->orderBy('nama')
                            ->get();

        return view('admin.sholat.index', compact('kegiatanSholat'));
    }

    public function storeSholat(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        KegiatanAsrama::create([
            'nama' => $request->nama,
            'jenis' => 'sholat',
            'berulang' => true,
        ]);

        return back()->with('success', 'Kegiatan sholat berhasil ditambahkan.');
    }

    public function deleteSholat($id)
    {
        $kegiatan = KegiatanAsrama::where('id', $id)
                        ->where('jenis', 'sholat')
                        ->where('berulang', true)
                        ->firstOrFail();

        $kegiatan->delete();

        return back()->with('success', 'Kegiatan sholat berhasil dihapus.');
    }


}
