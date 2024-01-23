<?php

use Livewire\Volt\Component;
use App\Models\Project;


new class extends Component {

  

    public function with(): array
    {
        return [
            'projects' => Auth::user()
                ->projects()
                ->orderBy('created_at', 'asc')
                ->get(),
        ];
    }
};?>

<div>
  <a href="{{ route('projects.create') }}">
      <button class="btn glass">Create New Project</button>
  </a>
  <div class="overflow-x-auto">
      <table class="table table-xs">
          <thead>
              <tr>
                  <th>#</th> 
                  <th>Name</th> 
                  <th>Department</th> 
                  <th>Status</th> 
                  <th>Priority</th> 
                  <th>Start Date</th>
                  <th>Due Date</th>
                  <th>Action</th>
              </tr>
          </thead> 
          <tbody>
              @foreach($projects as $project)
                  <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $project->name }}</td>
                      <td>{{ $project->department->name ?? 'N/A' }}</td>
                      <td>{{ $project->statuses->name ?? 'No Status' }}</td>
                      <td>{{ $project->priority }}</td>
                      <td>{{ $project->start_date ?? 'No Start Date' }}</td>
                      <td>{{ $project->due_date ?? 'No End Date' }}</td>
                      <td>
                          <!-- Action buttons like edit, delete -->
                      </td>
                  </tr>
              @endforeach
          </tbody> 
      </table>
  </div>
</div>

