<?php

namespace App\Controllers\V1;

use Exception;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use System\Core\Auth;
use App\Models\RefreshToken;
use App\Models\BlacklistToken;
use App\Transformers\UserTransform;
use App\Transformers\UserTransformer;

class AuthController
{
    public function login()
    {
        $email = input('email');
        $password = input('password');
        if (!$email || !$password) {
            return errorResponse(400, 'Vui lòng nhập email và mật khẩu');
        }

        $userModel = new User();
        $user = $userModel->getOne($email, 'email');
        // kiểm tra thông tin đăng nhập
        if (!$user) {
            return errorResponse(404, 'Tài khoản không tồn tại');
        }
        $passwordHash = $user->password;
        if (!password_verify($password, $passwordHash)) {
            return errorResponse(401, "Mật khẩu không chính xác");
        }

        // Tạo Token
        $payload = [
            'sub' => $user->id,
            'exp' => time() + env('JWT_EXPIRE'),
            'iat' => time()
        ];
        $accessToken = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
        $refreshToken = JWT::encode([
            'sub' => $user->id,
            'exp' => time() + env('JWT_REFRESH_EXPIRE'),
            'iat' => time()
        ], env('JWT_REFRESH_SECRET'), 'HS256');
        (new RefreshToken())->create(
            [
                'user_id' => $user->id,
                'refresh_token' => $refreshToken,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
        return successResponse(data: [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ]);
    }
    //
    public function profile()
    {
        return successResponse(200, Auth::user());
    }
    //
    public function refresh()
    {
        $refreshToken = input('refresh_token');
        if (!$refreshToken) {
            return errorResponse(404, 'Unauthorize');
        }
        try {
            $decoded = JWT::decode($refreshToken, new Key(env('JWT_REFRESH_SECRET'), 'HS256'));
            $refreshTokenModel = new RefreshToken();
            $token = $refreshTokenModel->find($refreshToken, 'refresh_token');
            var_dump($refreshToken);
            if (!$token) {
                throw new Exception('Token is valid');
            }
            $userId = $decoded->sub;

            $payload = [
                'sub' => $userId,
                'exp' => time() + env('JWT_EXPIRE'),
                'iat' => time()
            ];
            $accessToken = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
            return successResponse(data: [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken
            ]);
        } catch (Exception $e) {
            errorResponse(401, 'Unauthorize', $e->getMessage());
        }
    }
    //
    public function logout()
    {
        $authModel = new Auth;
        $token = Auth::user()->token;
        $expire = Auth::user()->expire;
        if ($token && $expire) {
            $blacklist = new BlacklistToken();
            try {
                $blacklist->create([
                    'token' => $token,
                    'expire' => $expire,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $userTransformer = new UserTransformer(Auth::user());
                return successResponse(data: $userTransformer);
            } catch (Exception $e) {
                return errorResponse(401, 'User logged out');
            }
        }
    }
}
