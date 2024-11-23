<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Course;

class DashboardController extends Controller{

    public function dashboard(){
        $roles = Role::get(['name'])->toArray();
        $user  = [];
        foreach ($roles as $role) {
            $role           = strtolower($role['name']);
            $user[$role]    = User::where('type',$role)->count();
        }
        return Inertia::render('Dashboard',[
            'user'      => $user,
            'course'    => Course::count(),
        ]);
    }
    
}
