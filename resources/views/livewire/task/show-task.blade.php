<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Task;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Models\Comment;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;  // Required for file uploads
use App\Models\Project;
use Carbon\Carbon;
use App\Mail\TaskCommented;
use Illuminate\Support\Facades\Mail;

use Mary\Traits\Toast;



new #[Layout('layouts.app')] class extends Component {
    //
    use Toast;
    use WithFileUploads;


    public Task $task;
    public $description;
    public bool $myModal = false;
    public $files = [];
    public $comments;
    public $name;
    public $status_id;
    public $comment;
    public $priority;
    public $statuses;
    public $users;
    public $tags = [];
    public $fileToDelete;
    public $projects;
    public $assignee_id;
    public $project_id;
    public $priorities = [
        ['value' => '1', 'label' => '🚩 Priority 1'], // Red flag emoji
        ['value' => '2', 'label' => '🟧 Priority 2'], // Orange square emoji
        ['value' => '3', 'label' => '🟦 Priority 3'], // Blue square emoji
        ['value' => '4', 'label' => '⬜ Priority 4'], // White square emoji
    ];
    public $showComments = false;
    public $showFiles = false;
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
    $this->files = $this->task->files->sortBy('created_at');
    $this->comments = $this->task->comments()->orderBy('created_at')->get();

}


public function saveComment()
{
    $validatedData = $this->validate([
        'comment' => 'required|string|max:255', // Validate the new comment
    ]);

    // Assuming you have a Comment model
    $this->task->comments()->create([
        'comment' => $this->comment,
        'user_id' => Auth::id(), 
        'department_id' => Auth::user()->department_id,
        'date' => now(),
    ]);

    // Clear the comment input after saving
    $this->comment = '';
    $this->task->load('comments'); 

    
$taskOwner = $this->task->user; 
$taskAssignee = $this->task->assignee; 


$commentText = $validatedData['comment'];



if ($taskOwner) {
    Mail::to($taskOwner->email)->send(new TaskCommented($this->task, $commentText));
}


if ($taskAssignee && $taskAssignee->id !== $taskOwner->id) {
    Mail::to($taskAssignee->email)->send(new TaskCommented($this->task, $commentText));
} 
    $this->comments = $this->task->comments()->orderBy('created_at')->get();

    // You can add a toast or any other notification here
    $this->toast('success', 'Comment added successfully.');
}


public function deleteFile($fileId)
{
    if ($this->fileToDelete) {
        $file = File::find($this->fileToDelete);
        if ($file) {
            $file->delete();
            $this->fileToDelete = null; 
        }
    }
  
    
    $this->toast('success', 'File deleted successfully.');
}

public function toggleComments()
{
    $this->showComments = !$this->showComments;
}
public function toggleFiles()
{
    $this->showFiles = !$this->showFiles;
}
public function deleteComment($commentId)
{
    $comment = Comment::find($commentId);

    if ($comment && $comment->user_id === Auth::id()) {
        $comment->delete();
        $this->toast('success', 'Comment deleted successfully.');
        $this->comments = $this->task->comments()->orderBy('created_at')->get();

    } else {
        $this->toast('error', 'Unable to delete comment.');
    }
}

