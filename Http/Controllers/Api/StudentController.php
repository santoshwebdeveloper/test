<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseTest;
use App\Models\Gatepass;
use App\Models\AdminNotice;
use App\Models\Student;
use App\Models\ScoreRecord;
use App\Models\AppSetting;
use App\Models\User;
use Auth;
use Validator;
use Carbon\Carbon;

class StudentController extends Controller{
    
    public function login(Request $request){

        $login = Validator::make($request->all(), [
            'email'        => 'required|string',
            'password'     => 'required|string',
            'device_token' => 'required',
        ]);
  
        if ($login->fails()) {
            return response([
                'status' => false,
                'message'=> $login->errors()->first()
            ]);
        }
  
        if(!( Auth::attempt([ 'email' => $request->email, 'password' => $request->password, 'type' => 'student'] ) ) ){
            return response([
                'status'  => false,
                'message' => 'Invalid login credentials.'
            ]);
  
        }else{

            Auth::user()->update([
                'device_token' => $request->device_token
            ]);

            $user = User::find(Auth::user()->id);
            $user->device_token = $request->device_token;
            $user->save();
            $accessToken  = Auth::user()->createToken('token-name', ['server:update'])->plainTextToken;
            return response([
                'status'        => true,
                'message'       => 'Login Successful.',
                'access_token'  => $accessToken,
                'data'          => $user,
            ]);
        }
    }

    public function profile(){
        $user =  User::where('id',Auth::user()->id)->with('student.course:id,name')->first();
        $user = [
            'name'          => $user->name,
            'email'         => $user->email,
            'class'         => $user->student->class,
            'dob'           => $user->student->dob,
            'phone_number'  => $user->student->phone_number,
            'school'        => $user->student->school,
            'father_name'   => $user->student->father_name,
            'course'        => $user->student->course->name,
            'profile_image' => is_null($user->profile_photo_path) ? $user->profile_photo_url : $user->profile_photo_path,
        ];

        return response([
            'status'    => true,
            'message'   => 'Student Profile',
            'data'      => $user
        ]);
    }

    public function feeStatus(Request $request){
        $response   = [];
        $user       = User::where('id',Auth::user()->id);
        
        if(is_null($request->id)){
            $user       = $user->with('student.course')->get();
            if(isset($user[0]['student']['course'])){
                array_push($response,$user[0]['student']['course']);
            }
        }else{
            $user = $user->with('student.fee_status.course')->get();
            if(isset($user[0]['student']['fee_status']) && !empty($user[0]['student']['fee_status']) ){
                foreach($user[0]['student']['fee_status'] as $key => $data){
                    $response[$key]  =  [
                        'installment'               => $data['installment'],
                        'paid'                      => $data['paid'],
                        'balance'                   => $data['balance'],
                        'paid_date'                 => $data['paid_date'],
                        'due_date'                  => $data['next_installment_due_date'],
                        'course'                    => $data['course']['name'],
                        'status'                    => $data['status'],
                    ];
                }
            }
        }

        return response([
            'status'    => true,
            'message'   => 'Fees Status Successfully Fetch',
            'data'      => $response
        ]);
    }

    public function testRecords(){
        $recent     = [];
        $previous   = [];
        $user       = User::where('id',Auth::user()->id)->with('student.score_records.course_test.course')->get();
        
        if(isset($user[0]['student']['score_records']) && !empty($user[0]['student']['score_records']) ){
            foreach($user[0]['student']['score_records'] as $data){
                
                $diffOfDate = strtotime(date('Y-m-d')) - strtotime($data['created_at']);
                $noOfDays   = floor( $diffOfDate / 60*60*24 );
                
                $insert = [
                    'scored_marks'              => $data['scored_marks'],
                    'rank'                      => $data['rank'],
                    'test_name'                 => $data['course_test']['test_name'],
                    'test_date'                 => $data['created_at'],
                    'course'                    => $data['course_test']['course']['name'],
                    'total_marks'               => $data['course_test']['total_marks'],
                ];
                
                if($noOfDays == 0){
                    $insert['time'] = $data['created_at'];
                    array_push($recent,$insert);
                }else{
                    array_push($previous,$insert);
                }
            }
        
            return response([
                'status'    => true,
                'message'   => 'Test Records Successfully Fetch',
                'data'      => [
                    'recent_results'    => $recent,
                    'previous_results'  => $previous,
                ],
            ]);
        }
    }

