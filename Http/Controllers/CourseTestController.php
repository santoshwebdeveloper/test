<?php
namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseTest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseTestController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-course-test',   ['only' => ['index']]);
        $this->middleware('permission:create-course-test', ['only' => ['create','store']]);
        $this->middleware('permission:edit-course-test',   ['only' => ['edit','update']]);
    }
    
    public function index(int $courseId = 0){
        $course_test = new CourseTest();
        if($courseId > 0){
            $course_test = $course_test::where('course_id',$courseId);
        }
        $course_test = $course_test->with('course:id,name')->latest()->paginate(50);
        return Inertia::render('CourseTest/Index',[
            'course_test'   => $course_test,
            'course_id'     => $courseId,
        ]);
    }

    public function create(int $courseId = 0){
        $course = Course::all();
        if($courseId > 0){
            $course = Course::where('id',$courseId)->get();
        }
        return Inertia::render('CourseTest/Form',[
            "course"    => $course,
            "course_id" => $courseId,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'course_id'     => 'required',
            'test_name'     => 'required|max:255',
            'total_marks'   => 'required|integer|gt:0',
        ]);
        CourseTest::create($request->all());
        return redirect()->route('course.test.index')->banner('New Course Test Has Been Enrolled.');
    }

    public function edit(int $id){
        $course      = Course::all();
        $course_test = CourseTest::where('id',$id)->first();
        if($course_test && $course){
            return Inertia::render('CourseTest/Form',[
                'course'        => $course,
                'course_test'   => $course_test,
            ]);
        }
        return redirect()->route('course.test.index')->banner('Course Test ID Not Found.');
    }

    public function update(Request $request,int $id){
        $request->validate([
            'course_id'     => 'required',
            'test_name'     => 'required|max:255',
            'total_marks'   => 'required|integer|gt:0',
        ]);
        $course_test = CourseTest::findOrFail($id);
        $course_test->update($request->all());
        return redirect()->route('course.test.index')->banner('Course Test Has Been Updated.');
    }

    public function destroy(int $id){
        $course_test = CourseTest::find($id);
        if($course_test){
            $course_test->delete();
        }
        return redirect()->route('course.test.index')->banner('Course Test Deleted Successfully.');
    }
}