public function changeTaskName()
{
    

    $this->task->update(['name' => $this->name]);

    
    $this->loadTaskProperties();

    $this->toast(
            type: 'warning',
            title: 'Mise A jour, Nom Du Task!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
}
public function changeTaskDescription()
{
    $this->task->update(['description' => $this->description]);

    $this->loadTaskProperties();

    $this->toast(
            type: 'warning',
            title: 'Mise A jour, Description!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
}

public function changeStatus()
    {

        $this->task->update(['status_id' => $this->status_id]);

        $this->loadTaskProperties();

        $this->toast(
            type: 'warning',
            title: 'Mise A jour,le Status !',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
    }
    public function changePriority()
    {
        $this->task->update(['priority' => $this->priority]);
        $this->loadTaskProperties();

        $this->toast(
            type: 'warning',
            title: 'Mise A jour, Priorite!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
    }
    public function changeAssignee()
    {
        $this->task->update(['assignee_id' => $this->assignee_id]);

        $this->loadTaskProperties();

        $this->toast(
            type: 'warning',
            title: 'Mise A jour, Assignee !',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
    }
    public function changeProject()
    {
        $this->task->update(['project_id' => $this->project_id]);
        $this->loadTaskProperties();

        $this->toast(
            type: 'warning',
            title: 'Mise A jour, Project!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
    }



}; ?>


<div class="flex flex-col items-center px-4 pt-4 md:flex-row md:items-start md:px-20 md:pt-20">
    <div class="w-full mb-4 md:w-1/2 lg:w-1/4 rounded-xl md:mr-4">

        <x-card title="Task Detail" subtitle=" " separator progress-indicator shadow>
            <x-input label="Task Name" wire:model.defer="name" placeholder="Enter task name" :value="$task->name" wire:keydown.enter="changeTaskName" />
              <br>
                <x-textarea
                label="Task Description"
                wire:model.defer="description"
                placeholder="Enter task description"
                :value="$task->description"
                wire:keydown.enter="changeTaskDescription"
                rows="5"
                inline
            />
         
    <x-icon name="m-bell-alert" />
    Status: <select wire:model="status_id" wire:change="changeStatus" id="status" name="status" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted" >
        @foreach($statuses as $status)
            <option value="{{ $status->id }}">{{ $status->name }}</option>
        @endforeach
    </select>
</strong>
            <br>
            <strong>
             Priority:   <select wire:model="priority" wire:change="changePriority" id="priority" name="priority" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted" >
                    @foreach($priorities as $priorityOption)
                        <option value="{{ $priorityOption['value'] }}">{{ $priorityOption['label'] }}</option>
                    @endforeach
                </select>
            </strong>
            <br>
            <strong>
                <x-icon name="m-users" /> <select wire:model="assignee_id" wire:change="changeAssignee" id="assignee" name="assignee" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted" >
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                    @endforeach
                </select>
            </strong>
            <br>
            <strong>  </strong>
            <strong>
                <x-icon name="m-tag" />
                Project: <select wire:model="project_id" wire:change="changeProject" id="project" name="project" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </strong>
            <div>


            </div>

        </x-card>
    </div>
    <div class="w-full p-6 overflow-auto bg-white shadow-md md:w-2/4 lg:w2/2">
        <div>
            <x-icon name="o-document-arrow-up" wire:click="toggleFiles" />
            {{ $task->files->count() }}
       
      
            <x-icon name="o-chat-bubble-bottom-center-text" wire:click="toggleComments" />
            {{ $task->comments->count() }}
        </div>
        @if($showFiles)

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
                    @if($files->count() > 0)
                        @foreach ($files as $file)
                            <!-- row -->
                            <tr>
                                <td>{{ $file->name }}</td>
                                <td>
                                    <a href="{{ Storage::url($file->file_path) }}" download="{{ $file->name }}">
                                        <x-icon name="m-arrow-down-on-square" />
                                    </a>
                                    <x-button wire:click="fileToDelete({{ $file->id }})" onclick="confirmDelete({{ $file->id }})" icon="o-trash" class="bg-red-500" spinner />

                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-center">No files available.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @endif
     
       
        @if($showComments)
            <hr class="my-6">
            <div class="mt-4">
                <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                <x-textarea
                    label="Leave Comment"
                    wire:model="comment"
                    placeholder=""
                    hint="Max 1000 chars"
                    rows="5"
                    inline
                />
                @error('comment') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            <!-- Add this button to trigger the saveComment method -->
            <x-button wire:click="saveComment" class="mt-4" spinner>
                Save Comment
            </x-button>
        
            <div class="w-full p-6 overflow-auto md:w-3/4 lg:w2/2">
                <hr class="my-6">
                <div class="mb-4 ">
                    <header class="mb-4">Comments</header>
                    @if($comments->count() > 0)
                        @foreach ($comments as $comment)
                            <x-list-item :item="$comments" no-separator no-hover>
                                <x-slot:avatar>
                                    @php
                                        $userName = $comment->user->fullname;
                                        $firstAlphabet = strtoupper(substr($userName, 0, 1));
                                        $lastAlphabet = strtoupper(substr($userName, -1));
                                    @endphp
                                    <x-badge value="{{ $firstAlphabet }}{{ $lastAlphabet }}" class="badge-primary text-uppercase" />                
                                </x-slot:avatar>
                                <x-slot:value>
                                    {{ $comment->user->fullname }}: <x-icon name="s-calendar-days"  /> <strong>{{ $comment->created_at->diffForHumans() }}</strong>-({{$comment->created_at}})
                                </x-slot:value>
                                <x-slot:sub-value>
                                    {{ $comment->comment }}
                                </x-slot:sub-value>
                                <x-slot:actions>
                                    <x-button icon="o-trash" class="text-red-500" wire:click="deleteComment({{ $comment->id }})" spinner />
                                </x-slot:actions>
                            </x-list-item>
                        @endforeach
                    @else
                        <p>No comments available.</p>
                    @endif
                </div>
            </div>
        @endif
        
        <script>
            
            function confirmDelete(fileId) {
                var confirmDelete = confirm("Are you sure you want to delete this file?");
                if (confirmDelete) {
                    Livewire.emit('confirmDelete', fileId); // Emit Livewire event with file ID
                }
            }
            function confirmDelete(commentId) {
                var confirmDelete = confirm("Are you sure you want to delete this comment?");
                if (confirmDelete) {
                    Livewire.emit('confirmDelete', commentId); // Emit Livewire event with comment ID
                }
            }
            
        </script>
