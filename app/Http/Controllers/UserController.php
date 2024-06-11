<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user-access');
    }

    public function index()
    {
        $title = 'Data User';
        $breadcrumbs = ['Master', 'Data User'];
        $roles = Role::get();

        return view('user.index', compact('title', 'breadcrumbs', 'roles'));
    }

    public function get(Request $request)
    {
        if ($request->ajax()) {
            $data = User::orderBy('name', 'asc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="#modal-dialog" id="' . $row->id . '" class="btn btn-sm btn-success btn-edit" data-route="' . route('users.update', $row->id) . '" data-bs-toggle="modal">Edit</a> <button type="button" data-route="' . route('users.destroy', $row->id) . '" class="delete btn btn-danger btn-delete btn-sm">Delete</button>';
                    return $actionBtn;
                })
                ->editColumn('role', function ($row) {
                    return $row->roles()->first()->name;
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|unique:users',
                'name' => 'required|string',
                'password' => 'required|string',
                'uid' => 'required|string|unique:users',
                'role' => 'required|numeric',
                'status' => 'required|numeric',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'username' => $request->username,
                'uid' => $request->uid,
                'name' => $request->name,
                'password' => bcrypt($request->password),
                'is_active' => $request->status,
            ]);

            $user->syncRoles($request->role);

            DB::commit();

            return back()->with('success', "User {$user->username} berhasil ditambahkan");
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'role' => $user->roles()->first()->id
        ]);
    }

    public function edit(User $user)
    {
        //
    }

    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'username' => 'required|string|unique:users,username,' . $user->id,
                'name' => 'required|string',
                'uid' => 'required|string|unique:users,uid,' . $user->id,
                'role' => 'required|numeric',
                'status' => 'required|numeric',
            ]);

            DB::beginTransaction();

            if ($request->password) {
                $password = bcrypt($request->password);
            } else {
                $password = $user->password;
            }

            $user->update([
                'username' => $request->username,
                'name' => $request->name,
                'password' => $password,
                'uid' => $request->uid,
                'is_active' => $request->status,
            ]);

            $user->syncRoles($request->role);

            DB::commit();

            return back()->with('success', "User {$user->username} berhasil diupdate");
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            $user->delete();

            DB::commit();

            return back()->with('success', "User {$user->username} berhasil didelete");
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }
}
