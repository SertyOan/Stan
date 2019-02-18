<?php
namespace App;

class Helpers {
    public static function checkParams($params, $field, $function) {
        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!is_string($field) || !preg_match('/^[a-z]+$/i', $field)) {
            throw new \InvalidArgumentException('Invalid field');
        }

        if(!property_exists($params, $field)) {
            throw new \InvalidArgumentException('Parameter "categoryID" expected');
        }

        if(!function_exists($function)) {
            throw new \InvalidArgumentException('Invalid function');
        }

        if(!call_user_func($function, $params->{$field})) {
            throw new \InvalidArgumentException('Parameter "'.$field.'" is invalid');
        }
    }
}
