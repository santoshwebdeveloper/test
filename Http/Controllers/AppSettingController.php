<?php

namespace App\Http\Controllers;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppSettingController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:settings',['only' => ['index']]);
    }

    public function index(){
        $app_setting = AppSetting::all();
        return Inertia::render('AppSetting/Form',[
            'app_setting' => $app_setting,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'app_status'      => 'required|max:255',
            'app_version'     => 'required',
        ]);
        if($request->id > 0){
            AppSetting::where('id',$request->id)->update($request->all());
        }else{
            AppSetting::create($request->all());
        }
        return redirect()->route('app.setting.index')->banner('App Settings Saved.');
    }
}
