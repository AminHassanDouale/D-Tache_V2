<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $fillable = ['model_id', 'model_type', 'date', 'user_id', 'department_id'];
    
    public function model()
    {
        return $this->morphTo();
    }
}
