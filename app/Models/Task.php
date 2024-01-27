<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory,HasUuids;
    protected $fillable = ['name', 'description','priority', 'status_id', 'notification', 'assignee_id', 'start_date', 'end_date', 'user_id', 'department_id','project_id','tags','status_updated_at'];
    protected $casts = [
        'tags' => 'array',
        'start_date' => 'date',
        'due_date' => 'date',
    ];
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($task) {
            if ($task->isDirty('status_id') && $task->status_id == 3) {
                $task->status_updated_at = Carbon::now();
            }
        });
    }
    
    public function project()
{
    return $this->belongsTo(Project::class, 'project_id');
}
public function status()
{
    return $this->belongsTo(Status::class, 'status_id');
}

public function department()
{
    return $this->belongsTo(Department::class);
}
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
public function assignee()
{
    return $this->belongsTo(User::class, 'assignee_id');
}


}
