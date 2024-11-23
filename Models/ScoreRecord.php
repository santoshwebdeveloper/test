<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\CourseTest;
use Carbon\Carbon;

class ScoreRecord extends Model{
    use HasFactory;
    protected $fillable = ['student_id','course_test_id','scored_marks','rank'];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function course_test(){
        return $this->belongsTo(CourseTest::class);
    }

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }
}
