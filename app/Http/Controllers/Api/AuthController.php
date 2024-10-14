<?php

 namespace App\Http\Controllers\Api;
 use App\Http\Controllers\Controller;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    /**
     * Log in a user and return an access token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Retrieve the credentials from the request
        $credentials = $request->only(['email', 'password']);
        
        // Attempt to authenticate the user using the provided credentials
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            // Return an error response if authentication fails
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Return the generated token on successful authentication
        return $this->respondWithToken($token);
    }
    
    /**
     * Return a JSON response with the access token details.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer', // Token type indicating it is a bearer token
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60 // Token expiration time in seconds
        ]);
    }
}
