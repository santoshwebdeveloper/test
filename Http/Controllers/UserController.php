<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller{

    /**
     * Instantiate a new UserController instance.
     */
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-user',   ['only' => ['index']]);
        $this->middleware('permission:create-user', ['only' => ['create','store']]);
        $this->middleware('permission:edit-user',   ['only' => ['edit','update']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(){
        return Inertia::render('User/Index', [
            'users' => User::whereNotIn('type',['student','admin'])->latest('id')->paginate(50)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        return Inertia::render('User/Form', [
            'roles' => Role::pluck('name')->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserCreateRequest $request){
        $input              = $request->all();
        $input['password']  = Hash::make($request->password);
        $input['type']      = strtolower($request->role);
        $user               = User::create($input);
        $user->assignRole($request->role);
        return redirect()->route('users.index')->banner('New user is added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user){
        return Inertia::render('User/Form', [
            'users'     => $user,
            'roles'     => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user){
        $input = $request->all();
        if(!empty($request->password)){
            $input['password'] = Hash::make($request->password);
        }else{
            $input = $request->except('password');
        }
        $user->update($input);
        $user->syncRoles($request->role);

        return redirect()->route('users.index')->banner('User is updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id){
        $user = User::find($id);
        $user->syncRoles([]);
        $user->delete();
        return redirect()->route('users.index')->banner('User is deleted successfully.');
    }
}
