<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;

class StudentFeesStatus extends Model{
    use HasFactory;
    protected $table    = 'student_fees_status';
    protected $fillable = [
        'course_id',
        'student_id',
        'installment',
        'paid',
        'balance',
        'paid_date',
        'next_installment_due_date',
        'status'
    ];

    public function course(){
        return $this->hasOne(Course::class,'id','course_id');
    }

    public function student(){
        return $this->hasOne(Student::class,'id','student_id');
    }

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }

    public function getPaidDateAttribute($date) {
        $paid_date = Carbon::parse($date);
        return $paid_date->format("d-m-Y");
    }

    public function getNextInstallmentDueDateAttribute($date) {
        $next_installment_due_date = Carbon::parse($date);
        return $next_installment_due_date->format("d-m-Y");
    }
}
