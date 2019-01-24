<?php
namespace App\Service;

use App\Session;

class Application {
    public static function session() {
        $session = Session::get();
        $user = $session->user;

        return [
            'id' => empty($user) ? null : $user->id,
            'role' => empty($user) ? null : $user->role,
            'nickname' => empty($user) ? null : $user->nickname
        ];
    }
}
