<?php
namespace App;

use Syra\MySQL\Request;

class DataRequest extends Request {
    const
        DATABASE_CLASS = '\\App\\Database';

    protected function buildClassFromTable($table) {
        return '\\App\\Model\\'.$table;
    }
}
