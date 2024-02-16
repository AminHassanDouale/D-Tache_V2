<?php
use Livewire\Volt\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

new class extends Component {
    use WithPagination;

    public function tasks()
    {
        return  Task::where(function($query) {
                $query->where('user_id', Auth::user()->id)
                      ->orWhere('assignee_id', Auth::user()->id);
            })
            ->where('status_id', '!=', 3) 
            ->orderBy('created_at', 'desc')
            ->paginate(8);
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
            <x-tabs selected="users-tab">
                <x-tab name="users-tab" label="Tasks" icon="s-bars-3-center-left">
                   
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @if ($tasks->isEmpty())
                            <p class="text-gray-500">No tasks found.</p>
                        @else
                            @foreach ($tasks as $task)
                                <!-- Pricing Card 1 -->
                                <div class="overflow-hidden transition-transform transform bg-white rounded-lg shadow-lg hover:scale-105">
                                    <div class="p-1 bg-blue-200">
                                    </div>
                                    <div class="p-8">
                                        <h2 class="mb-4 text-3xl font-bold text-gray-800"></h2>
                                        <p class="mb-6 text-gray-600"> <strong> {{ $task->name }} </strong></p>
                                        <ul class="mb-6 text-sm text-gray-600">
                                            <li class="flex items-center mb-2">
                                                Status: 
                                                <span class="{{ $task->status_id == 1 ? 'text-blue-500' : ($task->status_id == 2 ? 'text-orange-500' : 'text-green-500') }}">
                                                    {{ $task->status_id ? $task->status->name : ''}}
                                                </span>
                                            </li><li class="flex items-center mb-2">
                                                {{ $task->user->username }}
                                            </li><li class="flex items-center mb-2">
                                                {{ $task->assignee_id == Auth::user()->id ? 'Vous-même' : ($task->assignee ? $task->assignee->name : '') }}                                                 
                                            </li>
                                            
                                            
                                            <li class="flex items-center">
                                                
                                                <x-icon name="m-chat-bubble-bottom-center-text" /> {{ $task->comments->count() }}
                                                <x-icon name="m-folder" /> {{ $task->files->count() }}
                                            </li>
                                            <li class="flex items-center mb-2">
                                                Due Date: 
                                                <span class="due-date {{ Carbon::now()->gt($task->due_date) ? 'text-red-500' : '' }}">
                                                    {{ $task->due_date }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="p-4"><a href="{{ route('tasks.show', $task) }}" >
                                        <button class="w-full px-4 py-2 text-white bg-blue-500 rounded-full hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue active:bg-blue-800">
                                            Voir
                                        </button>
                                    </a>
                                    </div>
                                </div>
                            @endforeach
                            {{ $tasks->links() }}
                        @endif
                    </div>
                </x-tab>
                <x-tab name="tricks-tab" label="Tricks" icon="o-sparkles">
                    <div>Tricks</div>
                </x-tab>
                <x-tab name="musics-tab" label="Musics" icon="o-musical-note">
                    <div>Musics</div>
                </x-tab>
            </x-tabs>
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
<script>
    setInterval(function() {
        var dueDates = document.querySelectorAll('.due-date');
        dueDates.forEach(function(element) {
            var dueDate = new Date(element.textContent);
            if (dueDate < Date.now()) {
                element.classList.add('flash-alert');
            } else {
                element.classList.remove('flash-alert');
            }
        });
    }, 1000); // Flash every 1 second
</script>

