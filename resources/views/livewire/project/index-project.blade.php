<?php

use Livewire\Volt\Component;
use App\Models\Project;


new class extends Component {

    public bool $myModal = false;


    public function with(): array
    {
        return [
            'projects' => Auth::user()
                ->projects()
                ->with('statuses') 
                ->with('members.user') 
                ->withCount('tasks') 
                ->with(['tasks' => function ($query) {
                $query->where('status_id', '!=', 3); 
            }])
            ->withCount(['tasks as incomplete_tasks_count' => function ($query) {
                $query->where('status_id', '!=', 3);
            }, 'tasks as total_tasks_count']) 
                ->orderBy('created_at', 'asc')
                ->paginate(10),
        ];
    }
};?>

<div>

        <div class="flex flex-col items-center justify-between mb-6 md:flex-row">
            <input type="text" placeholder="Search ..." 
                   class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   />
                   <a href="{{ route('projects.create') }}" class="px-4 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-600">
           Add new
        </a>
        
        </div>
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Members</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tasks</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($projects as $index => $project)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $project->name }}</td>
                        <td class="px-5 py-5 text-sm border-b border-gray-200">
                            <span class="inline-flex px-2 text-xs font-semibold leading-5 
                                {{ $project->status_id == 1 ? 'bg-blue-100 text-blue-800' : 
                                   ($project->status_id == 2 ? 'bg-yellow-100 text-yellow-800' : 
                                   ($project->status_id == 3 ? 'bg-green-100 text-green-800' : 
                                   'bg-gray-100 text-gray-800')) }} rounded-full">
                                {{ optional($project->statuses)->name ?? 'No Status' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-3">
                                @forelse ($project->members as $member)
                                    @php
                                        $initials = $member->user->name;
                                        $initials = $initials[0] . (strlen($initials) > 1 ? $initials[strlen($initials) - 1] : '');
                                        $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-indigo-500', 'bg-purple-500', 'bg-pink-500'];
                                        $color = $colors[array_rand($colors)];
                                    @endphp
                                    <div class="flex -space-x-1 overflow-hidden">
                                        <span class="flex items-center justify-center inline-block w-8 h-8 text-sm font-semibold text-white {{ $color }} rounded-full hover:shadow-md" 
                                              title="{{ $member->user->name }}">
                                            {{ strtoupper($initials) }}
                                        </span>
                                    </div>
                                @empty
                                    <span class="text-sm italic text-gray-500">No members assigned</span>
                                @endforelse
                            </div>
                        </td>
                        
                        <td class="py-4 whitespace-nowrap">@php
                            $now = \Carbon\Carbon::now();
                            $startDateExpired = $now->greaterThanOrEqualTo($project->start_date);
                            $endDateExpired = $now->greaterThanOrEqualTo($project->end_date);
                            $isExpired = $startDateExpired && $endDateExpired && $project->status_id != 3;
                        @endphp
                        <span class="{{ $isExpired ? 'blink-red' : '' }}">
                            {{ optional($project->start_date)->format('d/m') }} - {{ optional($project->due_date)->format('d/m') }}
                            @if($isExpired)
                                <span> (Expired)</span>
                            @endif
                        </span></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($project->total_tasks_count > 0)
                                    @php
                                        $completionPercentage = 100 - ($project->incomplete_tasks_count / $project->total_tasks_count) * 100;
                                        $progressBarColor = $completionPercentage < 100 ? 'bg-yellow-500' : 'bg-green-500'; 
                                        $isCompleted = $completionPercentage >= 100;

                                    @endphp
                                    <div class="w-full bg-gray-200 rounded-full">
                                        <div class="{{ $progressBarColor }} text-xs font-medium text-white text-center p-0.5 leading-none rounded-l-full" style="width: {{ $completionPercentage }}%">
                                            {{ number_format($completionPercentage) }}%
                                        </div>
                                    </div>
                                    @if(!$isCompleted)
                                    <x-progress class="progress-primary h-0.5" indeterminate />
                                    
                                @endif

                                @else
                                    No tasks
                                @endif
                            </td>
                            
                            
            
                        
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $project->tasks_count }}</td>
                        <td> <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('projects.edit', $project) }}">Edit</a>
                            <a class="text-green-600 hover:text-indigo-900" href="">Show</a>
        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination (if applicable) -->
        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    </div>


</div>
 
  