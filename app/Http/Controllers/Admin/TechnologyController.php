<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Technology;
use Illuminate\Validation\Rule;

class TechnologyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $technologies = Technology::all();
        return view('admin.technologies.index', compact('technologies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.technologies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|unique:technologies',
            'color' => 'nullable|hex_color',

        ], [
            'label.required' => 'Nessun etichetta inserita',
            'color.hex_color' => 'Il formato del colore non è valido',

        ]);

        $data = $request->all();

        $technology = new Technology();

        $technology->fill($data);
        $technology->save();

        return to_route('admin.technologies.index', $technology->id)->with('message', "Nuovo tipo creato: $technology->label")->with('technology', "success");
    }

    /**
     * Display the specified resource.
     */
    public function show(Technology $technology)
    {
        return view('admin.technologies.show', compact('technology'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technology $technology)
    {
        return view('admin.technologies.edit', compact('technology'));
    }
        

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Technology $technology)
    {
        $request->validate([
            'label' =>
            ['required', 'string', Rule::unique('technologies')->ignore($technology->id)],
            'color' => 'nullable|hex_color',

        ], [
            'label.required' => 'Nessun etichetta inserita',
            'color.hex_color' => 'Il formato del colore non è valido',

        ]);

        $data = $request->all();
        $technology->update($data);

        return to_route('admin.technologies.index', $technology->id)->with('message', "Tipo modificato")->with('technology', "info");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technology $technology)
    {
        $technology->delete();
        return to_route('admin.technologies.index')->with('message', "$technology->label eliminato")->with('technology', "danger");
    }

}
