<?php
namespace App\Models;

use System\Core\Model;

class User extends Model {
    // Lấy ra tất cả User
    function get ($options=[]) {
        extract($options);
        $users=  $this->db->select('id', 'name', 'email', 'status', 'created_at', 'updated_at')->table('users')->orderBy($sort, $order);
        if ($status == 'true' || $status == 'false') {
            $users->where('status', '=' , $status=='true' ? 1 : 0);
        }
        if ($query) {
            $users->where(function($builder) use ($query) {
                $builder->orWhere('name', 'like', "%$query%");
                $builder->orWhere('email', 'like', "%$query%");
            });
        }
        if ($limit && isset($offset)) {
            $users->limit($limit)->offset($offset);
        }
        return $users->all();
    }
    // Lấy ra 1 User
    function getOne($value, $type = 'id') {
        return $this->db
        ->select('id', 'name', 'email', 'password', 'status', 'created_at', 'updated_at')
        ->table('users')
        ->where($type, $value)
        ->first();
    }
    public function findUserByKey($apiKey) {
        return $this->db->table('users')->where('api_key', $apiKey)->first();
    }
    // Thêm User
    function create($data=[]) {
        $this->db->table('users')->insert($data);
        $id = $this->db->getLastId();
        $user = $this->db->table('users')->where('id', $id)->first();
        unset($user->password);
        return $user;
    }
    // Tìm kiếm những email trùng lặp
    function existEmail ($email, $id = 0) {
        $users = $this->db->table('users')->where('email', '=', $email);
        if ($id > 0) {
            $users->where('id', '!=', $id);
        }
        $count = $users->count();
        return $count > 0;
    }
    // Lấy ra số lượng User
    function getCount($options=[]) {
        extract($options);
        $users=  $this->db->select('id')->table('users')->orderBy($sort, $order);
        if ($status == 'true' || $status == 'false') {
            $users->where('status', '=' , $status=='true' ? 1 : 0);
        }
        if ($query) {
            $users->where(function($builder) use ($query) {
                $builder->orWhere('name', 'like', "%$query%");
                $builder->orWhere('email', 'like', "%$query%");
            });
        }
        return $users->count();
    }
    // Cập nhật User
    function update($id, $data=[]) {
        return $this->db->table('users')->where('id', '=', $id)->update($data);
    }
    // Xóa 1 User
    function delete($id) {
        return $this->db->table('users')->where('id', $id)->delete();
    }
    // Xóa nhiều User
    function deletes($ids) {
        return $this->db->table('users')->whereIn('id', $ids)->delete();
    }
    //
    function courses($userId) {
        return $this->db
        ->query("SELECT * 
        FROM courses INNER JOIN users_courses ON courses.id = users_courses.courses_id
        WHERE users_courses.user_id=:user_id", [
            'user_id' => $userId
        ]);
    }
}