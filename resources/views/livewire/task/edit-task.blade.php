<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Task;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;  // Required for file uploads
use App\Models\Project;
use Carbon\Carbon;
use Mary\Traits\Toast;



new #[Layout('layouts.app')] class extends Component {
    //
    use Toast;
    use WithFileUploads;

    public Task $task;
    public $files = [];
    public $name, $description,$priority, $status_id, $notification = false, $assignee_id, $start_date, $end_date, $user_id, $department_id, $project_id;
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

    public function mount(Task $task)
    {
        $this->task = $task;
        $this->loadTaskProperties();
    }
    
    private function loadTaskProperties()
{
    $this->name = $this->task->name;
    $this->description = $this->task->description;
    $this->priority = $this->task->priority;
    $this->status_id = $this->task->status_id;
    $this->notification = $this->task->notification;
    $this->assignee_id = $this->task->assignee_id;
    $this->start_date = $this->task->start_date ? $this->task->start_date->format('Y-m-d\TH:i') : null;
    $this->end_date = $this->task->end_date ? $this->task->end_date->format('Y-m-d\TH:i') : null;
    $this->project_id = $this->task->project_id;
    $this->tags = $this->task->tags; // Assuming 'tags' is an array
    $this->statuses = Status::all();
        $this->users = User::all();
        $this->projects = Project::all();

    // Add any other properties that you need to load from the task
}


public function saveTask()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'notification' => 'boolean',
            'assignee_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'project_id' => 'required|exists:projects,id',
            'tags' => 'array',
        ]);
   
        
        $this->task->update($validated);

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
}; ?>


<div class="flex justify-center">
<div class="w-2/4 p-6 bg-white shadow-md rounded-xl">


    <form wire:submit.prevent="saveTask">
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

        <select label="Assignee" wire:model="assignee_id">
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>

        <x-datetime label="Start Date" wire:model.defer="start_date" type="datetime-local" />
        <x-datetime label="End Date" wire:model.defer="end_date" type="datetime-local" />

        <x-tags label="Tags" wire:model="tags" hint="Hit enter to create a new tag" />

        <select label="Project" wire:model="project_id">
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>

        <x-checkbox label="Send Notification" wire:model.defer="notification" />
        <x-button label="Cancel" link="/" class="mt-10"/>
        <x-button label="Save Changes" spinner="saveTask" type="submit" icon="o-paper-airplane" class="btn-primary" />
    </form>

    <hr>
<div class="pt-6">
    <header>Ajouter File</header>
    <form action="{{ route('file.store',$task) }}" method="post" enctype="multipart/form-data">
        @csrf

        <input type="file" name="files[]" multiple >
        @error('files.*')
            <div class="error">{{ $message }}</div>
        @enderror
        <button type="submit">Upload Files</button>
    </form>
    


</div>
</div>
</div>




