<?php

use Livewire\Volt\Component;
use App\Models\Task;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Carbon\Carbon;


new class extends Component {
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
    public function mount()
    {
        // Load data for dropdowns
        $this->statuses = Status::all();
        $this->users = User::all();
        $this->projects = Project::all();
    }
    public function saveTask()
    {
        // Validation logic
        $validatedData = $this->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'status_id' => 'required',
            'tags' => '',
            'priority' => 'required|in:1,2,3,4',
            'notification' => 'boolean',
            'assignee_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'project_id' => 'required|uuid|exists:projects,id', // Ensure 'id' is the UUID in your projects table

        ]);
        
        //Task::create($validatedData);

        $task = task::create([
            'name' => $this->name,
            'description' => $this->description,
            'status_id' => $this->status_id,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'assignee_id' => $this->assignee_id,
            'end_date' => $this->end_date,
            'tags' => $this->tags,
            'project_id' => $this->project_id,
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
        ]);
        $this->resetForm();

    }
    private function resetForm()
{
    $this->name = '';
    $this->description = '';
    $this->status_id = null;
    $this->priority = null;
    $this->start_date = null;
    $this->assignee_id = null;
    $this->end_date = null;
    $this->tags = [];
    $this->project_id = null;
    $this->notification = false;
}
}; ?>

<div>
    <div>
        <form wire:submit.prevent="saveTask">
            <x-errors title="Oops!" description="Please, fix the errors below." />
            
                <x-input label="Task Name" wire:model.defer="name" placeholder="Enter task name" />
                <x-textarea label="Description" wire:model.defer="description" placeholder="Enter task description" />
                <x-select label="" icon="o-bell"  :options="$statuses" wire:model="status_id" inline />

            <select label="priorities" wire:model="priority"  class="px-10 py-2 mt-2 rounded shadow">
                <option value="">Choisie a Priorite</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
                @endforeach
            </select>
            <x-select label="Etiquettes" icon="o-user" :options="$users" wire:model="assignee_id" />
            <x-datetime label="Date + Time" wire:model.defer="start_date" icon="o-calendar" type="datetime-local" />
            <x-datetime label="Date + Time" wire:model.defer="end_date" icon="o-calendar" type="datetime-local" />
            <x-tags label="Tags" wire:model="tags" icon="o-home" hint="Hit enter to create a new tag" />
            <x-select label="Projects" icon="o-key" :options="$projects" wire:model="project_id" />
            <x-checkbox label="Send Notification" wire:model.defer="notification" class="mt-12" />

                <x-button label="Cancel" link="/" class="mt-10"/>
                <x-button label="Create" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="save" />
        </form>
    </div>
    
</div>
