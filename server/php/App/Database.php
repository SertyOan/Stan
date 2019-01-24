<?php
namespace App;

use Syra\DatabaseInterface;
use Syra\MySQL\Database as MySQLDatabase;

class Database implements DatabaseInterface {
    private static
        $writer,
        $reader;

    public static function getWriter() {
		if(is_null(self::$writer)) {
			self::$writer = new MySQLDatabase(STAN_DATABASE_HOSTNAME, STAN_DATABASE_USER, STAN_DATABASE_PASSWORD);
			self::$writer->connect();
        }

        return self::$writer;
    }

    public static function getReader() {
        if(is_null(self::$reader)) {
            self::$reader = self::getWriter();
        }

        return self::$reader;
    }
}
