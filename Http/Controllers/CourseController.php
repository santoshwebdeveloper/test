<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-course',   ['only' => ['index']]);
        $this->middleware('permission:create-course', ['only' => ['create','store']]);
        $this->middleware('permission:edit-course',   ['only' => ['edit','update']]);
    }
   
    public function index(){
        $course = Course::latest()->paginate(10);
        return Inertia::render('Courses/Index',['course' => $course]);
    }

    public function create(){
        return Inertia::render('Courses/Form');
    }

    public function store(Request $request){
        $request->validate([
            'name'      => 'required|max:255',
            'price'     => 'required|integer|gt:0',
        ]);
        Course::create($request->all());
        return redirect()->route('courses.index')->banner('New Course Has Been Enrolled.');
    }

    public function edit(int $id){
        $course = Course::where('id',$id)->first();
        if($course){
            return Inertia::render('Courses/Form',[
                'course'=>$course,
            ]);
        }
        return redirect()->route('courses.index')->banner('Course ID Not Found.');
        
    }

    public function update(Request $request,$id){
        $request->validate([
            'name'    => 'required|max:255',
            'price'   => 'required|integer|gt:0',
        ]);
        $course = Course::findOrFail($id);
        $course->update($request->all());
        return redirect()->route('courses.index')->banner('Course Has Been Updated.');
    }

    public function destroy(int $id){
        $course = Course::find($id);
        if($course){
            $course->delete();
        }
        return redirect()->route('courses.index')->banner('Course Deleted Successfully.');
    }
}
