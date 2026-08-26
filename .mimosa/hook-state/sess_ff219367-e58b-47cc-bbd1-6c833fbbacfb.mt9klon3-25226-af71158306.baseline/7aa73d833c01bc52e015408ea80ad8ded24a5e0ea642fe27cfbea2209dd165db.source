<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InstansiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Instansi::class);

        $query = Instansi::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_instansi', 'like', "%{$search}%")
                    ->orWhere('kode_instansi', 'like', "%{$search}%")
                    ->orWhere('kepala_instansi', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $instansis = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('sakip.instansi.index', compact('instansis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Instansi::class);
        return view('sakip.instansi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Instansi::class);

        $validated = $request->validate([
            'kode_instansi' => 'required|string|max:255|unique:instansis,kode_instansi',
            'nama_instansi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'kepala_instansi' => 'nullable|string|max:255',
            'nip_kepala' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $instansi = Instansi::create($validated);

            return redirect()
                ->route('sakip.instansi.index')
                ->with('success', 'Instansi berhasil ditambahkan.');
        } catch (\Exception $e) {
            \Log::error('Error creating instansi: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data instansi.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Instansi $instansi)
    {
        $this->authorize('view', $instansi);

        $instansi->load(['sasaranStrategis', 'programs', 'performanceIndicators']);

        return view('sakip.instansi.show', compact('instansi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instansi $instansi)
    {
        $this->authorize('update', $instansi);
        return view('sakip.instansi.edit', compact('instansi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instansi $instansi)
    {
        $this->authorize('update', $instansi);

        $validated = $request->validate([
            'kode_instansi' => 'required|string|max:255|unique:instansis,kode_instansi,' . $instansi->id,
            'nama_instansi' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'kepala_instansi' => 'nullable|string|max:255',
            'nip_kepala' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        try {
            $instansi->update($validated);

            return redirect()
                ->route('sakip.instansi.index')
                ->with('success', 'Instansi berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating instansi: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data instansi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instansi $instansi)
    {
        $this->authorize('delete', $instansi);

        try {
            // Check if instansi has related data
            $hasSasaranStrategis = $instansi->sasaranStrategis()->count() > 0;
            $hasPrograms = $instansi->programs()->count() > 0;
            $hasIndicators = $instansi->performanceIndicators()->count() > 0;

            if ($hasSasaranStrategis || $hasPrograms || $hasIndicators) {
                return back()->with('error', 'Instansi tidak dapat dihapus karena memiliki data terkait (Sasaran Strategis, Program, atau Indikator Kinerja).');
            }

            $instansi->delete();

            return redirect()
                ->route('sakip.instansi.index')
                ->with('success', 'Instansi berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Error deleting instansi: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus instansi.');
        }
    }
}
