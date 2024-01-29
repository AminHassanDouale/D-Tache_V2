<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Task;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Carbon\Carbon;
use Mary\Traits\Toast;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Mail\TaskCreatedMail;
use Illuminate\Support\Facades\Mail;




new class extends Component {
    use WithPagination;
    use Toast;
     #[Url]
     public string $search = '';
     protected $queryString = ['search' => ['except' => '']];


    public bool $myPersistentModal = false;
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
        $this->headers = [
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-1'],
            ['key' => 'status_id', 'label' => 'Status'],
        ];
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    

    public function saveTask()
    {
        // Validation logic
        $validatedData = $this->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'status_id' => '',
            'tags' => '',
            'priority' => 'nullable|in:1,2,3,4',
            'notification' => 'boolean',
            'assignee_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
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
        $this->toast(
            type: 'success',
            title: 'ce fait!',
            description: null,                  // optional (text)
            position: 'toast-top toast-end',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-success',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
        if ($task->assignee_id) {
        $assignee = User::find($task->assignee_id);
        $creator = User::find($task->user_id); // Assuming user_id is the creator's ID
        $project = Project::find($task->project_id);

        // Prepare the email data
        $emailData = [
            'task' => $task,
            'assignee' => $assignee,
            'creator' => $creator,
            'project' => $project,
        ];

        // Send the email
        Mail::to($assignee->email)->send(new TaskCreatedMail($emailData));
    }
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


public function with(): array
    {
        return [
            'tasks' => Task::orderBy('created_at', 'asc')->paginate(10),
        ];
    }
    
}; ?>



<div>
    
 
<div class="container p-4 mx-auto mt-10 bg-white rounded-lg shadow-md">
    <div class="flex flex-col items-center justify-between mb-6 md:flex-row">
        <input type="text" placeholder="Search ..." 
               class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
               />
        <button class="px-4 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-600" wire:click="$set('myPersistentModal', true)">Add New</button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
        <thead>
            <tr>
                <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase border-b-2 border-gray-200">
                    Name
                </th>
                <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase border-b-2 border-gray-200">
                    Assigned
                </th>
                <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase border-b-2 border-gray-200">
                    Status
                </th>
                <th class="px-5 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase border-b-2 border-gray-200">
                    Date
                </th>
                <th class="px-5 py-3 text-xs font-semibold tracking-wider text-center text-gray-600 uppercase border-b-2 border-gray-200">
                    Remaining 
                </th> <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase border-b-2 border-gray-200">
                    Action
                </th>
                

            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
            <tr>
                <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                    {{ $task->name }}
                </td>
                <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                    {{ optional($task->assignee)->name ?? 'Not Assigned' }}

                </td>
                <td class="px-5 py-5 text-sm border-b border-gray-200">
                    <span class="inline-flex px-2 text-xs font-semibold leading-5 
                        {{ $task->status_id == 1 ? 'bg-blue-100 text-blue-800' : 
                           ($task->status_id == 2 ? 'bg-yellow-100 text-yellow-800' : 
                           ($task->status_id == 3 ? 'bg-green-100 text-green-800' : 
                           'bg-gray-100 text-gray-800')) }} rounded-full">
                        {{ optional($task->status)->name ?? 'No Status' }}
                    </span>
                </td>
               
                
                
                <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                    @php
                        $now = \Carbon\Carbon::now();
                        $startDateExpired = $now->greaterThanOrEqualTo($task->start_date);
                        $endDateExpired = $now->greaterThanOrEqualTo($task->end_date);
                        $isExpired = $startDateExpired && $endDateExpired && $task->status_id != 3;
                    @endphp
            
                    <span class="{{ $isExpired ? 'blink-red' : '' }}">
                        {{ optional($task->start_date)->format('d/m H:i A') }} - {{ optional($task->end_date)->format('d/m H:i A') }}
                        @if($isExpired)
                            <span> (Expired)</span>
                        @endif
                    </span>
                </td>
            
                <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                    @php
                    $now = \Carbon\Carbon::now();
                    $endDate = $task->end_date;
                    $isExpired = $now->greaterThanOrEqualTo($endDate) && $task->status_id != 3;
                    $timeRemainingText = $isExpired ? 'EXPIRED' : $now->diffForHumans($endDate, true);
                @endphp
        
                <span class="{{ $isExpired ? 'blink-red' : '' }}">
                    {{ $timeRemainingText }}
                </span>
                </td>
                <td>
                    <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('tasks.edit', $task) }}">Edit</a>
                    <a class="text-green-600 hover:text-indigo-900" href="{{ route('tasks.show', $task) }}">Show</a>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    {{ $tasks->links() }}
</div>

 
<x-modal wire:model="myPersistentModal" title="Persistent" separator persistent>

    <form wire:submit.prevent="saveTask">
        <x-errors title="Oops!" description="Please, fix the errors below." />
        <x-input label="Task Name" wire:model.live.debounce="name" placeholder="Enter task name" />
        <x-textarea label="Description" wire:model.defer="description" placeholder="Enter task description" />
        <x-select label="Etiquettes" icon="o-user" :options="$users" wire:model="assignee_id" placeholder="Select a user" />
        <x-select label="Projects" icon="o-key" :options="$projects" wire:model="project_id" placeholder="Project" />
        <x-button label="Cancel" @click="$wire.myPersistentModal = false" />
        <x-button label="Create" wire:click="saveTask" icon="o-paper-airplane" class="btn-primary" spinner />
        <x-hr target="saveTask" />
</form>
</x-modal>
    
    <div>
    
   
    </div>
