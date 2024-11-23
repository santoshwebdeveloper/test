<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use Carbon\Carbon;

class Gatepass extends Model{
    use HasFactory;
    protected $fillable = ['student_id','class','receiving_time','receiver_name','contact_number','address','purpose'];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }
}
