<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model{
    use HasFactory;
    protected $hidden = [
        'app_maintenance_icon',
        'app_updation_icon',
        'created_at',
        'updated_at',
    ];

    protected $fillable = ['app_status','app_version','app_maintenance_icon','app_updation_icon'];
}
