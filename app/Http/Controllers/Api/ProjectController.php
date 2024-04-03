<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::whereIsCompleted(true)->with('technologies')->with('type')->paginate(6);

        //Correggo il path delle immagini 
        foreach ($projects as $project) {
            if ($project->image) $project->image = url('storage/' . $project->image);
        };


        return response()->json($projects);

        
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $project = Project::whereIsCompleted(true)->with('technologies')->with('type')->whereSlug($slug)->first();

        if (!$project) return response(null, 404);

        //Correggo il path dell'immagine
        if ($project->image) $project->image = url('storage/' . $project->image);

        return response()->json($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}