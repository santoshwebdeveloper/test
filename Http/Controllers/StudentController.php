<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-student',      ['only' => ['index']]);
        $this->middleware('permission:create-student',    ['only' => ['create','store']]);
        $this->middleware('permission:edit-student',      ['only' => ['edit','update']]);
        $this->middleware('permission:delete-student',    ['only' => ['destroy']]);
    }
    
    public function index(Request $request){
        $student = new Student();
        if($request->has('orderBy') && $request->has('order')){
            $request->orderBy = $request->orderBy == 'course' ? 'course_id' : $request->orderBy;
            $student = $request->order == 'asc' ? $student->orderBy($request->orderBy,'ASC') : $student->orderBy($request->orderBy,'DESC');
        }

        if($request->has('search')){
            $student = $student->where('class','LIKE','%'.$request->search.'%')
            ->orWhere('school','LIKE','%'.$request->search.'%')
            ->orWhere('father_name','LIKE','%'.$request->search.'%')
            ->orWhere('phone_number','LIKE','%'.$request->search.'%')
            ->orWhereHas('course',function ($query) use ($request) {
                $query->where('name','LIKE','%'.$request->search.'%');
            })
            ->orWhereHas('user',function ($query) use ($request) {
                $query->where('name','LIKE','%'.$request->search.'%');
            })
            ->orWhereHas('user',function ($query) use ($request) {
                $query->where('email','LIKE','%'.$request->search.'%');
            });
        }
        $student = $student->with('course:id,name')->with('user:id,name,email,profile_photo_path')->latest()->paginate(50);
        $getUserPermissions = auth()->user()->getAllPermissionsAttribute();
        
        return Inertia::render('Student/Index',[
            'student'                   => $student,
            'gate_pass_permissions'     => in_array('list-gate-pass',$getUserPermissions) ? true : false,
            'fees_permissions'          => in_array('show-student-fees',$getUserPermissions) ? true : false,
            'notice_permissions'        => in_array('list-notice',$getUserPermissions) ? true : false,
        ]);
    }

    public function create(){
        $course = Course::all();
        return Inertia::render('Student/Form',[
            'course' => $course,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name'              => 'required|string|max:250',
            'email'             => 'required|email|max:250|unique:users',
            'class'             => 'required',
            'dob'               => 'required',
            'phone_number'      => 'required|numeric|unique:students|digits:10',
            'school'            => 'required',
            'father_name'       => 'required|string|max:255',
            'password'          => 'required|min:8',
            'course_id'         => 'required',
            'profile_image'     => 'required|mimes:jpeg,png,jpg',
        ]);

        $url = null;
        if($request->hasFile('profile_image')){
            $filenamewithextension  = $request->file('profile_image')->getClientOriginalName();
            $filename               = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $extension              = $request->file('profile_image')->getClientOriginalExtension();
            $filenametostore        = "dabad-offline/student/".$filename.'_'.time().'.'.$extension;
            $path                   = Storage::disk('s3')->put($filenametostore, fopen($request->file('profile_image'), 'r+'), 'public');
            $url                    = Storage::disk('s3')->url($filenametostore);
        }

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'profile_photo_path'=> $url,
        ]);

        $user->assignRole('Student');

        Student::create([
            'user_id'       => $user->id,
            'class'         => $request->class,
            'dob'           => $request->dob,
            'phone_number'  => $request->phone_number,
            'school'        => $request->school,
            'father_name'   => $request->father_name,
            'course_id'     => $request->course_id,
        ]);

        return redirect()->route('student.index')->banner('New Student Register Successfully.');
    }

    public function edit(int $id){
        $course  = Course::all();
        $student = Student::where('id',$id)->with('user:id,name,email')->first();
        if($student){
            return Inertia::render('Student/Form',[
                'student'   => $student,
                'course'    => $course,
            ]);
        }
        return redirect()->route('student.index')->banner('Student Not Found.');
    }

    public function update(Request $request,int $id){
        $student  = Student::findOrFail($id);
        $user     = User::findOrFail($student->user_id);

        $request->validate([
            'name'              => 'required|string|max:250',
            'email'             => 'required|email|unique:users,email,'.$user->id,
            'dob'               => 'required',
            'phone_number'      => 'required|numeric|digits:10|unique:students,phone_number,'.$id,
            'school'            => 'required',
            'father_name'       => 'required|string|max:255',
            'password'          => 'nullable|min:8',
            'course_id'         => 'required',
            'profile_image'     => 'nullable|mimes:jpeg,png,jpg',
        ]);
        
        $user->name             = $request->name;
        $user->email            = $request->email;
        if($request->has('password')){
            $user->password     = Hash::make($request->password);
        }
        
        $student->dob           = $request->dob;
        $student->class         = $request->class;
        $student->phone_number  = $request->phone_number;
        $student->school        = $request->school;
        $student->father_name   = $request->father_name;
        $student->course_id     = $request->course_id;

        if($request->hasFile('profile_image')){
            $filenamewithextension    = $request->file('profile_image')->getClientOriginalName();
            $filename                 = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $extension                = $request->file('profile_image')->getClientOriginalExtension();
            $filenametostore          = "dabad-offline/student/".$filename.'_'.time().'.'.$extension;
            $path                     = Storage::disk('s3')->put($filenametostore, fopen($request->file('profile_image'), 'r+'), 'public');
            $user->profile_photo_path = Storage::disk('s3')->url($filenametostore);
        }

        $user->update();
        $student->update();
        return redirect()->route('student.index')->banner('Student Data Update Successfully.');
    }

    public function destroy(int $id){
        $student = Student::where('id',$id)->first();
        $user    = User::findOrFail($student->user_id);
        if($student && $user){
            $student->gate_pass()->delete();
            $student->notice()->delete();
            $student->score_records()->delete();
            $student->fee_status()->delete();
            $student->delete();
            $user->delete();
        }
        return redirect()->route('student.index')->banner('Student Deleted Successfully.');
    }
}