    public function gatePass(){
        $user = User::where('id',Auth::user()->id)->with('student.gate_pass')->get();
        $response = [];
        if(isset($user[0]['student']['gate_pass']) && !empty($user[0]['student']['gate_pass']) ){
            foreach($user[0]['student']['gate_pass'] as $key => $data){
                $response[$key] = [
                    'name'              => $user[0]['name'],
                    'class'             => $data['class'],
                    'receiving_time'    => $data['receiving_time'],
                    'receiver_name'     => $data['receiver_name'],
                    'contact_number'    => $data['contact_number'],
                    'address'           => $data['address'],
                    'purpose'           => $data['purpose'],
                    'date'              => $data['created_at'],
                ];
            }
        
            return response([
                'status'    => true,
                'message'   => 'Gate Pass Successfully Fetch',
                'data'      => $response
            ]);
        }
    }

    public function notice(){
        $recent     = [];
        $previous   = [];
        $admin      = [];
        $user       = User::where('id',Auth::user()->id)->with('student.notice')->get();

        if(isset($user[0]['student']['notice']) && !empty($user[0]['student']['notice']) ){
            foreach($user[0]['student']['notice'] as $key => $data){
                
                $diffOfDate = strtotime(date('Y-m-d')) - strtotime($data['created_at']);
                $noOfDays   = floor( $diffOfDate / 60*60*24 );

                $insert = [
                    'title'     => $data['title'],
                    'message'   => $data['message'],
                    'date'      => $data['created_at'],
                ];

                if($noOfDays == 0){
                    array_push($recent,$insert);
                }else{
                    array_push($previous,$insert);
                }
            }

            foreach(AdminNotice::all() as $adminNotice){
                array_push($admin,[
                    'title'     => $adminNotice['title'],
                    'message'   => $adminNotice['message'],
                    'date'      => $adminNotice['created_at'],
                ]);
            }
        
            return response([
                'status'    => true,
                'message'   => 'Student Notice',
                'data'      => [
                    'admin_notices'     => $admin,
                    'recent_notices'    => $recent,
                    'previous_notices'  => $previous,
                ],
            ]);
        }
    }

    public function searchTestRecords(Request $request){
        $user           = User::where('id',Auth::user()->id)->with('student')->get();
        $score_records  = [];
        $response       = [];
        
        if(isset($user[0]['student']['id'])){
            $score_records    = ScoreRecord::orWhereHas('course_test',function ($query) use ($request) {
                $query->where('test_name','LIKE','%'.$request->search.'%');
            });

            $score_records = $score_records->where('student_id',$user[0]['student']['id'])->get();

            if($score_records->isEmpty()){
                $score_records = ScoreRecord::where('student_id',$user[0]['student']['id'])->where('created_at','>=',$request->search)->get();
            }

            foreach($score_records as $index => $score_record){
                $response[$index] = [
                    'scored_marks'              => $score_record['scored_marks'],
                    'rank'                      => $score_record['rank'],
                    'test_name'                 => $score_record['course_test']['test_name'],
                    'test_date'                 => $score_record['created_at'],
                    'course'                    => $score_record['course_test']['course']['name'],
                    'total_marks'               => $score_record['course_test']['total_marks'],
                ];
            }
        }


        return response([
            'status'    => true,
            'message'   => 'Student Test Records',
            'data'      => $response,
        ]);
    }

    public function appSettings(){
        return response([
            'status'    => true,
            'message'   => 'App Settings',
            'data'      => AppSetting::all(),
        ]);
    }
    
}
