<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use Carbon\Carbon;

class Course extends Model{
    use HasFactory;
    protected $fillable = ['name','price'];

    public function student(){
        return $this->hasMany(Student::class);
    }

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }
}
