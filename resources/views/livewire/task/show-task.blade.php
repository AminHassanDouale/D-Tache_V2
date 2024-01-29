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
use Illuminate\Support\Facades\Storage;
use App\Mail\TaskCommented;
use Illuminate\Support\Facades\Mail;

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
    public $newComment = '';
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
    $this->files = $this->task->files; // Assuming a one-to-many relationship

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
public function deleteFile($fileId)
{
    $file = File::findOrFail($fileId);
    $file->delete();
}


// Add a method to save new comments
public function addComment()
{
    $validatedData = $this->validate([
        'newComment' => 'required|string|max:255', // Validate the new comment
    ]);

    $this->task->comments()->create([
        'model_id' => $this->task->id, 
        'model_type' => Task::class,
        'comment' => $this->newComment,
        'user_id' => auth()->id(),
        'department_id' => auth()->user()->department_id,
        'date' => now(),
    ]);

    $this->newComment = ''; // Clear the new comment input after saving
    $this->task->load('comments'); // Reload the comments relationship to include the new comment

    $taskOwner = $this->task->user; // Assuming user_id is related to 'user' relation
    $taskAssignee = $this->task->assignee; // Assuming assignee_id is related to 'assignee' relation

    // Prepare the comment text for the email
    $commentText = $validatedData['newComment'];

    // Import the TaskCommented Mailable at the top of your component

    // Send email to task owner
    if ($taskOwner) {
        Mail::to($taskOwner->email)->send(new TaskCommented($this->task, $commentText));
    }

    // Send email to assignee if different from owner
    if ($taskAssignee && $taskAssignee->id !== $taskOwner->id) {
        Mail::to($taskAssignee->email)->send(new TaskCommented($this->task, $commentText));
    }
}

}; ?>

<div class="px-10"> <!-- adds horizontal padding -->

<div class="flex justify-start">
    <div class="w-3/4 pt-4 bg-white shadow-md rounded-xl">


    <form wire:submit.prevent="saveTask">
        <!-- Other task fields -->
        <x-errors title="Oops!" description="Please, fix the errors below." />

        <x-input label="Task Name" wire:model.defer="name" placeholder="Enter task name" />
        <x-textarea label="Description" wire:model.defer="description" placeholder="Enter task description" />
<div>
        <label for="priority">Priority</label>
<select id="priority" wire:model="priority">
    @foreach ($priorities as $priority)
        <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
    @endforeach
</select>
</div>
<br>
<div>
<label for="priority">Status</label>

        <select label="Status" wire:model="status_id">
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
            @endforeach
        </select>
    </div>
<br>
<label for="assignee_id">Assignee</label>
        <select label="Assignee" wire:model="assignee_id">
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>

        <x-datetime label="Start Date" wire:model.defer="start_date" type="datetime-local" />
        <x-datetime label="End Date" wire:model.defer="end_date" type="datetime-local" />

        <x-tags label="Tags" wire:model="tags" hint="Hit enter to create a new tag" />
        <x-file wire:model="files" label="Documents" multiple id="fileInput" />

        <select label="Project" wire:model="project_id">
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>

        <x-checkbox label="Send Notification" wire:model.defer="notification" />
        <x-button label="Cancel" link="/" class="mt-10"/>
        <x-button label="Save Changes" spinner="saveTask" type="submit" icon="o-paper-airplane" class="btn-primary" />
    </form>
    
    @if (session()->has('message'))
        <div>{{ session('message') }}</div>
    @endif
</div>
</div>


<div>
    @foreach ($task->files as $file)
        <div>
             <a href="{{ Storage::url($file->file_path) }}" download="{{ $file->name }}">

                {{ $file->name }} ({{ $file->size }} bytes)
            </a>
            <button wire:click="deleteFile({{ $file->id }})">Delete</button>

        </div>
    @endforeach
</div>

<div>
    <textarea wire:model="newComment"></textarea>
    <button wire:click="addComment">Add Comment</button>
</div>

<div>
    @foreach ($task->comments as $comment)
        <div>{{ $comment->comment }}</div>
    @endforeach
</div>

</div>