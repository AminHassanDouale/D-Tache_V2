<?php

namespace App\Livewire\Project;

use App\Models\Member;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Str;


class Create extends Component
{
    public $name, $description, $status_id, $priority, $start_date, $due_date, $remark, $tags, $user_id, $department_id;
    public $statuses; 
    public string $privacy = 'private';
    public $priorities = [
        ['value' => '1', 'label' => '🚩 Priority 1'], // Red flag emoji
        ['value' => '2', 'label' => '🟧 Priority 2'], // Orange square emoji
        ['value' => '3', 'label' => '🟦 Priority 3'], // Blue square emoji
        ['value' => '4', 'label' => '⬜ Priority 4'], // White square emoji
    ];
    public $selectedUsers = [];
    public $users;
    public function mount()
    {
        $this->statuses = Status::all(); // Fetch statuses
        $this->users = User::all(); // Fetch all users

    }
    public function submit()
{
    $validatedData = $this->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status_id' => 'required|exists:statuses,id',
        'priority' => 'required|in:1,2,3,4',
        'start_date' => 'nullable|date',
        'due_date' => 'nullable|date|after_or_equal:start_date',
        'remark' => 'nullable|string',
        'tags' => 'nullable|string',
        'user_id' => 'required|exists:users,id',
        'department_id' => 'required|exists:departments,id',
        'selectedUsers' => 'required_if:privacy,public|array',
        'selectedUsers.*' => 'exists:users,id'
    ]);

    // Create the project
   
    $project = new Project();
    $project->fill($validatedData);
    $project->uuid = (string) Str::uuid();  
    $project->user_id = Auth::user()->id;
    $project->department_id = Auth::user()->department_id;

    $project->save();

    // Add members if privacy is public
    if ($this->privacy === 'public' && !empty($this->selectedUsers)) {
        foreach ($this->selectedUsers as $userId) {
            Member::create([
                'model_id' => $project->id,
                'model_type' => Project::class,
                'user_id' => $userId,
                'department_id' => $this->department_id, // Assuming this is relevant
                'date' => Carbon::now(), // Set the current date or another relevant date
            ]);
        }
    }
dd($validatedData);
    // Reset form fields
    $this->reset();

    // Provide user feedback (optional)
    session()->flash('message', 'Project successfully created.');
}

    public function render()
    {
        return view('livewire.project.create')->layout('layouts.app');
    }

   }
