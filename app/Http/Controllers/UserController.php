<?php

namespace App\Http\Controllers;
    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * UserController constructor.
     * Apply permission middleware for various actions.
     */
    public function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a paginated listing of users along with their roles.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Eager load roles with the users
        $data = User::with('roles')->latest()->paginate(5);

        // Map through the users to include role names and guard names
        $data->getCollection()->transform(function ($user) {
            $user->roles_info = $user->roles->map(function ($role) {
                return [
                    'role_name' => $role->name,
                    'guard_name' => $role->guard_name,
                ];
            });
            return $user;
        });

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create(): View
    {
        // Fetch all roles to assign to the user
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming request data
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required|array' // Ensure roles are provided as an array
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']); // Hash the password

        // Create the user
        $user = User::create($input);
        $this->assignRoles($user, $request->input('roles')); // Assign roles to the user

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        // Fetch the user with their roles
        $data = User::with('roles')->findOrFail($id);
    
        // Map through the user's roles to include role names and guard names
        $data->roles_info = $data->roles->map(function ($role) {
            return [
                'role_name' => $role->name,
                'guard_name' => $role->guard_name,
            ];
        });
    
        return view('users.show', compact('data'));
    }
    

    /**
     * Show the form for editing the specified user.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $user = User::findOrFail($id); // Use findOrFail to handle not found cases
        $roles = Role::pluck('name', 'name')->all(); // Fetch all roles
        $userRole = $user->roles->pluck('name', 'name')->all(); // Get the user's roles

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        // Validate the incoming request data
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|same:confirm-password',
            'roles' => 'required|array' // Ensure roles are provided as an array
        ]);

        // Retrieve all input data
        $input = $request->all();
        
        // Hash password if provided
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']); // Hash the password
        } else {
            // Exclude password from input if not provided
            $input = Arr::except($input, ['password']);
        }

        // Find the user by ID and update their information
        $user = User::findOrFail($id); // Use findOrFail to handle not found cases
        $user->update($input);

        // Clear existing roles for the user and assign new roles
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $this->assignRoles($user, $request->input('roles')); // Assign new roles

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        User::findOrFail($id)->delete(); // Use findOrFail to handle not found cases
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    /**
     * Assign roles to a user for both web and API guards.
     *
     * @param User $user
     * @param array $roles
     */
    private function assignRoles(User $user, array $roles): void
    {
        foreach ($roles as $roleName) {
            // Assign role for web guard (default)
            $user->assignRole($roleName);

            // Attach role for API guard if it exists
            $apiRole = Role::where('name', $roleName)->where('guard_name', 'api')->first();
            if ($apiRole) {
                $user->roles()->attach($apiRole); // Manually attaching the role for the API guard
            }
        }
    }
}
