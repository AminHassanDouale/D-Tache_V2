<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Task;
use App\Models\Project;
use App\Models\Member;
use App\Models\File;
use App\Models\Status;
use App\Models\User;
use App\Models\Department;
use App\Mail\Project\MemberAddedToProjectMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;  // Required for file uploads
use Carbon\Carbon;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] class extends Component {
    use Toast;
    use WithFileUploads;

    public Project $project;
    public $files = [];
    public $name, $description,$priority, $status_id, $notification = false, $assignee_id, $start_date, $due_date, $user_id, $department_id, $project_id;
    public $statuses;
    public $privacy = '';
    public $users;
    public $tags = [];
    public $selectedUser = [];
    public $projects;
    public $priorities = [
        ['value' => '1', 'label' => '🚩 Priority 1'], // Red flag emoji
        ['value' => '2', 'label' => '🟧 Priority 2'], // Orange square emoji
        ['value' => '3', 'label' => '🟦 Priority 3'], // Blue square emoji
        ['value' => '4', 'label' => '⬜ Priority 4'], // White square emoji
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->loadNonMemberUsers();
        $this->loadProjectProperties();
    }
    private function loadProjectProperties()
{
    $this->name = $this->project->name;
    $this->description = $this->project->description;
    $this->priority = $this->project->priority;
    $this->status_id = $this->project->status_id;
    $this->notification = $this->project->notification;
    $this->start_date = $this->project->start_date ? $this->project->start_date->format('Y-m-d\TH:i') : null;
    $this->due_date = $this->project->due_date ? $this->project->due_date->format('Y-m-d\TH:i') : null;
    $this->tags = $this->project->tags; // Assuming 'tags' is an array
    $this->statuses = Status::all();
    $this->users = User::all();

    // Add any other properties that you need to load from the task
}
public function saveProject()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'notification' => '',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
            'tags' => 'array',
        ]);
   
        
        $this->project->update($validated);

        $this->toast(
            type: 'warning',
            title: 'Mise A jour!',
            description: null,                  // optional (text)
            position: 'toast-bottom toast-start',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );

       
}
public function handleFileUploads() {
        foreach ($this->files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $file->storeAs('project_files', $fileName, 'public');

            File::create([
                'model_id' => $this->project->id,
                'model_type' => Project::class,
                'filename' => $fileName,
                'file_path' => $filePath,
                'name' => $fileName,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'user_id' => Auth::id(),
                'department_id' => Auth::user()->department_id, 
            ]);
           


            $this->toast(
            type: 'warning',
            title: 'File Ajouter!',
            description: null,                  // optional (text)
            position: 'toast-top toast-start',    // optional (daisyUI classes)
            icon: 'o-information-circle',       // Optional (any icon)
            css: 'alert-warning',                  // Optional (daisyUI classes)
            timeout: 3000,                      // optional (ms)
            redirectTo: null                    // optional (uri)
        );
        }

    }
    public function deleteFile($fileId)
{
    $file = File::find($fileId);

    if ($file && $file->user_id === auth()->id()) {
        
        $file->delete(); 
        $this->toast('success', 'Files deleted successfully.');

    }
}
public function addedMember()
{
    if ($this->privacy == 2) {

    $this->validate([
        'selectedUser' => 'required|exists:users,id' // Make sure a user is selected and exists
    ]);

    // Check if the user is already a member
    if(!$this->project->members()->where('user_id', $this->selectedUser)->exists()) {
        Member::create([
            'model_id' => $this->project->id,
            'model_type' => Project::class,
            'user_id' => $this->selectedUser,
            'department_id' => Auth::user()->department_id,
            'date' => Carbon::now(), // Use Carbon::now() or similar
        ]);
        $this->project->load('members.user');
        $this->loadNonMemberUsers(); // Refresh non-member users

            $this->toast('success', 'Member added successfully.');
            $user = User::findOrFail($this->selectedUser);
        Mail::to($user->email)->send(new MemberAddedToProjectMail($this->project, $user));
        
            $this->selectedUser = null; // Reset after adding
        } else {
            $this->toast('error', 'This user is already a member of the project.');
        }
    } else {
        $this->toast('error', 'Members cannot be added to a private project.');
    }
}

