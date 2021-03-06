<?php
namespace App\Model;

use Syra\MySQL\ModelObject;

class Subscription extends ModelObject {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Subscription';

	protected static
		$properties = Array(
			'id' => Array('class' => 'Integer'),
			'user' => Array('class' => '\\App\\Model\\User'),
			'category' => Array('class' => '\\App\\Model\\Category'),
            'owner' => Array('class' => 'Integer')
		);

    protected
        $id,
        $user,
        $category,
        $owner;
}
