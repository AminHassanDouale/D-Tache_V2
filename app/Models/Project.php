<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, HasUuids;
   

    protected $fillable = [
        'name', 'description', 'status_id', 'priority', 'privacy',
        'start_date', 'due_date', 'remark', 'tags', 'user_id', 'department_id'
    ];
    protected $casts = [
        'tags' => 'array',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function members()
    {
        return $this->morphMany(Member::class, 'model');
    }
    public function statuses()
        {
            return $this->belongsTo(Status::class, 'status_id');
        }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'model');
    }
    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }
    public function department()
        {
            return $this->belongsTo(Department::class, 'department_id');
        }
}
