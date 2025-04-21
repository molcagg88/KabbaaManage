<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserAuthService;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private UserAuthService $userAuthService;

    public function __construct()
    {
        $this->userAuthService = new UserAuthService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email')
            ])
            ->with(['attendances', 'profile'])
            ->select('users.*')
            ->paginate(request()->get('per_page', 15))
            ->appends(request()->query());

        return response()->json($results);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt(Str::random(10)) // Generate a random password
        ]);

        // Create profile
        $user->profile()->create([]);

        return response()->json(['success' => true], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load('profile');

        $user->load('branches');

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($request->has('email') && $user->email != $request->email) {
            $exists = User::where('email', '=', $request->email)->exists();
            if ($exists) {
                return response()->json(['message' => 'email must be unique'], 400);
            }
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        $profile = [
            'address',
            'city',
            'state',
            'country',
            'postcode',
            'newsletter'
        ];

        if ($request->hasAny($profile)) {
            $user->profile()->update($request->only([
                'address',
                'city',
                'state',
                'country',
                'postcode',
                'newsletter'
            ]));
        }

        $user->save();

        $user->refresh();

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->is_admin) {
            return response()->json(['message' => 'you are not allowed to do this to yourself'], 401);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Bulk delete multiple users
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        // Prevent deleting admin users
        $adminIds = User::whereIn('id', $request->ids)
            ->where('is_admin', true)
            ->pluck('id')
            ->toArray();

        if (!empty($adminIds)) {
            return response()->json([
                'message' => 'Cannot delete admin users',
                'admin_ids' => $adminIds
            ], 400);
        }

        User::whereIn('id', $request->ids)->delete();

        return response()->json(null, 204);
    }
}
