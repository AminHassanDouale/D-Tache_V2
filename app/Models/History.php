<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'date',
        'name',
        'department_id',
        'user_id',
    ];

    
    public function model()
    {
        return $this->morphTo();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
