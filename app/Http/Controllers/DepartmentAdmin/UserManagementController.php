<?php

namespace App\Http\Controllers\DepartmentAdmin;

use App\Http\Requests\SystemAdmin\UserRequest;
use App\Models\DepartmentUser;
use App\Models\Position;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentAdmin\UserManagementRequest;
use Illuminate\Support\Facades\Auth;
use File;

class UserManagementController extends Controller
{
    public function index()
    {
        $departmentID = DepartmentUser::where('user_id', Auth::user()->id)->first();
        $departmentUser = DB::table('users')
            ->join('department_users', 'department_users.user_id', '=', 'users.id')
            ->where('users.is_active',config('setting.active.is_active'))
            ->where('users.role',config('setting.position.secretary'))
            ->where('department_users.position_id',config('setting.position.secretary'))
            ->where('department_users.department_id', $departmentID['department_id'])
            ->get();
//        $departmentUser = User::with('departmentUser')->where('is_active',config('setting.active.is_active'))->where('departmentUser.department_id',2)->get();

        return view('department_admin.usersUI.index', compact( 'departmentUser'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('department_admin.usersUI.add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserManagementRequest $request)
    {
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['avatar'] = $this->savePicture($input);
        $input['role'] = Auth::user()->role;
        $departmentID = DepartmentUser::where('user_id',Auth::user()->id)->first();
        User::create($input);
        $id = User::select('id')->where('email', $input['email'])->first();
        DB::table('department_users')->insert(['user_id' => $id->id, 'start_date' => Carbon::now(), 'end_date' => $input['end_date'], 'department_id' => $departmentID['department_id'], 'position_id' => config('setting.position.secretary')]);

        return redirect()->route('users.index')->with('messageSuccess', 'Thêm Thành Công');
    }

    public function indexOfAdd(){
        $listUsers = DB::table('users')->join('department_users', 'users.id', '=', 'department_users.user_id')
            ->where('department_users.department_id', config('setting.department.no_department'))
            ->where('users.is_active', config('setting.active.is_active'))
            ->pluck('users.name', 'users.id');

        return view('department_admin.usersUI.add_user_exists', compact('listUsers'));
    }

    public function addUserExist(Request $request){
        $input = $request->departments;
        $departmentID = DepartmentUser::where('user_id',Auth::user()->id)->first();
        foreach($input as $value){
            DepartmentUser::where('user_id', $value)->update(['department_id' => $departmentID['department_id']]);
        }

        return redirect()->route('users.index')->with('messageSuccess', 'Thêm Thành Công');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function archiveIndex(){
        $departmentUser = User::with('departmentUser')->where('is_active',config('setting.active.no_active'))->get();
        $position = Position::pluck('name', 'id');
        $department = Department::pluck('name', 'id');

        return view('department_admin.users.archive', compact( 'position', 'department', 'departmentUser'));
    }

    public function restore($id){
        DB::beginTransaction();
        try
        {
            User::find($id)->update(['is_active' => config('setting.active.is_active')]);
            DB::commit();

            return redirect()->route('users.archive')->with('messageSuccess', 'Hoàn Tác Thành Công');
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            return redirect()->route('users.archive')->with('messageFail', 'Hoàn Tác Thất Bại');
        }
    }

    public function savePicture($input){
        if(isset($input['avatar']))
        {
            $file = $input['avatar'];
            $fileExtension = $input['avatar']->getClientOriginalExtension();
            $newName = 'avatar-'.time().'.'.$fileExtension;
            $path = public_path('images/avatar');
            $input['avatar'] = $newName;
            $file->move($path, $newName);
            return $newName;
        }
    }

    public function show($id)
    {
        try
        {
            $user = User::with('departmentUser')->where('users.id',$id)->first();
            $department = DepartmentUser::where('user_id',$id)->first();

            return view('department_admin.usersUI.edit', compact('user', 'department'));
        }
        catch (Exception $exception)
        {
            return redirect()->back()->with('messageFail', "Lỗi Hệ Thống");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    public function ajaxEmail(){
        $user = User::where('is_active',config('setting.active.is_active'))->where('status',config('setting.lock.no_lock'))->get();
        return response()->json($user);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserManagementRequest $request, $id)
    {
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        DB::beginTransaction();
        try
        {
            if(!isset($input['avatar'])){
                User::find($id)->update(['email' => $input['email'], 'password' => $input['password'], 'name' => $input['name'], 'birth_date' => $input['birth_date']]);
                DepartmentUser::where('user_id', $id)->update(['start_date' => Carbon::now(),'end_date' => $input['end_date']]);
            }
            else {
                $input['avatar'] = $this->savePicture($input);
                User::find($id)->update($input);
                DepartmentUser::where('user_id', $id)->update(['start_date' => Carbon::now(),'end_date' => $input['end_date']]);
            }
            DB::commit();

            return redirect()->route('users.index')->with('messageSuccess', 'Cập Nhật Thành Công');
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            return redirect()->route('users.index')->with('messageFail', 'Cập Nhật Thất Bại');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try
        {
            $user = User::findOrFail($id);
            $user->update(['is_active' => config('setting.active.no_active')]);
            DepartmentUser::where('user_id', $id)->update(['is_active' => config('setting.active.no_active')]);
            DB::commit();

            return redirect()->route('users.index')->with('messageSuccess', 'Xóa Thành Công');
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            return redirect()->back()->with('messageFail', 'Xóa Thất Bại');
        }
    }
}
