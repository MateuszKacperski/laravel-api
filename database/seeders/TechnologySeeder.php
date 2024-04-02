<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Technology;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project_ids = Project::pluck('id')->toArray();

        $technologies = [
            ['label' => 'HTML', 'color' => 'info'],
            ['label' => 'CSS', 'color' => 'primary'],
            ['label' => 'ES6', 'color' => 'warning'],
            ['label' => 'Vue', 'color' => 'success'],
            ['label' => 'PHP', 'color' => 'dark'],
            ['label' => 'SQL', 'color' => 'secondary'],
            ['label' => 'Laravel', 'color' => 'danger'],
        ];

        foreach ($technologies as $technology) {
            $new_technology = new Technology();
            $new_technology->label = $technology['label'];
            $new_technology->color = $technology['color'];
            $new_technology->save();

            $technology_projects = [];
            foreach ($project_ids as $project_id){
                if(rand(0, 1)) $technology_projects[] = $project_id;
            }

            $new_technology->projects()->attach($technology_projects);

        }
    }
}
