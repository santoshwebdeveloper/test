<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Gatepass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GatepassController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-gate-pass',      ['only' => ['index']]);
        $this->middleware('permission:create-gate-pass',    ['only' => ['create','store']]);
        $this->middleware('permission:edit-gate-pass',      ['only' => ['edit','update']]);
        $this->middleware('permission:delete-gate-pass',    ['only' => ['destroy']]);
    }
    
    public function index(int $student_id){
        $gate_pass = Gatepass::where('student_id',$student_id)->with('student.user:id,name')->latest()->paginate(10);
        return Inertia::render('GatePass/Index',[
            'gate_pass'     => $gate_pass,
            'student_id'    => $student_id,
        ]);
    }

    public function create(int $student_id){
        $students = Student::where('id',$student_id)->with('user:id,name')->get();
        return Inertia::render('GatePass/Form',[
            'students'  => $students,
            'student_id'=> $student_id,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'student_id'        => 'required',
            'class'             => 'required',
            'receiving_time'    => 'required',
            'receiver_name'     => 'required',
            'contact_number'    => 'required',
            'address'           => 'required',
            'purpose'           => 'required',
        ]);
        Gatepass::create($request->all());
        return redirect()->route('gate.pass.index',$request->student_id)->banner('Student Gate Pass Added.');
    }

    public function edit($ID){
        $gate_pass  = Gatepass::where('id',$ID)->first();
        $students   = Student::where('id',$gate_pass->student_id)->with('user:id,name')->get();
        return Inertia::render('GatePass/Form',[
            'students'    => $students,
            'gate_pass'   => $gate_pass
        ]);
    }

    public function update(Request $request, $ID){
        $request->validate([
            'student_id'        => 'required',
            'class'             => 'required',
            'receiving_time'    => 'required',
            'receiver_name'     => 'required',
            'contact_number'    => 'required',
            'address'           => 'required',
            'purpose'           => 'required',
        ]);
        Gatepass::findOrFail($ID)->update($request->all());
        return redirect()->route('gate.pass.index',$request->student_id)->banner('Student Gate Pass Updated.');
    }

    public function destroy($ID){
        $gate_pass  = Gatepass::where('id',$ID)->first();
        $student_id = $gate_pass->student_id;
        if($gate_pass){
            $gate_pass->delete();
        }
        return redirect()->route('gate.pass.index',$student_id)->banner('Student Gate Pass Deleted.');
    }
}
