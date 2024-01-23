<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = ['model_id', 'model_type', 'filename', 'file_path', 'name', 'type', 'size', 'user_id', 'department_id'];

    public function model()
    {
        return $this->morphTo();
    }
}
