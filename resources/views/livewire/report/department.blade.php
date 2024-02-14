<?php
use App\Models\Task;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\History;
use Illuminate\Support\Facades\Auth;


new class extends Component {
    use WithPagination; 

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $status_id = null;

    #[Url]
    public ?int $user_id = null; // Add user_id property

    #[Url]
    public string $sort = 'status_updated_at';

    public function sorts(): array
    {
        return [
            [
                'id' => 'status_updated_at',
                'name' => 'Last updated'
            ],
            [
                'id' => 'created_at',
                'name' => 'Newest'
            ]
        ];
    }

    public function clear(): void
    {
        $this->reset();
    }

    public function statuses(): Collection
    {
        return Status::all();
    }

    public function histories()
    {
        return History::whereHas('user', function (Builder $query) {
            $query->where('department_id', Auth::user()->department_id);
        })
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function tasks()
    {
        return Task::whereHas('assignee', function (Builder $query) {
            $query->where('department_id', Auth::user()->department_id);
        })
        ->with(['project', 'status', 'assignee'])
        ->when($this->status_id, fn(Builder $q) => $q->where('status_id', $this->status_id))
        ->where('name', 'like', "%$this->search%")
        ->orWhere('description', 'like', "%$this->search%")
        ->latest($this->sort)
        ->paginate(10);
    }

    public function taskCountByStatus($statusId): int
    {
        return Task::where('status_id', $statusId)->count();
    }

    public function delayedTasksCount(): int
    {
        return Task::where('status_id', '!=', 3)
            ->whereDate('due_date', '<', now())
            ->whereHas('assignee', function (Builder $query) {
                $query->where('department_id', Auth::user()->department_id);
            })
            ->count();
    }

    public function with(): array
    {
        return [
            'statuses' => $this->statuses(),
            'tasks' => $this->tasks(),
            'sorts' => $this->sorts(),
            'taskCounts' => [
                'status1' => $this->taskCountByStatus(1),
                'status2' => $this->taskCountByStatus(2),
                'status3' => $this->taskCountByStatus(3),
                'delayed' => $this->delayedTasksCount(),
            ],
            'histories' => $this->histories(),
        ];
    }
}; 

 ?>


   


<div>
    <div>
        <div class="grid grid-cols-4 gap-4 pb-5">
            <x-button label="ToDo" icon="o-arrow-right-on-rectangle" badge="{{ $taskCounts['status1'] }}" responsive />
            <x-button label="UnderProcess" icon="o-signal" badge="{{ $taskCounts['status2'] }}" responsive />
            <x-button label="Terminated" icon="m-check-badge" badge="{{ $taskCounts['status3'] }}" responsive />
            <x-button label="Delayed" icon="s-arrow-trending-down" badge="{{ $taskCounts['delayed'] }}" responsive />
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-1">
            <div class="grid w-full">
                <x-header size="text-inherit" separator progress-indicator>
                    <x-slot:title>
                        <x-input placeholder="Search ..." wire:model.live.debounce="search" icon="o-magnifying-glass">
                            <x-slot:append>
                                <x-select wire:model.live="status_id" :options="$statuses" placeholder="All" placeholder-value="null" class="rounded-r-none bg-primary/5" icon="o-tag" />
                            </x-slot:append>
                        </x-input>
                    </x-slot:title>
                    <x-slot:actions>
                        <x-radio wire:model.live="sort" :options="$sorts" class="px-20 text-sm bg-transparent border-transparent shadow" />
                    </x-slot:actions>
                </x-header>
                <x-card class="p-0 sm:p-2" shadow>
                    @forelse($tasks as $task)
                        <x-list-item :item="$task" value="name" sub-value="description" :link="route('tasks.show', $task)">
                            <x-slot:subValue class="flex items-center gap-3 pt-0.5">
                                <div>
                                    <span class="text-sm text-gray-500">User:</span>
                                    <span>{{ $task->user->username }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500" icon="right-round-arrow">Assignee:</span>
                                    <span class="text-sm text-gray-500" icon="o-arrow-right">{{ $task->assignee->username }} </span>
                                </div>
                                <livewire:timestamp :dateTime="$task->updated_at ?? $task->updated_at" :key="'time-'.$task->id" />
                            </x-slot:subValue>
                            <x-slot:actions>
                            </x-slot:actions>
                        </x-list-item>
                    @empty
                        <x-alert title="Nothing here!" description="Try to remove some filters." icon="o-exclamation-triangle" class="border-none bg-base-100">
                            <x-slot:actions>
                                <x-button label="Clear filters" wire:click="clear" icon="o-x-mark" spinner />
                            </x-slot:actions>
                        </x-alert>
                    @endforelse
                </x-card>
                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
    
    </div>
    