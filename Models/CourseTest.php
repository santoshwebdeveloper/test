<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\ScoreRecord;
use Carbon\Carbon;

class CourseTest extends Model{
    use HasFactory;
    protected $fillable = ['course_id','test_name','total_marks'];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function scored_marks(){
        return $this->hasMany(ScoreRecord::class);
    }

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }
}
