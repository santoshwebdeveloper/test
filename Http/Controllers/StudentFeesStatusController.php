<?php
namespace App\Http\Controllers;

use App\Models\StudentFeesStatus;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentFeesStatusController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:show-student-fees',   ['only' => ['index']]);
        $this->middleware('permission:add-student-fees',    ['only' => ['create','store']]);
    }

    public function index(int $student_id){
        $student     = Student::where('id',$student_id)->with('user:id,name','course')->first();
        $student_fee = StudentFeesStatus::where('student_id',$student_id)
        ->with('student.user:id,name')
        ->with('course:id,name')
        ->paginate(10);
        return Inertia::render('StudentFees/Index',[
            'student_fee'   => $student_fee,
            'student_id'    => $student_id,
            'student'       => $student,
        ]);
    }
    
    public function create(int $student_id){
        $student        = Student::where('id',$student_id)->with('user:id,name','course')->first();
        $student_fee    = StudentFeesStatus::where('student_id',$student_id)
        ->with('student.user:id,name')
        ->with('course:id,name')
        ->get();
        return Inertia::render('StudentFees/Form',[
            'student_fee'   => $student_fee,
            'student_id'    => $student_id,
            'student'       => $student,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'course_id'     => 'required',
            'student_id'    => 'required',
            'balance'       => 'required|integer|gt:0',
        ]);
        
        if(is_array($request->installments) && !empty($request->installments)){
            foreach($request->installments as $installment ){
                $insert = [
                    "course_id"                 => $request->course_id,
                    "student_id"                => $request->student_id,
                    "balance"                   => $request->balance,
                    "installment"               => $installment['installment'],
                    "paid"                      => $installment['paid'],
                    "paid_date"                 => $installment['paid_date'],
                    "next_installment_due_date" => $installment['due_date'],
                    "status"                    => $installment['status'],
                ];
                if($installment['id'] != 0){
                    StudentFeesStatus::findOrFail($installment['id'])->update($insert);
                }else{
                    StudentFeesStatus::create($insert);
                }
            }
        }
        return redirect()->route('student.fee.index',$request->student_id)->banner('Student Fees Saved.');
    }

    // public function destroy(int $id){
    //     $student_fee    = StudentFeesStatus::findOrFail($id);
    //     $student_id     = 0;
    //     if($student_fee){
    //         $student_id = $student_fee->student_id;
    //         $student_fee->delete();
    //     }
    //     return redirect()->route('student.fee.index',$student_id)->banner('Student Fees Deleted.');
    // }
}
