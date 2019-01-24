<?php
namespace App;

use App\Model\User;
use App\DataRequest;

class Session {
    private static
        $instance;

    private
        $user;

    public static function get() {
        if(empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        $token = empty($_SERVER['HTTP_STAN_TOKEN']) ? '' : $_SERVER['HTTP_STAN_TOKEN'];

        $this->user = DataRequest::get('User')->withFields('id', 'nickname', 'email', 'role')
            ->where('', 'User', 'secretKey', '=', $token)
            ->mapAsObject();
    }

    public function __get($property) {
        switch($property) {
            case 'user':
                return $this->{$property};
            default:
                throw new InvalidArgumentException('Property does not exist');
        }
    }

    public function isIdentified() {
        return $this->user instanceof User;
    }
}
