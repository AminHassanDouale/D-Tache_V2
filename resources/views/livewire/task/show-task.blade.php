<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Task;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Models\Comment;
use App\Models\History; 
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;  
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
    public $histories;
    public $statuses;
    public $users;
    public $start_date;
    public $due_date;
    public $confirmingFileDeletion = false;
    public $fileToDeleteId;
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
    public $showHistories = false;
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
        'comment' => 'required|string|max:255', 
    ]);

    $this->task->comments()->create([
        'comment' => $this->comment,
        'user_id' => Auth::id(), 
        'department_id' => Auth::user()->department_id,
        'date' => now(),
    ]);
    $this->logHistory('Comment added', $this->task->id, Task::class);


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

    $this->toast('success', 'Comment added successfully.');
}


public function toggleComments()
{
    $this->showComments = !$this->showComments;
}
public function toggleHistories()
{
    $this->showHistories = !$this->showHistories;
    
    if ($this->showHistories) {
        $this->histories = $this->task->histories()->orderBy('created_at')->get();
    }
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
        $this->logHistory('Name updated', $this->task->id, Task::class);

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
        $this->logHistory('Description updated', $this->task->id, Task::class);

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
        $this->logHistory('Status updated', $this->task->id, Task::class);


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
        $this->logHistory('Priority updated', $this->task->id, Task::class);

    }
    public function updateStartDate($newStartDate)
{
    $startDate = Carbon::parse($newStartDate);
    
    $this->task->update(['start_date' => $startDate]);

    $this->loadTaskProperties();

    $this->toast(
        type: 'warning',
        title: 'Start Date Updated!',
        description: null,
        position: 'toast-bottom toast-end',
        icon: 'o-information-circle',
        css: 'alert-warning',
        timeout: 3000,
        redirectTo: null
    );

    $this->logHistory('Start date updated', $this->task->id, Task::class);
}

public function updateEndDate($newEndDate)
{
    $endDate = Carbon::parse($newEndDate);
    
    $this->task->update(['due_date' => $endDate]);

    $this->loadTaskProperties();

    $this->toast(
        type: 'warning',
        title: 'End Date Updated!',
        description: null,
        position: 'toast-bottom toast-end',
        icon: 'o-information-circle',
        css: 'alert-warning',
        timeout: 3000,
        redirectTo: null
    );

    $this->logHistory('End date updated', $this->task->id, Task::class);
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
        $this->logHistory('Assignee updated', $this->task->id, Task::class);

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
        $this->logHistory('Project updated', $this->task->id, Task::class);

    }

    private function logHistory($action, $modelId, $modelType)
    {
        History::create([
            'action' => $action,
            'model_id' => $modelId,
            'model_type' => $modelType,
            'date' => now(),
            'name' => $this->name, 
            'department_id' => Auth::user()->department_id,
            'user_id' => Auth::id(),
        ]);
    }
    public function confirmFileDeletion($fileId)
{
    $this->fileToDeleteId = $fileId;
    $this->confirmingFileDeletion = true;
}

public function deleteFileConfirmed()
{
    $file = File::find($this->fileToDeleteId);
    
    if ($file) {
        $file->delete();
        $this->toast(
            type: 'danger',
            title: 'Deleted File!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-start',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
        $this->logHistory('File dated', $this->task->id, Task::class);

        $this->files = $this->task->files->sortBy('created_at'); 
    } else {
        $this->toast('error', 'Unable to delete file.');
    }

    $this->confirmingFileDeletion = false;
}

public function deleteFileCancelled()
{
    $this->confirmingFileDeletion = false;
}

}; ?>


