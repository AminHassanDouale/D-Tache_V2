<?php
use Livewire\Volt\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use WithPagination;

    public function tasks()
    {
        return Task::whereHas('assignee', function (Builder $query) {
                $query->where('department_id', Auth::user()->department_id);
            })
            ->where('status_id', '!=', 3) 
            ->with(['project', 'status', 'assignee'])
            ->paginate(10); 
    }

    public function with(): array
    {
        return [
            'tasks' => $this->tasks(), // Access the tasks method here
        ];
    }
};
?>

<div>
    <div class="py-12">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
            <p class="text-center sm:text-left">Bienvenue sur D-Tache, {{ Auth::user()->fullname }}. <span id="timing" class="block float-right sm:inline-block"></span></p>
            <br>
            
                <div class="overflow-hidden shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 text-gray-900 bg-white dark:text-gray-100">
                        @if ($tasks->isEmpty())
                            <p class="text-gray-500">No tasks found.</p>
                        @else
                            @foreach ($tasks as $task)
                                <div class="p-4 mb-4 bg-white rounded-lg shadow ">
                                    <h2 class="text-lg font-semibold">{{ $task->name ?: 'No Task Name' }}</h2>
                                    <p class="text-sm text-gray-600">{{ $task->description ?: 'No Description' }}</p>
                                    <p class="text-sm text-gray-500">Project: {{ optional($task->project)->name ?: 'No Project Name' }}</p>
                                    <p class="text-sm text-gray-500">Status: {{ optional($task->status)->name ?: 'No Status' }}</p>
                                    <p class="text-sm text-gray-500">Assignee: {{ optional($task->assignee)->name ?: 'No Assignee' }}</p>
                                </div>
                            @endforeach
                            {{ $tasks->links() }}
                        @endif
                    </div>
                </div>
                
      
        </div>
    </div>
</div>

<script>
    setInterval(updateTiming, 1000);

    function updateTiming() {
        var now = new Date();
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        var formattedDate = now.toLocaleDateString('fr-FR', options);
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        var timing = hours + ":" + minutes + ":" + seconds + " " + formattedDate;
        document.getElementById('timing').textContent = timing;
    }

    updateTiming();
</script>

