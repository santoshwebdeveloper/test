<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use DB;

class RoleController extends Controller{

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('permission:list-role', ['only' => ['index','show']]);
        $this->middleware('permission:create-role', ['only' => ['create','store']]);
        $this->middleware('permission:edit-role',   ['only' => ['edit','update']]);
        $this->middleware('permission:delete-role', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(){
        return Inertia::render('Role/Index', [
            'roles' => Role::whereNotIn('name',['Student'])->orderBy('id','DESC')->paginate(3)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        return Inertia::render('Role/Form', [
            'permissions' => Permission::get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request){
        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'web'
        ]);
        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();
        $role->syncPermissions($permissions);
        return redirect()->route('roles.index')->banner('New role is added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role){
        $rolePermissions = DB::table("role_has_permissions")->where("role_id",$role->id)
            ->pluck('permission_id')
            ->all();

        return Inertia::render('Role/Form', [
            'role'              => $role,
            'permissions'       => Permission::get(),
            'rolePermissions'   => $rolePermissions
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role){
        $input = $request->only('name');

        $role->update($input);

        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();

        $role->syncPermissions($permissions);    
        
        return redirect()->route('roles.index')->banner('Role is updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $role_id){
        $role = Role::find($role_id);
        if(auth()->user()->hasRole($role->name)){
            abort(403, 'CAN NOT DELETE SELF ASSIGNED ROLE');
        }
        $role->delete();
        return redirect()->route('roles.index')->banner('Role is deleted successfully.');
    }
}