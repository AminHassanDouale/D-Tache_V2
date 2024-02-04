<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['model_id', 'model_type', 'comment', 'user_id', 'department_id', 'date'];

    public function model()
    {
        return $this->morphTo();
    }
    public function user()
{
    return $this->belongsTo(User::class);
}

}
