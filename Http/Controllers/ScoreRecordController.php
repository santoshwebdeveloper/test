<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ScoreRecord;
use App\Models\CourseTest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScoreRecordController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:show-student-marks', ['only' => ['index']]);
        $this->middleware('permission:set-student-marks',  ['only' => ['create','store']]);
    }
    
    public function index(int $course_test_id){
        $score_record = ScoreRecord::where('course_test_id',$course_test_id)
        ->with('student.user:id,name')
        ->with('course_test')
        ->paginate(50);
        
        return Inertia::render('ScoreRecord/Index',[
            'score_record'      => $score_record,
            'course_test_id'    => $course_test_id,
        ]);
    }

    public function create(int $course_test_id){
        $score_record = CourseTest::where('id',$course_test_id)->with('course.student.user:id,name')->get();
        if($score_record && isset($score_record[0])){
            foreach($score_record[0]['course']['student'] as $key => $data){
                $score_record[0]['course']['student'][$key]['score_record'] = ScoreRecord::where('student_id',$data['id'])->where('course_test_id',$course_test_id)->first();
            }
            return Inertia::render('ScoreRecord/Form',[
                'score_record'      => $score_record[0],
                'course_test_id'    => $course_test_id,
            ]);
        }
        return redirect()->route('course.test.index')->banner('Course Test ID Not Found.');
    }

    public function store(Request $request,int $course_test_id){
        if(isset($request->all()['request'])){
            foreach ($request->all()['request'] as $key => $value) {
                if(isset($value['id']) && $value['id'] != 0){
                    ScoreRecord::where('id',$value['id'])->update([
                        'student_id'        => $value['student_id'],
                        'course_test_id'    => $course_test_id,
                        'scored_marks'      => $value['scored_marks'],
                        'rank'              => $value['rank'],
                    ]);
                }else{
                    ScoreRecord::create([
                        'student_id'        => $value['student_id'],
                        'course_test_id'    => $course_test_id,
                        'scored_marks'      => $value['scored_marks'],
                        'rank'              => $value['rank'],
                    ]);
                }
            }
        }
        return redirect()->route('scored.record.create',$course_test_id)->banner('Student Scored Saved.');
    }

    // public function edit($ID){
    //     $students       = Student::select('id','name')->get();
    //     $score_record   = ScoreRecord::where('id',$ID)->first();
    //     return Inertia::render('ScoreRecord/Form',[
    //         'students'     => $students,
    //         'score_record' => $score_record
    //     ]);
    // }

    // public function update(Request $request, $ID){
    //     $request->validate([
    //         'student_id'    => 'required',
    //         'test_name'     => 'required',
    //         'scored_marks'  => 'required',
    //         'total_marks'   => 'required',
    //         'rank'          => 'required',
    //     ]);
    //     ScoreRecord::findOrFail($ID)->update($request->all());
    //     return redirect()->route('scored.record.create')->banner('Student Scored Updated.');
    // }

    // public function destroy($ID){
    //     $student_fee     = ScoreRecord::where('id',$ID)->first();
    //     if($student_fee){
    //         $student_fee->delete();
    //     }
    //     return redirect()->route('scored.record.create')->banner('Student Scored Deleted.');
    // }
}
