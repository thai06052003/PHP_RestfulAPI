# Bắt đầu với dự án PHP Resful API

Chào mừng bạn đến với tài liệu hướng dự án PHP RESTful API. Tài liệu này sẽ giúp bạn hiểu rõ hơn về cách xây dựng và triển khai API trong PHP.

## 🤩 Giới thiệu về RESTful API

RESTFUL API là một kiến trúc phần mềm được xây dựng trên mô hình lập trình MVC cho phép các hệ thống khác nhau giao tiếp với nhau thông qua các yêu cầu HTTP.

# 🔥 Các tính năng chính của dự án

## User: CURD, tìm kiếm, lấy danh sách bài học, định dạng đầu ra của User

- Hiển thị danh sách User: Method (GET)

```php
Route::get('/users', 'UserController@index');
```

Lấy ra danh sách tất cả user hoặc theo điều kiện đề ra.

```php
$sort = input('sort') ?? 'id';
$order = input('order') ?? 'asc';
$status = input('status');
$query = input('query');
$page = input('page') ?? 1;
$limit = input('limit');
```

- Tìm kiếm User: Method (GET)

Truyền vào id và lấy ra User tìm kiếm

```php
Route::get('/users/{id}', 'UserController@find');
```

Lấy ra thông tin User bạn mong muốn thông qua user_id

- Thêm User : Method (GET)

```php
Route::post('/users', 'UserController@store');
```

Thêm vào 1 User, nếu thông tin user không đủ hoặc lỗi sẽ hiển thị validate

```php
$validator->setMessages([
            'required' => ':attribute bắt buộc phải nhập',
            'email:email' => ':attribute phải đúng định dạng',
            'min' => ':attribute phải từ :min ký tự',
        ]);
        $validation = $validator->make(input()->all(), [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                function ($email) {
                    $user = new User();
                    if ($user->existEmail(($email))) {
                        return ":attribute đã tồn tại trên hệ thống";
                    }
                    return true;
                }
            ],
            'password' => 'required|min:6',
            'status' => [function ($value) {
                if ($value == 'true' || $value == 'false' || is_bool($value)) {
                    return true;
                }
                return ':attribute không hợp lệ';
            }],
        ]);
        $validation->setAliases([
            'name' => 'Tên',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'status' => 'Trạng thái',
        ]);
```

- Cập nhật User (PATCH)

```php
Route::patch('/users/{id}', 'UserController@update');
```

Chỉ cập nhật lại thông tin User và giữ lại những thông tin không cập nhật

- Cập nhật User (PUT)

```php
Route::put('/users/{id}', 'UserController@update');
```

Cập nhật lại thông tin User và xóa những thông tin không cập nhật

- Xóa 1 User

```php
Route::delete('/users/{id}', 'UserController@delete');
```

- Xóa những User được chọn:

```php
Route::delete('/users', 'UserController@deletes');
```

- Lấy ra danh sách khoa học: Cần đăng nhập

```php
Route::get('/my-courses', 'UserController@courses')
```

- Định dạng đầu ra của User: Usertransformer

Chuyển định dạng các key đầu ra của mảng từ định dạng Snake Case trong DB chuyển thành định dạng Camel Case

## Auth: login, logout, refresh token, xem hồ sơ, sửa hồ sơ

- Login (POST)

```php
Route::post('/auth/login', 'AuthController@login');
```

Sử dụng email và password để đăng nhập vào hệ thống và sẽ được cấp 1 Access Token  có hiệu lực trong 1 giờ và sẽ được cấp lại. Trong 1 tuần phải đăng nhập lại từ đầu

- Logout (POST): cần đăng nhập

```php
Route::post('/auth/logout', 'AuthController@logout');
```

Đăng xuất và đưa Access Token của tài khoản vừa mới đăng xuất vào Black List Token để Hacker không thể dùng token đó đăng nhập vào hệ thống

- Refresh token (POST)

```php
Route::post('/auth/refresh-token', 'AuthController@refresh');
```

Kiểm tra xem Refresh Token đã đến hạn chưa

Nếu đã đến hạn: cấp lại Refresh Token và Access Token

Nếu đã chưa đến hạn: cấp lại Access Token

- Xem hồ sơ (PATCH): Cần đăng nhập

```php
Route::patch('/auth/profile', 'AuthController@updateProfile');
```

- Sửa hồ sơ: Method (POST): Cần đăng nhập

```php
Route::post('/auth/profile', 'AuthController@updateProfile');
```

Sửa hồ sơ, có validate

```php
$validator->setMessages([
            'required' => ':attribute bắt buộc phải nhập',
            'email:email' => ':attribute phải đúng định dạng',
            'min' => ':attribute phải từ :min ký tự',
        ]);
        $rules = [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                function ($email) use ($id) {
                    $user = new User();
                    if ($user->existEmail($email, $id)) {
                        return ":attribute đã tồn tại trên hệ thống";
                    }
                    return true;
                }
            ],
        ];

        $validation = $validator->make(input()->all(), $rules);
        $validation->setAliases([
            'name' => 'Tên',
            'email' => 'Email',
            'status' => 'Trạng thái',
        ]);
        $validation->validate();
        if ($validation->fails()) {
            $errors = $validation->errors();
            return errorResponse(
                status: 400,
                message: 'Bad request',
                errors: $errors->firstOfAll(),
            );
        }
        $data = [
            'name' => input('name'),
            'email' => input('email'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
```

1. Middleware: kiểm tra token, giới hạn Request
- Kiểm tra token: lấy ra api của User và kiểm tra xem có nằm trong Black List không nếu không lấy ra User đăng nhập hiện tại và cho phép đăng nhập vào tài khoản
- Giới hạn Request: cấu hình 10 request trong 1 phút nếu vượt quá sẽ hiển thị thông báo “Rate Limit”

# 🛠️ Yêu cầu kỹ thuật

- MySQL/PHPAdmin
- Composer (PHP dependency manager)
- Postman hoặc công cụ test API tương tự

# 🏗️ Cấu trúc

Dự án được xây dựng theo kiến trúc MVC, cho phép các hệ thống khác nhau giao tiếp thông qua các yêu cầu HTTP

```php
Route::get('/', function() {
    return '<h1>API Refrence</h1>';
});

Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'v1', 'namespace' => '\App\Controllers\V1'], function () {
        Route::get('/users', 'UserController@index');
        Route::get('/users/{id}', 'UserController@find');
        Route::post('/users', 'UserController@store');
        Route::patch('/users/{id}', 'UserController@update');
        Route::put('/users/{id}', 'UserController@update');
        Route::delete('/users/{id}', 'UserController@delete');
        Route::delete('/users', 'UserController@deletes');
        Route::post('/auth/login', 'AuthController@login');
        Route::post('/auth/refresh-token', 'AuthController@refresh');
        Route::group(['middleware' => AuthMiddleware::class], function() {
            Route::get('/auth/profile', 'AuthController@profile');
            Route::patch('/auth/profile', 'AuthController@updateProfile');
            Route::post('/auth/profile', 'AuthController@updateProfile');
            Route::get('/my-courses', 'UserController@courses');
            Route::post('/auth/logout', 'AuthController@logout');
        });
    });
});
```