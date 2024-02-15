<?php
use App\Models\Task;
use App\Models\Status;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\History;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Models\User;


new class extends Component {
    use WithPagination;



   

   
    public function histories()
    {
        return History::whereHas('user', function (Builder $query) {
            $query->where('user_id', Auth::user()->id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    }

    public function tasks()
{
    return Task::whereHas('user', function (Builder $query) {
            $query->where('user_id', Auth::user()->id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);
}
public function with(): array
    {
        $user = Auth::user(); 


$userFiles = $user->files()->orderBy('created_at', 'desc')->paginate(10);

        return [
            
            'tasks' => $this->tasks(),
            'histories' => $this->histories(),
            'userFiles' => $userFiles,

            
          
        ];
    }
}; ?>

<div>
    <div class="flex flex-col items-center justify-center ">
        <div class="relative flex flex-col items-center rounded-[10px] border-[1px] border-gray-200 w-[400px] mx-auto p-4 bg-white bg-clip-border shadow-md shadow-[#F3F3F3] dark:border-[#ffffff33] dark:!bg-navy-800 dark:text-white dark:shadow-none">
            <div class="relative flex justify-center w-full h-32 bg-cover rounded-xl" >
                <img src='https://horizon-tailwind-react-git-tailwind-components-horizon-ui.vercel.app/static/media/banner.ef572d78f29b0fee0a09.png' class="absolute flex justify-center w-full h-32 bg-cover rounded-xl"> 
                <div class="absolute -bottom-12 flex h-[87px] w-[87px] items-center justify-center rounded-full border-[4px] border-white bg-pink-400 dark:!border-navy-700">
                    <img class="w-full h-full rounded-full" src='https://cdn.dribbble.com/users/2071065/screenshots/5746865/dribble_2-01.png' alt="" />
                </div>
            </div> 
            <div class="flex flex-col items-center mt-16">
                <h4 class="text-xl font-bold text-navy-700 dark:text-white">
                {{ Auth::user()->fullname }}
                </h4>
                <p class="text-base font-normal text-gray-600">{{ Auth::user()->department->name }}</p>
            </div> 
            <div class="mt-6 mb-3 flex gap-14 md:!gap-14">
                <div class="flex flex-col items-center justify-center">
                <p class="text-2xl font-bold text-navy-700 dark:text-white">{{ Auth::user()->projects->count() }}</p>
                <p class="text-sm font-normal text-gray-600">Poject</p>
                </div>
                <div class="flex flex-col items-center justify-center">
                <p class="text-2xl font-bold text-navy-700 dark:text-white">
                    {{ Auth::user()->tasks->count() }}
                </p>
                <p class="text-sm font-normal text-gray-600">Tasks</p>
                </div>
                <div class="flex flex-col items-center justify-center">
                <p class="text-2xl font-bold text-navy-700 dark:text-white">
                    {{ Auth::user()->files->count() }}
                </p>
                <p class="text-sm font-normal text-gray-600">File</p>
                </div>
            </div>
        </div>  
    </div>
    <hr>
    <div class="flex items-center justify-center h-full pt-2">

    <x-tabs selected="users-tab">
        <x-tab name="users-tab" label="Project" icon="s-shield-check">
            
        </x-tab>
        <x-tab name="tricks-tab" label="Task" icon="o-sparkles">
            <x-card class="p-0 sm:p-2" shadow>
                @forelse($tasks as $task)
                    <x-list-item :item="$task" value="name" sub-value="description" :link="route('tasks.show', $task)">
                        <x-slot:subValue class="flex items-center gap-3 pt-0.5">
                            <div>
                                <span class="text-sm text-gray-500">User:</span>
                                <span>{{ $task->user->username }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Status:</span>
                                <span>{{ $task->status ? $task->status->name : '' }}</span> <!-- Display task status name -->
                            </div>
                            <div>
                                <span class="text-sm text-gray-500" icon="right-round-arrow">Assignee:</span>
                                <span class="text-sm text-gray-500" icon="o-arrow-right">{{ $task->assignee_id ? $task->assignee->username : '' }} </span>
                            </div>
                            <livewire:timestamp :dateTime="$task->updated_at ?? $task->updated_at" :key="'time-'.$task->id" />
                        </x-slot:subValue>
                        <x-slot:actions>
                        </x-slot:actions>
                    </x-list-item>
                @empty
                    <x-alert title="Nothing here!" description="Try to remove some filters." icon="o-exclamation-triangle" class="border-none bg-base-100">
                        
                    </x-alert>
                @endforelse
            </x-card>
            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        </x-tab>
        <x-tab name="musics-tab" label="histories" icon="o-rectangle-group">
            <x-card class="p-0 sm:p-2" shadow>
                @forelse($histories as $historie)
                 <div class="space-y-6 border-l-2 border-dashed">
                    <div class="relative w-full">
                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="absolute -top-0.5 z-10 -ml-3.5 h-7 w-7 rounded-full text-blue-500">
          <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>
           <div class="ml-6">
          <h4 class="font-bold text-blue-500">{{ $historie->name }}.</h4>
          <p class="max-w-screen-sm mt-2 text-sm text-gray-500"> The actions, {{ $historie->action }}</p>
          <span class="block mt-1 text-sm font-semibold text-blue-500">{{ $historie->created_at->diffForHumans() }}</span>
        </div>
      </div>
    </div>
    @empty
    <x-alert title="Nothing here!" description="Try to remove some filters." icon="o-exclamation-triangle" class="border-none bg-base-100">
       
    </x-alert>
@endforelse
            </x-card>
            <div class="mt-4">
                {{ $histories->links() }}
            </div>
        </x-tab>
        <x-tab name="files" label="files" icon="o-folder">
            <div class="mt-4">
                @if($userFiles->count() > 0)
                    @foreach($userFiles as $file)
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
    
                        </div>
                    </div>
                    @endforeach
                @else
                    <x-alert title="No files!" description="Try to upload files or create tasks with files." icon="o-exclamation-triangle" class="border-none bg-base-100"></x-alert>
                @endif
            </div>
        </x-tab>
    </x-tabs>
</div>
</div>