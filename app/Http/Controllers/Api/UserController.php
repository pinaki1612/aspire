<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function login()
    {
        $credentials = request(['email', 'password']);
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['status'=>'error','message' => 'Unauthorized', 'data'=>[]], Response::HTTP_UNAUTHORIZED);
        }
        return $this->respondWithToken($token);
    }

    public function profile()
    {
        $user = auth()->user();
        $user['role'] = $user->getRoleNames();
        return response()->json([
            'status' =>'success',
            'message' => 'User data',
            'data' => ['user'=>$user],
        ],Response::HTTP_OK);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['status'=>'success','message' => 'Successfully logged out','data'=>[]],Response::HTTP_OK);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' =>'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ],Response::HTTP_OK);
    }

    protected function registration(UserRegistrationRequest $request){
        $roleCustomer = Role::findByName('customer');
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'email_verified_at' => now(),
            'password' => Hash::make($request['password']),
            'remember_token' => Str::random(10),
            'status' => 'active'
        ]);
        $user->assignRole($roleCustomer);

        return response()->json([
            'status' => 'success',
            'data' => ['user'=>$user],
            'message' => 'User created successful',
        ],Response::HTTP_OK);
    }

    public function list(\Illuminate\Http\Request $request)
    {
        $search = 1;
        if(!empty($request["search"])){
            $search = "(users.name like '%".$request["search"]."%' or users.email like '%".$request["search"]."%')";
        }

        $user = User::whereRaw($search)
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => ['user'=>$user],
            'message' => 'loan list'
        ],Response::HTTP_OK);

    }
}