<div>
    <main class="grid items-start grid-cols-1 gap-4 px-4 pt-4 md:grid-cols-2 md:px-20 md:pt-20">

        <aside class="">
        
            <div class="p-10 bg-white rounded-lg shadow">
                <div class="flex flex-col items-center gap-1 text-center">
                    <img class="w-32 h-32 p-2 mb-4 bg-white rounded-full shadow" src="https://cdn.dribbble.com/users/2071065/screenshots/5746865/dribble_2-01.png" alt="">
                    <p class="font-semibold">{{ $task->user->fullname }}</p>
                    <div class="flex items-center justify-center text-sm leading-normal text-gray-400">
                    <svg viewBox="0 0 24 24" class="mr-1" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    {{ $task->department->name }}
                    </div>
                   
                </div>
                
            </div>
    
            <div class="p-6 mt-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-600">Status</h3>
                    
                        <select wire:model="status_id" wire:change="changeStatus" id="status" name="status" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted" >
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                   
    
            </div><div class="p-6 mt-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-600">Priorite</h3>
                    
                <select wire:model="priority" wire:change="changePriority" id="priority" name="priority" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted" >
                    @foreach($priorities as $priorityOption)
                        <option value="{{ $priorityOption['value'] }}">{{ $priorityOption['label'] }}</option>
                    @endforeach
                </select>
            </div><div class="p-6 mt-6 bg-white rounded-lg shadow">
                @php
                $config1 = ['altFormat' => 'd/m/Y'];
               @endphp
                 <x-datepicker label="Start Date" wire:model.defer="start_date"  wire:change="updateStartDate($event.target.value)" :config="$config1" />
            </div>
            <div class="p-6 mt-6 bg-white rounded-lg shadow">
                <x-datepicker label="Due Date" wire:model.defer="due_date" wire:change="updateEndDate($event.target.value)" :config="$config1" />
            </div>
            <div class="p-6 mt-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-600">Project</h3>
                <select wire:model="project_id" wire:change="changeProject" id="project" name="project" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="p-6 mt-6 bg-white rounded-lg shadow">
                <h3 class="mb-4 text-sm font-semibold text-gray-600">Assigne</h3>
                <select wire:model="assignee_id" wire:change="changeAssignee" id="assignee" name="assignee" class="block w-full mt-1 border-0 border-solid divide-y divide-blue-200 rounded shadow hover:border-dotted" >
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                    @endforeach
                </select>
            </div>
        </aside>
    
        <article class="">
            <div class="p-4 mb-6 bg-white rounded-lg shadow md:mt-1">
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
            </div>
            <form class="p-4 mb-6 space-y-2 rounded-lg shadow">
                @if($files->count() > 0)
                @foreach ($files as $file)
                    
                
                <div class="flex w-full items-center justify-between rounded-2xl bg-white p-3 shadow-3xl shadow-shadow-500 dark:!bg-navy-700 dark:shadow-none">
                    <div class="flex items-center">
                    <div class="">
                        <img
                        class="h-[83px] w-[83px] rounded-lg"
                        src="https://cdn.dribbble.com/users/1397292/screenshots/16139947/media/8e4fd02616f0e1053030b7c9bc9559b5.png?resize=1600x1200&vertical=center"
                        alt=""
                        />
                    </div>
                    <div class="ml-4">
                        <p class="text-base font-medium text-navy-700 dark:text-white">
                        {{ $file->filename }}
                        </p>
                        @php
                         $sizeInMB = round($file->size / (1024 * 1024), 2); 
                    @endphp
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $sizeInMB }} MB

                            <a
                            class="ml-1 font-medium text-brand-500 hover:text-brand-500 dark:text-white"
                            href=" "
                        >
                        </a>
                        </p>
                    </div>
                    </div>
                    <div class="flex items-center justify-center mr-4 text-gray-600 dark:text-white">
                        <a href="{{ Storage::url($file->file_path) }}" download="{{ $file->name }}">
                            <x-icon name="m-arrow-down-on-square" />
                        </a>
                        <x-button wire:click="confirmFileDeletion({{ $file->id }})" icon="o-trash" class="bg-red-500" spinner />

                    </div>
                </div>
                @endforeach
                @else
                        <tr>
                            <td colspan="2" class="text-center">No files available.</td>
                        </tr>
                    @endif
            </form>
            <div class="mb-6 bg-white rounded-lg shadow">
                <div class="border-b border-gray-100"></div>
                <div class="flex w-full border-t border-gray-100">
                    <div class="flex flex-row mx-5 mt-3 text-xs">
                        <div class="flex items-center mb-2 mr-4 font-normal text-gray-700 rounded-md">Comments:<div class="ml-1 text-gray-400 text-ms"> {{ $task->comments->count() }}</div></div>
                    </div>
                </div>
                @if($comments->count() > 0)
                @foreach ($task->comments as $comment)
                <div class="flex p-4 antialiased text-black">
                    <img class="w-8 h-8 mt-1 mr-2 rounded-full " src="https://cdn.dribbble.com/users/2071065/screenshots/5746865/dribble_2-01.png">
                    <div>
                        <div class="bg-gray-100 rounded-lg px-4 pt-2 pb-2.5">
                            <div class="text-sm font-semibold leading-relaxed">{{ $comment->user->username }}</div>
                            <div class="text-xs leading-snug md:leading-normal">{{ $comment->comment }}.</div>
                        </div>
                        <div class="text-xs  mt-0.5 text-gray-500">{{ $task->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
                @else
                        <p>No comments available.</p>
                @endif

                <div class="relative flex items-center self-center w-full max-w-xl p-4 overflow-hidden text-gray-600 focus-within:text-gray-400">
                    
                    <img class="object-cover w-10 h-10 mr-2 rounded-full shadow cursor-pointer" alt="User avatar" src="https://cdn.dribbble.com/users/2071065/screenshots/5746865/dribble_2-01.png">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-6">
                        <button type="submit" wire:click="saveComment" class="p-1 focus:outline-none focus:shadow-none hover:text-blue-500" spinner>
                        <svg class="w-6 h-6 text-gray-400 transition duration-300 ease-out hover:text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <x-icon name="o-paper-airplane"  />
                        </svg>
                        </button>
                    </span>
                        <input type="search" wire:model="comment" class="w-full py-2 pl-4 pr-10 text-sm placeholder-gray-400 bg-gray-100 border border-transparent appearance-none rounded-tg" style="border-radius: 25px" placeholder="Post a comment..." autocomplete="off">
                </div>
            </div>
    
            
                    
    
        </article>
        
    </main>
  
</div>