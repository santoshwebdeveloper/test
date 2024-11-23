<?php
namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Notice;
use App\Models\AdminNotice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Auth;

class NoticeController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-notice',      ['only' => ['index']]);
        $this->middleware('permission:create-notice',    ['only' => ['create','store']]);
        $this->middleware('permission:edit-notice',      ['only' => ['edit','update']]);
        $this->middleware('permission:delete-notice',    ['only' => ['destroy']]);
    }
    
    public function index(int $user_id = null){
        $notice = [];
        if(is_null($user_id) || $user_id == Auth::user()->id ){
            $notice  = AdminNotice::with('user:id,name')->latest()->paginate(50);
            $user_id = Auth::user()->id;
        }else{
            $notice  = Notice::where('student_id',$user_id)->with('student.user:id,name')->latest()->paginate(50);
        }

        return Inertia::render( 'Notice/Index',[
            'notice'    => $notice,
            'user_id'   => $user_id,
        ]);
    }

    public function create(int $user_id){
        $data = [];
        if($user_id == Auth::user()->id){
            $data = [
                'user' => User::where('id',Auth::user()->id)->first()
            ];
        }else{
            $data = [
                'student' => Student::where('id',$user_id)->with('user:id,name')->first()
            ];
        }

        return Inertia::render('Notice/Form',$data);
    }

    public function store(Request $request){
        $request->validate([
            'title'         => 'required|max:255',
            'message'       => 'required',
        ]);

        if(Auth::user()->id == $request->user_id){
            $request['admin_id'] = $request->user_id;
            AdminNotice::create($request->all());
            return redirect()->route('notice.index')->banner('Notice Created Successfully.');
        }else{
            $request['student_id'] = $request->user_id;
            Notice::create($request->all());
            return redirect()->route('notice.index',$request->student_id)->banner('Notice Created Successfully.');
        }
    }

    public function edit(int $id,int $user_id){
        $data   = [];
        if($user_id == Auth::user()->id){
            $data   = [
                'notice' => AdminNotice::where('id',$id)->first(),
                'user'   => User::where('id',Auth::user()->id)->first()
            ];
        }else{
            $data   = [
                'notice'  => Notice::where('student_id',$user_id)->where('id',$id)->with('student.user:id,name')->first(),  
                'student' => Student::where('id',$user_id)->with('user:id,name')->first()
            ];
        }

        if($data['notice']){
            return Inertia::render('Notice/Form',$data);
        }
    }

    public function update(Request $request,$id){
        $request->validate([
            'title'         => 'required|max:255',
            'message'       => 'required',
        ]);

        if(Auth::user()->id == $request->user_id){
            $request['admin_id'] = $request->user_id;
            AdminNotice::findOrFail($id)->update($request->all());
        }else{
            $request['student_id'] = $request->user_id;
            Notice::findOrFail($id)->update($request->all());
        }
        return redirect()->route('notice.index',$request->user_id)->banner('Notice Updated Successfully.');
    }

    public function destroy(int $id,int $user_id){
        if(Auth::user()->id == $user_id){
            AdminNotice::findOrFail($id)->delete();
        }else{
            Notice::findOrFail($id)->delete();
        }
        return redirect()->route('notice.index',$user_id)->banner('Notice Deleted Successfully.');
    }
}
