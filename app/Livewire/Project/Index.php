<?php

namespace App\Livewire\Project;

use App\Models\Project;
use Livewire\Component;

class Index extends Component
{
    public $projects;

    public function mount()
    {
        $this->projects = Project::all();
    }

    public function render()
    {
        return view('livewire.project.index')->layout('layouts.app');
    }
  
}
