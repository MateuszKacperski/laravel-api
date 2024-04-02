<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Models\Type;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        //salvo i filtri in delle variabili
        $is_completed_filter = $request->query('is_completed_filter');
        $type_filter = $request->query('type_filter');
        $technology_filter = $request->query('technology_filter');


        $projects = Project::completedFilter($is_completed_filter)
            ->typeFilter($type_filter)
            ->technologyFilter($technology_filter)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $types = Type::select('label', 'id')->get();
        $technologies = Technology::select('id','label')->get();

        return view('admin.projects.index', compact('projects','type_filter','is_completed_filter', 'technology_filter', 'types', 'technologies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $project = new Project();
        $types = Type::select('label', 'id')->get();
        $technologies = Technology::select('label', 'id')->get();

        return view('admin.projects.create', compact('project', 'types', 'technologies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|unique:projects',
            'description' => 'required|string',
            'image' => 'nullable|image',
            'type_id' => 'nullable|exists:types,id',
            'technologies' => 'nullable|exists:technologies,id',
        ], 
        [
            'title.required' => 'Il progetto deve avere un titolo',
            'description.required' => 'Il progetto deve avere una descrizione',
            'image.image' => 'Il file inserito non è un immagine',
            'type_id.exist' => 'Il tipo non è valido o esistente',
            'technologies.exists' => 'Tecnologie scelte non esistenti o non valide',
        ]);

        $data = $request->all();
        
        $project = new Project();

        $project->fill($data);

        $project->slug = Str::slug($project->title);
        $project->is_completed = Arr::exists($data, 'is_completed');

        //controllo se arriva un file
        if(Arr::exists($data, 'image')){

            //salvo nella variabile extension l'estensione dell'immagine inserita dall'utente
            $extension = $data['image']->extension(); 

            //salvo nella variabile url e in project images l'immagine rinominata con lo slug del progetto
            $img_url = Storage::putFileAs('project_images', $data['image'], "$project->slug.$extension"); 

            $project->image= $img_url;
        }
        

        $project->save();

        if(Arr::exists($data, 'technologies')) 
        {
            $project->technologies()->attach($data['technologies']);
        }

        return to_route('admin.projects.show', $project)->with('type', 'success')->with('message', 'Progetto creato con successo');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $types = Type::all();
        return view('admin.projects.show', compact('project', 'types'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $types = Type::all();
        $technologies = Technology::select('label', 'id')->get();

        //Ricavo le tecnologie utilizzate dal progetto prima di modificarlo cosi da utilizzarle nell'old nel form
        $previous_technologies = $project->technologies->pluck('id')->toArray();

        return view('admin.projects.edit', compact('project', 'types', 'technologies', 'previous_technologies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => ['required', 'string', Rule::unique('projects')->ignore($project->id)],
            'description' => 'required|string',
            'image' => 'nullable|image',
            'type_id' => 'nullable|exists:types,id',
            'technologies' => 'nullable|exists:technologies,id',
        ], 
        [
            'title.required' => 'Il progetto deve avere un titolo',
            'description.required' => 'Il progetto deve avere una descrizione',
            'image.image' => 'Il file inserito non è un immagine',
            'type_id.exist' => 'Il tipo non è valido o esistente',
            'technologies.exists' => 'Tecnologie scelte non esistenti o non valide',
        ]);
    
        $data = $request->all();

        $data['slug'] = Str::slug($data['title']);
        $data['is_completed'] = Arr::exists($data, 'is_completed');

        //controllo se arriva un file
        if(Arr::exists($data, 'image')){

            // controllo se ho un altra immagine già esistente nella cartella e la cancello
            if($project->image) Storage::delete($project->image);

            //salvo nella variabile extension l'estensione dell'immagine inserita dall'utente
            $extension = $data['image']->extension();

            //salvo nella variabile url e in project images l'immagine rinominata con lo slug del progetto
            $img_url = Storage::putFileAs('project_images', $data['image'], "{$data['slug']}.$extension"); 

            $project->image = $img_url;
            
        }
    
        $project->update($data);

        //se ho inviato uno o dei valori sincronizzo 
        if (Arr::exists($data, 'technologies')) $project->technologies()->sync($data['technologies']);

        //Se non ho inviato valori ma il project ne aveva in precedenza, vuol dire che devo eliminare valore perchè li ho tolti tutti
        elseif (!Arr::exists($data, 'technologies') && $project->has('technologies')) $project->technologies()->detach();

    
        return to_route('admin.projects.show', $project->id)->with('type', 'success')->with('message', 'Progetto modificato con successo');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return to_route('admin.projects.index')
        ->with('toast-button-type', 'danger')
        ->with('toast-message', 'Progetto eliminato')
        ->with('toast-label', config('app.name'))
        ->with('toast-method', 'PATCH')
        ->with('toast-route', route('admin.projects.restore', $project->id))
        ->with('toast-button-label', 'ANNULLA');
    }


    //Rotte Soft delete

    public function trash() {
        $projects = Project::onlyTrashed()->get();
        return view('admin.projects.trash', compact('projects'));
    }

    public function restore(Project $project){

        $project->restore();

        return to_route('admin.projects.index')->with('type', 'success')->with('message', 'Progetto ripristinato con successo');
    }

    public function drop(Project $project){

        if($project->has('technologies')) $project->technologies()->detach();
        if($project->image) Storage::delete($project->image);

        $project->forceDelete();

        return to_route('admin.projects.trash')->with('type', 'danger')->with('message', 'Progetto eliminato con successo');
    }
}
