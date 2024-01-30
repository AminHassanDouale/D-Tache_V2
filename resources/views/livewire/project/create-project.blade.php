<?php

use Livewire\Volt\Component;
use App\Models\Member;
use App\Models\Project;
use App\Models\Status;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;  // Required for file uploads
use Carbon\Carbon;
use Mary\Traits\Toast;



new class extends Component {
    use WithFileUploads;
    use Toast;

public $files = [];
    public $name, $description, $status_id, $amount, $priority, $start_date, $due_date, $remark, $user_id, $department_id, $project_id;
    public $statuses; 
    public $users;
    public $tags = [];


    public $selectedUsers = [];
    public $privacy = 1;
    public $priorities = [
        ['value' => '1', 'label' => '🚩 Priority 1'], // Red flag emoji
        ['value' => '2', 'label' => '🟧 Priority 2'], // Orange square emoji
        ['value' => '3', 'label' => '🟦 Priority 3'], // Blue square emoji
        ['value' => '4', 'label' => '⬜ Priority 4'], // White square emoji
    ];
   
    public function mount()
    {
        $this->statuses = Status::all(); // Fetch statuses

        $this->users = User::orderBy('name', 'ASC')->get(); // Fetch all users and order them alphabetically by name

    }
    public function resetForm()
{
    $this->name = '';
    $this->description = '';
    $this->status_id = '';
    $this->priority = '';
    $this->remark = '';
    $this->tags = '';
    $this->start_date = '';
    $this->due_date = '';
    $this->privacy = 1;
    
    $this->files = ''; // Reset to default privacy setting

    // ... reset other properties ...
}

    public function submit()
{
    $validatedData = $this->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'status_id' => 'required|exists:statuses,id',
        'priority' => 'required|in:1,2,3,4',
        'start_date' => 'nullable|date',
        'due_date' => 'nullable|date',
        'remark' => 'nullable|string',
        'privacy' => 'nullable',
        'tags' => 'nullable',
    ]);

    $project = Project::create([
            'name' => $this->name,
            'description' => $this->description,
            'status_id' => $this->status_id,
            'priority' => $this->priority,
            'tags' => $this->tags,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'remark' => $this->remark,
            'tags' => $this->tags,
            'privacy' => $this->privacy,
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
        ]);

        if ($this->privacy == 2 && !empty($this->selectedUsers)) {
        foreach ($this->selectedUsers as $userId) {
            Member::create([
                'model_id' => $project->id,
                'model_type' => Project::class, // Specify the model type
                'user_id' => $userId,
                'department_id' => Auth::user()->department_id, // Adjust as needed
                'date' => Carbon::now(), // Use Carbon::now() or similar
            ]);
        }
        
    }
    foreach ($this->files as $uploadedFile) {
    // Corrected variable names to use $uploadedFile instead of $file
    $fileName = auth()->id() . '-projects-' . time() . rand(1, 99999) . '.' . $uploadedFile->getClientOriginalName();
    $filePath = $uploadedFile->storeAs('projectfiles', $fileName, 'public');

    File::create([
        'model_id' => $project->id,
        'model_type' => Project::class,
        'filename' => $uploadedFile->getClientOriginalName(),
        'file_path' => $filePath,
        'name' => $uploadedFile->getClientOriginalName(),
        'type' => $uploadedFile->getClientMimeType(),
        'size' => $uploadedFile->getSize(),
        'user_id' => auth()->id(),
        'department_id' => auth()->user()->department_id,
    ]);
   
}
$this->resetform();
$this->files = [];

return redirect()->to('/projects');

}
}; ?>
<div>

    <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
        <div>
            <x-form wire:submit="submit">
            <!-- First Column -->
            <x-errors title="Oops!" description="Please, fix the errors below." />
            <x-input label="Name" wire:model="name" />
            <x-textarea label="Description"
                wire:model="description"
                placeholder="Place Enter Description ..."
                rows="5"
            />
            <select wire:model="priority" class="px-4 py-2 rounded shadow">
                <option value="">Select Priority</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
                @endforeach
            </select>
        </div>
    
        <div>
            <!-- Second Column -->
            <div class="mt-4 lg:mt-0">
                <input type="radio" wire:model="privacy" class="radio" value="1" onclick="toggleWelcomeMessage(false)"/> Private 
                <input type="radio" wire:model="privacy" class="radio" value="2" onclick="toggleWelcomeMessage(true)"/> Public
                <div id="welcomeMessage" class="hidden">
                    <x-choices label="Choisir De Membre" wire:model="selectedUsers" :options="$users" />
                </div>
                <x-select label="Status" icon="o-bell" :options="$statuses" wire:model="status_id" />
                <x-file wire:model="files" label="Documents" multiple id="fileInput" />
                @error('files.*') <span class="error">{{ $message }}</span> @enderror
                <x-input label="Remark" wire:model="remark" />
                <x-tags label="Tags" wire:model="tags" icon="o-home" hint="Hit enter to create a new tag"  />
            </div>
        </div>
    </div>
    
    <x-slot:actions>
        <x-button label="Cancel" link="/projects" />
        <x-button label="Create" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="submit" />
    </x-slot:actions>
</x-form>
</div>
<script>
function toggleWelcomeMessage(isPublic) {
    var welcomeMessage = document.getElementById('welcomeMessage');
    if (isPublic) {
        welcomeMessage.style.display = 'block';
    } else {
        welcomeMessage.style.display = 'none';
    }
}


</script>