public function deleteMember($memberId)
{
    $member = Member::find($memberId);

        
        $member->delete(); 
        $this->toast('error', 'A Member Has Been Removed.');

}
private function loadNonMemberUsers() {
        $existingMemberIds = $this->project->members->pluck('user_id')->toArray();
        $this->users = User::whereNotIn('id', $existingMemberIds)->orderBy('name', 'ASC')->get();
    }
    
}; ?>

<div>
    <x-header title="Project Edit" separator progress-indicator />

    <div class="flex justify-center pt-20">
        <div class="w-2/4 p-6 bg-white shadow-md rounded-xl">
        
        
            <form wire:submit.prevent="saveProject">
                <!-- Other task fields -->
                <x-errors title="Oops!" description="Please, fix the errors below." />
        
                <x-input label="Project Name" wire:model.defer="name" placeholder="Enter Project name" />
                <x-textarea label="Description" wire:model.defer="description" placeholder="Enter task description" />
        
                <label for="priority">Priority</label>
        <select id="priority" wire:model="priority">
            @foreach ($priorities as $priority)
                <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
            @endforeach
        </select>
        
        
                <select label="Status" wire:model="status_id">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
        
               
                <x-datetime label="Start Date" wire:model.defer="start_date" type="datetime-local" />
                <x-datetime label="End Date" wire:model.defer="due_date" type="datetime-local" />
                <x-tags label="Tags" wire:model="tags" hint="Hit enter to create a new tag" />
                <x-button label="Cancel" link="/" class="mt-10"/>
                <x-button label="Save Changes" spinner="saveTask" type="submit" icon="o-paper-airplane" class="btn-primary" />
            </form>
        </div>
      
        
        <div class="mt-4">

            <div>
                <input type="radio" wire:model="privacy" class="radio" value="1" {{ $project->privacy == 1 ? 'checked' : '' }} onclick="toggleWelcomeMessage(false)"/> Private 
                <input type="radio" wire:model="privacy" class="radio" value="2" {{ $project->privacy == 2 ? 'checked' : '' }} onclick="toggleWelcomeMessage(true)"/> Public
                <div>
                    <div class="mt-4">
                        <label for="memberSelect" class="block text-sm font-medium text-gray-700">Add Member</label>
                        <select id="memberSelect" wire:model="selectedUser" class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select Member</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedUser') <span class="text-red-500 error">{{ $message }}</span> @enderror
    
                        <!-- Trigger for adding member -->
                        <x-button wire:click.prevent="addedMember" class="mt-2">Add Member</x-button>
                    </div>
                </div>
                
                @if ($project->privacy == 2)
                    <div id="welcomeMessage" class="flex items-center mt-2 space-x-3">
                        @forelse ($project->members as $member)
                            <div class="flex overflow-hidden">
                                <ul>
                                    <li> {{ $member->user->name }} - 
                                        <x-button icon="o-trash" wire:click="deleteMember({{ $member->id }})" spinner class="btn-sm"></x-button>
                                    </li>
                                </ul>
                            </div>
                        @empty
                            <span class="text-sm italic text-gray-500">No members assigned</span>
                        @endforelse
                    </div>
                @endif
            </div>
            
            <form wire:submit.prevent="saveProject">
        
                <div>
                    <x-file label="Files" wire:model="files" multiple />
                    @error('files.*') <span class="error">{{ $message }}</span> @enderror
                </div>
                    <x-button  wire:click="handleFileUploads"  external icon="o-link" tooltip="Upload File">Upload </x-button>
            
                <!-- You can add a button to specifically trigger file uploads -->
               
            
            </form>
            <h3 class="text-lg font-medium text-gray-900">Files</h3>
            @if($project->files->isNotEmpty())
                <ul class="mt-2">
                    @foreach($project->files as $file)
                        <li class="flex items-center justify-between py-2">
                            <span class="flex-grow">{{ $file->name }}</span>
                            
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2 text-sm text-gray-600">No files uploaded.</p>
            @endif
        </div>
        
    </div>
        </div>
        <script>
            function toggleWelcomeMessage(isPublic) {
                var welcomeMessage = document.getElementById('welcomeMessage');
                welcomeMessage.style.display = isPublic ? 'block' : 'none';
            }
            </script>