<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\SasaranStrategis;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SasaranStrategisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SasaranStrategis::class);

        $query = SasaranStrategis::with('instansi');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_strategis', 'like', "%{$search}%")
                    ->orWhere('kode_sasaran_strategis', 'like', "%{$search}%")
                    ->orWhereHas('instansi', function ($q) use ($search) {
                        $q->where('nama_instansi', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by instansi
        if ($request->filled('instansi_id')) {
            $query->where('instansi_id', $request->get('instansi_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $sasaranStrategis = $query->orderBy('created_at', 'desc')->paginate(15);
        $instansis = Instansi::where('status', 'aktif')->orderBy('nama_instansi')->get();

        return view('sakip.sasaran-strategis.index', compact('sasaranStrategis', 'instansis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', SasaranStrategis::class);

        $instansis = Instansi::where('status', 'aktif')->orderBy('nama_instansi')->get();

        // Check if there are any instansis
        if ($instansis->isEmpty()) {
            return redirect()
                ->route('sakip.instansi.index')
                ->with('warning', 'Silakan tambahkan Instansi terlebih dahulu sebelum membuat Sasaran Strategis.');
        }

        return view('sakip.sasaran-strategis.create', compact('instansis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SasaranStrategis::class);

        $validated = $request->validate([
            'instansi_id' => 'required|exists:instansis,id',
            'kode_sasaran_strategis' => 'required|string|max:255|unique:sasaran_strategis,kode_sasaran_strategis',
            'nama_strategis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $sasaranStrategis = SasaranStrategis::create($validated);

            return redirect()
                ->route('sakip.sasaran-strategis.index')
                ->with('success', 'Sasaran Strategis berhasil ditambahkan.');
        } catch (\Exception $e) {
            \Log::error('Error creating sasaran strategis: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data sasaran strategis.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SasaranStrategis $sasaranStrategis)
    {
        $this->authorize('view', $sasaranStrategis);

        $sasaranStrategis->load(['instansi', 'programs']);

        return view('sakip.sasaran-strategis.show', compact('sasaranStrategis'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SasaranStrategis $sasaranStrategis)
    {
        $this->authorize('update', $sasaranStrategis);

        $instansis = Instansi::where('status', 'aktif')->orderBy('nama_instansi')->get();

        return view('sakip.sasaran-strategis.edit', compact('sasaranStrategis', 'instansis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SasaranStrategis $sasaranStrategis)
    {
        $this->authorize('update', $sasaranStrategis);

        $validated = $request->validate([
            'instansi_id' => 'required|exists:instansis,id',
            'kode_sasaran_strategis' => 'required|string|max:255|unique:sasaran_strategis,kode_sasaran_strategis,' . $sasaranStrategis->id,
            'nama_strategis' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $sasaranStrategis->update($validated);

            return redirect()
                ->route('sakip.sasaran-strategis.index')
                ->with('success', 'Sasaran Strategis berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating sasaran strategis: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data sasaran strategis.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SasaranStrategis $sasaranStrategis)
    {
        $this->authorize('delete', $sasaranStrategis);

        try {
            // Check if sasaran strategis has related programs
            $hasPrograms = $sasaranStrategis->programs()->count() > 0;

            if ($hasPrograms) {
                return back()->with('error', 'Sasaran Strategis tidak dapat dihapus karena memiliki Program terkait.');
            }

            $sasaranStrategis->delete();

            return redirect()
                ->route('sakip.sasaran-strategis.index')
                ->with('success', 'Sasaran Strategis berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Error deleting sasaran strategis: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus sasaran strategis.');
        }
    }

    /**
     * Get sasaran strategis by instansi (for AJAX)
     */
    public function byInstansi($instansiId)
    {
        $sasaranStrategis = SasaranStrategis::where('instansi_id', $instansiId)
            ->where('status', 'aktif')
            ->orderBy('nama_strategis')
            ->get(['id', 'kode_sasaran_strategis', 'nama_strategis']);

        return response()->json($sasaranStrategis);
    }
}
