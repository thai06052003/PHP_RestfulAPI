<?php

namespace App\Middlewares;

use App\Models\BlacklistToken;
use Exception;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use System\Core\Auth;
use Pecee\Http\Request;
use Pecee\Http\Middleware\IMiddleware;

class AuthMiddleware implements IMiddleware
{
    /* 
    kiểm tra theo hệ thống hoặc kiểm tra thoe User thông qua $apiKeyStatus nếu: 
    - $apiKeyStatus = true: kiểm tra theo api-key User
    - $apiKeyStatus = false: kiểm tra theo api-key hệ thống
    */
    public function handle($request): void
    {
        $authorization = $request->getHeader('Authorization');
        if (!$authorization) {
            errorResponse(401, 'Unauthorize');
        } else {
            $token = trim(str_replace('Bearer', '', $authorization));
            try {
                $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

                // Kiểm tra token có trong Black lít hay không
                $blacklist = (new BlacklistToken())->find($token, 'token');
                if ($blacklist) {
                    throw new Exception('Token is Blacklist');
                }

                $userId = $decoded->sub;
                $userModel = new User;
                $user = $userModel->getOne($userId);
                if (!$user) {
                    errorResponse(404, 'User not found');
                } else if ($user->status == 0) {
                    errorResponse(404, 'User Block');
                } else {
                    $user->token = $token;
                    $user->expire = date('Y-m-d H:i:s', $decoded->exp);
                    Auth::setUser($user);
                }
            } catch (Exception $e) {
                errorResponse(401, 'Unauthorize');
            }
        }
    }
}
