<?php
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Task;
use App\Models\Status;
use App\Models\User;
use App\Models\Project;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination;
    use Toast;
    public bool $myModal = false;

    public $name, $description, $assignee_id, $project_id;
    public $statuses;
    public $users;
    public $priorities = [
        ['value' => '1', 'label' => '🚩 Priority 1'],
        ['value' => '2', 'label' => '🟧 Priority 2'],
        ['value' => '3', 'label' => '🟦 Priority 3'],
        ['value' => '4', 'label' => '⬜ Priority 4'],
    ];

    // Add these properties
    public $status_id;
    public $priority;
    public $start_date;
    public $due_date;
    public $projects;
    public function mount()
    {
        $this->statuses = Status::all();
        $this->users = User::all();
        $this->projects = Project::all();
    }

    public function saveTask()
    {
        $validatedData = $this->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'assignee_id' => 'required|exists:users,id',
            'project_id' => 'required',
        ]);

        $task = Task::create([
            'name' => $this->name,
            'description' => $this->description,
            'assignee_id' => $this->assignee_id,
            'project_id' => $this->project_id,
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

        $this->resetForm();

      
    }

    private function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->assignee_id = null;
        $this->project_id = null;
    }

    public function changeStatus($taskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->status_id == 3) {
            $task->update(['status_id' => 2]);
        } else {
            $task->update(['status_id' => 3]);
        }

        $this->toast(
            type: 'success',
            title: 'Status Updated!',
            description: 'Task status has been updated.',
            position: 'toast-top toast-start',
            icon: 'o-information-circle',
            css: 'alert-success',
            timeout: 3000,
            redirectTo: null                    // optional (uri)


        );
    }

    public function with(): array
    {
        return [
            'tasks' => Task::orderBy('created_at', 'Desc')->paginate(10),
        ];
    }
};
?>



<div>
        <div class="flex flex-col items-center justify-between mb-2 md:flex-row">
            <input type="text" placeholder="Search ..." 
                   class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   />
                   <x-button label="Add New" class="btn-primary" wire:click="$toggle('myModal')" />
    
                </div>
    <x-tabs selected="users-tab">
        <x-tab name="users-tab" label="Grid" icon="o-squares-2x2">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">
        @foreach ($tasks as $task)
        
        <x-card title="{{ $task->name ?? '' }}" subtitle="{{ $task->status->name }}" shadow separator>
            {!!\Illuminate\Support\Str::limit($task->description, 50)!!}...
        </x-card>
        @endforeach
            </div>
            {{ $tasks->links() }}
        </div>
       
    </x-tab>
     
        <x-tab name="tricks-tab" label="Lists" icon="o-list-bullet">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase border-b-2 border-gray-200">
                           #
                        </th>
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
                         <th class="px-5 py-3 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase border-b-2 border-gray-200">
                            Action
                        </th>
                        
        
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                    <tr>
                        <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                            <input type="checkbox" wire:model="selectedTasks.{{ $task->id }}"class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" wire:change="changeStatus({{ $task->id }})" {{ $task->status_id == 3 ? 'checked' : '' }}>
                        </td>
                        <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                            {{ $task->name }}
                            <br>
                            <span class="italic line-clamp-3">{{ $task->project->name ?? 'No Project' }}</span>
                        </td>
                        <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                            {{ optional($task->assignee)->username ?? 'Not Assigned' }}
        
                        </td>
                        <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                            <span class="inline-flex px-2 text-center text-xs font-semibold leading-5 
                                {{ $task->status_id == 1 ? 'bg-blue-100 text-blue-800' : 
                                   ($task->status_id == 2 ? 'bg-yellow-100 text-yellow-800' : 
                                   ($task->status_id == 3 ? 'bg-green-100 text-green-800' : 
                                   'bg-gray-100 text-gray-800')) }} rounded-full">
                                {{ optional($task->status)->name ?? 'No Status' }}
                            </span>
                        </td>
                       
                        
                        
                        <td class="px-5 py-5 text-sm bg-white border-b border-gray-200">
                           
                    
                            <span class="">
                                {{ optional($task->start_date)->format('d/m') }} - {{ optional($task->due_date)->format('d/m') }}
                            </span>
                        </td>
                    
                        
                        <td class="bg-white">
                            <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('tasks.edit', $task) }}">Edit</a>
                            <a class="text-green-600 hover:text-indigo-900" href="{{ route('tasks.show', $task) }}">Show</a>

                        </td>
                    </tr>
                </div>
                    @endforeach
                </tbody>
            </table>


<x-modal wire:model="myModal" title="Create Task"  separator>

    <form wire:submit.prevent="saveTask">
        <x-errors title="Oops!" description="Please, fix the errors below." />
        <x-input label="Task Name" wire:model.live.debounce="name" placeholder="Enter task name" />
        <x-textarea label="Description" wire:model.defer="description" placeholder="Enter task description" />

        <label for="priority">Assignee</label>
        <br>
<select id="priority" wire:model="assignee_id" class="px-20 border border-blue-500 rounded">
    @foreach ($users as $user)
        <option value="{{ $user->id}}">{{ $user->username }}</option>
    @endforeach
</select>
        <x-select label="Projects" icon="o-key" :options="$projects" wire:model="project_id" placeholder="Project" />
        <x-button label="Cancel" @click="$wire.myPersistentModal = false" />
        <x-button label="Create" wire:click="saveTask" icon="o-paper-airplane" class="btn-primary" spinner />
        <x-hr target="saveTask" />
</form>
</x-modal>
</div>
</div>
</div>
{{ $tasks->links() }}
</div>
</x-tab>
</x-tabs>
 </div>
    
    



