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
    public $name, $description,$priority, $status_id, $assignee_id, $start_date, $due_date, $user_id, $department_id, $project_id;
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
    $this->assignee_id = $this->task->assignee_id;
    $this->start_date = $this->task->start_date ? $this->task->start_date->format('Y-m-d\TH:i') : null;
    $this->due_date = $this->task->due_date ? $this->task->due_date->format('Y-m-d\TH:i') : null;
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
            'description' => '',
            'priority' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'assignee_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
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
    $file = File::find($fileId);


    $file->delete();

    // Show a success message
    $this->toast('success', 'File deleted successfully.');
}
}; ?>


<div class="flex justify-center px-4 pt-4 md:px-20 md:pt-20">
    <div class="w-full p-6 bg-white shadow-md md:w-2/4 rounded-xl">

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
        <x-datetime label="End Date" wire:model.defer="due_date" type="datetime-local" />

        <x-tags label="Tags" wire:model="tags" hint="Hit enter to create a new tag" />

        <select label="Project" wire:model="project_id">
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
<div class="mb-10">
        <x-button label="Cancel" link="/" class="mt-10"/>
        <x-button label="Save Changes" spinner="saveTask" type="submit" icon="o-paper-airplane" class="btn-primary" />
</div>
    </form>

    <hr>
    <x-header title="Added Files"size="text-xl" separator  />

    <form action="{{ route('file.store',$task) }}" method="post" enctype="multipart/form-data">
        @csrf

        <input type="file" name="files[]" multiple >
        @error('files.*')
            <div class="error">{{ $message }}</div>
        @enderror
        <x-button type="submit">Upload Files</x-button>
    </form>
    @if($task->files->count() > 0)

    <header>File</header>
    <div class="overflow-x-auto">
        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($task->files as $file)
                <!-- row -->
                <tr>
                    <td>{{ $file->name }}</td>
                    <td>
                        <a href="{{ Storage::url($file->file_path) }}" download="{{ $file->name }}"><x-icon name="m-arrow-down-on-square" />
                        </a>
                        <x-button wire:click="deleteFile({{ $file->id }})" onclick="confirmDelete(event)"  icon="o-trash" class="bg-red-500" spinner />
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @else
    <p>No files available.</p>
@endif

</div>

</div>
</div>

<!-- JavaScript confirmation script -->
<script>
    function confirmDelete(event) {
        var confirmDelete = confirm("Are you sure you want to delete this file?");
        if (!confirmDelete) {
            event.preventDefault(); // Cancel the Livewire action if the user clicks "Cancel" in the confirmation
        }
    }
</script>




