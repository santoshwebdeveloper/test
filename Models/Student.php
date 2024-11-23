<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\User;
use App\Models\Gatepass;
use App\Models\StudentFeesStatus;
use App\Models\ScoreRecord;
use App\Models\Notice;
use Carbon\Carbon;

class Student extends Model{
    use HasFactory;
    protected $fillable = ['user_id','class','dob','phone_number','school','father_name','course_id']; 

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function gate_pass(){
        return $this->hasMany(Gatepass::class,'student_id','id');
    }

    public function fee_status(){
        return $this->hasMany(StudentFeesStatus::class,'student_id','id');
    }

    public function score_records(){
        return $this->hasMany(ScoreRecord::class,'student_id','id');
    }

    public function notice(){
        return $this->hasMany(Notice::class,'student_id','id');
    }

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }
}
