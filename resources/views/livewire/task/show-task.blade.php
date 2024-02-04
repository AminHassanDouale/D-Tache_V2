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
    public $files = [];
    
    public $comments;
    public $comment;
    public $statuses;
    public $users;
    public $tags = [];
    public $fileToDelete;
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


}; ?>


<div class="flex flex-col items-center px-4 pt-4 md:flex-row md:items-start md:px-20 md:pt-20">
    <div class="w-full mb-4 md:w-1/2 lg:w-1/4 rounded-xl md:mr-4">

        <x-card title="Task Name: {{ $task->name }}" subtitle=" Task Description: {!! $task->description !!}" separator progress-indicator shadow>
           CreatedBy: <code>{{ $task->user->fullname }}</code> <br>
            <strong>
                <x-icon name="m-bell-alert" />
                @if($task->status_id == 3)
                    <span class="text-green-600"> {{ $task->status ? $task->status->name : ''}}</span> <!-- Green dot -->
                @elseif($task->status_id == 2)
                    <span class="text-yellow-200">{{ $task->status ? $task->status->name : ''}}</span> <!-- Warning dot -->
                @elseif($task->status_id == 1)
                    <span class="text-blue-300">{{ $task->status ? $task->status->name : ''}}</span> <!-- Blue dot -->
                @endif
            </strong>
            <br>
            <strong>
                @foreach($priorities as $priorityOption)
                    @if($priorityOption['value'] == $task->priority)
                        <span class="text-blue-300">{{ $priorityOption['label'] }}</span>
                    @endif
                @endforeach
            </strong>
            <br>
            <strong>
                <x-icon name="m-users" /> {{ $task->assignee_id ? $task->assignee->fullname : ''}}
            </strong>
            <br>
            <strong>  </strong>
            <strong>
                <x-icon name="m-tag" />
                Project: {{ $task->project_id ? $task->project->name : ''}}
            </strong>
        </x-card>
    </div>

    <div class="w-full p-6 bg-white shadow-md md:w-2/4 lg:w2/2 ">
        <header class="mb-4">File</header>
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

        <hr class="my-6">
        <div class="mt-4">
            <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
            <x-textarea
            label="Leave Comment"
            wire:model="comment"
            placeholder=""
            hint="Max 1000 chars"
            rows="5"
            inline />
            @error('comment') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>
        <!-- Add this button to trigger the saveComment method -->
<x-button wire:click="saveComment" class="mt-4" spinner>
    Save Comment
</x-button>
<div class="w-full p-6 md:w-3/4 lg:w2/2">
    <hr class="my-6">

    <div class="mb-4">
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
</div>

<script>
    function confirmDelete(fileId) {
        var confirmDelete = confirm("Are you sure you want to delete this file?");
        if (confirmDelete) {
            Livewire.emit('confirmDelete', fileId); // Emit Livewire event with file ID
        }
    }
    
</script>
<script>
    function confirmDelete(commentId) {
        var confirmDelete = confirm("Are you sure you want to delete this comment?");
        if (confirmDelete) {
            Livewire.emit('confirmDelete', commentId); // Emit Livewire event with comment ID
        }
    }
</script>


