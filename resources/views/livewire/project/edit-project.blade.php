<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Task;
use App\Models\Project;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;  // Required for file uploads
use Carbon\Carbon;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] class extends Component {
    use Toast;
    use WithFileUploads;

    public Project $project;
    public $files = [];
    public $name, $description,$priority, $status_id, $notification = false, $assignee_id, $start_date, $due_date, $user_id, $department_id, $project_id;
    public $statuses;
    public $users;
    public $tags = [];
    public $projects;
    public $priorities = [
        ['value' => '1', 'label' => '🚩 Priority 1'], // Red flag emoji
        ['value' => '2', 'label' => '🟧 Priority 2'], // Orange square emoji
        ['value' => '3', 'label' => '🟦 Priority 3'], // Blue square emoji
        ['value' => '4', 'label' => '⬜ Priority 4'], // White square emoji
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->loadProjectProperties();

    }
    private function loadProjectProperties()
{
    $this->name = $this->project->name;
    $this->description = $this->project->description;
    $this->priority = $this->project->priority;
    $this->status_id = $this->project->status_id;
    $this->notification = $this->project->notification;
    $this->start_date = $this->project->start_date ? $this->project->start_date->format('Y-m-d\TH:i') : null;
    $this->due_date = $this->project->due_date ? $this->project->due_date->format('Y-m-d\TH:i') : null;
    $this->tags = $this->project->tags; // Assuming 'tags' is an array
    $this->statuses = Status::all();
    $this->users = User::all();

    // Add any other properties that you need to load from the task
}
public function saveProject()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'notification' => '',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'tags' => 'array',
        ]);
   
        
        $this->project->update($validated);

        $this->toast(
            type: 'warning',
            title: 'Mise A jour!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-start',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );

       
}
public function handleFileUploads() {
        foreach ($this->files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs('project_files', $fileName, 'public');

            File::create([
                'model_id' => $this->project->id,
                'model_type' => Project::class,
                'filename' => $fileName,
                'file_path' => $filePath,
                'name' => $fileName,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'user_id' => Auth::id(),
                'department_id' => Auth::user()->department_id, 
            ]);
           


            $this->toast(
            type: 'warning',
            title: 'File Ajouter!',
            description: null,                  // optional (text)
            position: 'toast-top toast-start',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
        }

    }
    public function deleteFile($fileId)
{
    $file = File::find($fileId);

    if ($file && $file->user_id === auth()->id()) {
        
        $file->delete(); 
    }
}
    
}; ?>

<div>
    <div class="flex justify-center">
        <div class="w-2/4 p-6 bg-white shadow-md rounded-xl">
        
        
            <form wire:submit.prevent="saveProject">
                <!-- Other task fields -->
                <x-errors title="Oops!" description="Please, fix the errors below." />
        
                <x-input label="Task Name" wire:model.defer="name" placeholder="Enter task name" />
                <x-textarea label="Description" wire:model.defer="description" placeholder="Enter task description" />
        
                <label for="priority">Priority</label>
        <select id="priority" wire:model="priority">
            @foreach ($priorities as $priority)
                <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
            @endforeach
        </select>
        
        
                <select label="Status" wire:model="status_id">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
        
               
                <x-datetime label="Start Date" wire:model.defer="start_date" type="datetime-local" />
                <x-datetime label="End Date" wire:model.defer="due_date" type="datetime-local" />
                <x-tags label="Tags" wire:model="tags" hint="Hit enter to create a new tag" />
                <x-button label="Cancel" link="/" class="mt-10"/>
                <x-button label="Save Changes" spinner="saveTask" type="submit" icon="o-paper-airplane" class="btn-primary" />
            </form>
        </div>

        <div class="mt-4">
            <form wire:submit.prevent="saveProject">
        
                <div>
                    <x-file label="Files" wire:model="files" multiple />
                    @error('files.*') <span class="error">{{ $message }}</span> @enderror
                </div>
                    <x-button  wire:click="handleFileUploads"  external icon="o-link" tooltip="Upload File">Upload </x-button>
            
                <!-- You can add a button to specifically trigger file uploads -->
               
            
            </form>
            <h3 class="text-lg font-medium text-gray-900">Files</h3>
            @if($project->files->isNotEmpty())
                <ul class="mt-2">
                    @foreach($project->files as $file)
                        <li class="flex items-center justify-between py-2">
                            <span class="flex-grow">{{ $file->name }}</span>
                            
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2 text-sm text-gray-600">No files uploaded.</p>
            @endif
        </div>
        
    </div>
        </div>
        
