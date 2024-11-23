<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;

class AdminNotice extends Model{
    use HasFactory;

    protected $fillable = ['admin_id','title','message'];

    public function getCreatedAtAttribute($date) {
        $created_at = Carbon::parse($date);
        return $created_at->format("d-m-Y");
    }

    public function user(){
        return $this->belongsTo(User::class,'admin_id');
    }
}
