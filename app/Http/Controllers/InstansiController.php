<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instansi;
use Illuminate\Support\Facades\Validator;

class InstansiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instansis = Instansi::withCount('programs')->paginate(10);
        return view('instansi.index', compact('instansis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('instansi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_instansi' => 'required|unique:instansis|max:20',
            'nama_instansi' => 'required|max:255',
            'alamat' => 'required',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'kepala_instansi' => 'required|max:255',
            'nip_kepala' => 'nullable|max:20',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Instansi::create($request->all());

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Instansi $instansi)
    {
        $instansi->load(['programs.kegiatans.indikatorKinerjas']);
        return view('instansi.show', compact('instansi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instansi $instansi)
    {
        return view('instansi.edit', compact('instansi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instansi $instansi)
    {
        $validator = Validator::make($request->all(), [
            'kode_instansi' => 'required|max:20|unique:instansis,kode_instansi,' . $instansi->id,
            'nama_instansi' => 'required|max:255',
            'alamat' => 'required',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'kepala_instansi' => 'required|max:255',
            'nip_kepala' => 'nullable|max:20',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $instansi->update($request->all());

        return redirect()->route('instansi.index')
            ->with('success', 'Instansi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instansi $instansi)
    {
        try {
            $instansi->delete();
            return redirect()->route('instansi.index')
                ->with('success', 'Instansi berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('instansi.index')
                ->with('error', 'Instansi tidak dapat dihapus karena masih memiliki data terkait.');
        }
    }
}
