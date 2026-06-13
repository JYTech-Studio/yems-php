<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    use HasUuids;

    protected $fillable = ['student_id', 'parent_id', 'relation'];
}
