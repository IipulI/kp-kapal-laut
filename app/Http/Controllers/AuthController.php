<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

// <-- Import User model
// <-- Import JWTAuth facade
// If using specific exceptions:
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenInvalidException;
// use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller {
    public function login(Request $request) {

        $login = $request->input('user');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $request->merge([$field => $login]);

        $credentials = $request->only($field, 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            // if (! $token = Auth::guard('api')->attempt($credentials)) { // Alternative using Auth facade
            return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $status = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // Get TTL from config (config/jwt.php, 'ttl' key) and convert to seconds
            'expires_in' => config('jwt.ttl') * 60,
            'user' => Auth::guard('api')->user() // Get authenticated user details
        ], $status);
    }
}


//class AuthController extends Controller
//{
//    /**
//     * Create a new AuthController instance.
//     * Note: In modern Laravel (11+), controller middleware is typically defined in routes.
//     * The protection for these auth routes is handled in routes/api.php.
//     *
//     * @return void
//     */
//    public function __construct()
//    {
//        // Middleware is applied via routes in routes/api.php for methods like logout, refresh, me.
//        // Example: Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
//        // This constructor can be left empty or used for other dependency injections if needed.
//    }
//
//    /**
//     * Get a JWT via given credentials.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function login(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'email' => 'required|email',
//            'password' => 'required|string|min:6',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json($validator->errors(), 422); // Unprocessable Entity
//        }
//
//        $credentials = $request->only('email', 'password');
//
//        if (! $token = JWTAuth::attempt($credentials)) {
//            // if (! $token = Auth::guard('api')->attempt($credentials)) { // Alternative using Auth facade
//            return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
//        }
//
//        return $this->respondWithToken($token);
//    }
//
//    /**
//     * Register a User.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function register(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|string|between:2,100',
//            'email' => 'required|string|email|max:100|unique:users',
//            'password' => 'required|string|confirmed|min:6',
//            'role' => 'sometimes|string|in:student,teacher', // Example validation
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json($validator->errors(), 400); // Bad Request
//        }
//
//        $user = User::create([
//            'name' => $request->name,
//            'email' => $request->email,
//            'password' => Hash::make($request->password),
//            'role' => $request->input('role', 'student'), // Default if not provided
//        ]);
//
//        // Optionally, log the user in directly and return a token
//        // $token = JWTAuth::fromUser($user);
//        // return $this->respondWithToken($token, 201); // 201 Created
//
//        return response()->json([
//            'message' => 'User successfully registered',
//            'user' => $user
//        ], 201); // 201 Created
//    }
//
//    /**
//     * Get the authenticated User.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function me()
//    {
//        try {
//            // Attempt to authenticate the user via the token in the request.
//            // This uses the 'api' guard which we configured to use the 'jwt' driver.
//            $user = Auth::guard('api')->user();
//
//            if (!$user) {
//                // This case might be redundant if middleware('auth:api') already handles unauthenticated access.
//                return response()->json(['error' => 'User not found or token invalid'], 404);
//            }
//        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
//            return response()->json(['error' => 'Token has expired'], 401);
//        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
//            return response()->json(['error' => 'Token is invalid'], 401);
//        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) { // Catch broader JWT exceptions
//            return response()->json(['error' => 'Token is absent or could not be parsed: ' . $e->getMessage()], 401);
//        } catch (\Exception $e) { // Catch any other unexpected errors
//            return response()->json(['error' => 'Could not authenticate user: ' . $e->getMessage()], 500);
//        }
//
//        return response()->json($user);
//    }
//
//    /**
//     * Log the user out (Invalidate the token).
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function logout()
//    {
//        try {
//            // Invalidate the token using the 'api' guard (which uses JWT driver)
//            Auth::guard('api')->logout();
//            // For tymon/jwt-auth specifically, if you need to ensure it's blacklisted:
//            // JWTAuth::invalidate(JWTAuth::getToken()); // This might be redundant if Auth::guard('api')->logout() handles it
//            return response()->json(['message' => 'Successfully logged out']);
//        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
//            // Something went wrong whilst attempting to encode/invalidate the token
//            return response()->json(['error' => 'Failed to logout, please try again. ' . $e->getMessage()], 500);
//        }  catch (\Exception $e) { // Catch any other unexpected errors
//            return response()->json(['error' => 'Could not logout user: ' . $e->getMessage()], 500);
//        }
//    }
//
//    /**
//     * Refresh a token.
//     * The 'auth:api' middleware ensures a valid token (even if expired but refreshable) is present.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function refresh()
//    {
//        try {
//            // Refresh the token using the 'api' guard
//            $newToken = Auth::guard('api')->refresh();
//            // $newToken = JWTAuth::refresh(JWTAuth::getToken()); // Alternative direct Tymon call
//            return $this->respondWithToken($newToken);
//        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
//            return response()->json(['error' => 'The token has been blacklisted'], 401);
//        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
//            return response()->json(['error' => 'Could not refresh token: ' . $e->getMessage()], 401);
//        } catch (\Exception $e) { // Catch any other unexpected errors
//            return response()->json(['error' => 'Could not refresh token: ' . $e->getMessage()], 500);
//        }
//    }
//
//    /**
//     * Get the token array structure.
//     *
//     * @param  string $token
//     * @return \Illuminate\Http\JsonResponse
//     */
//    protected function respondWithToken($token, $status = 200)
//    {
//        return response()->json([
//            'access_token' => $token,
//            'token_type' => 'bearer',
//            // Get TTL from config (config/jwt.php, 'ttl' key) and convert to seconds
//            'expires_in' => config('jwt.ttl') * 60,
//            'user' => Auth::guard('api')->user() // Get authenticated user details
//        ], $status);
//    }
//}
