<?php
namespace App\Model;

use Syra\MySQL\Object;

class Subscription extends Object {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Subscription',
        ROLE_OWNER = 1;

	protected static
		$properties = Array(
			'id' => Array('class' => 'Integer'),
			'user' => Array('class' => '\\App\\Model\\User'),
			'category' => Array('class' => '\\App\\Model\\Category'),
            'role' => Array('class' => 'Integer')
		);

    protected
        $id,
        $user,
        $category,
        $role;
}
