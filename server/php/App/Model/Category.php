<?php
namespace App\Model;

use Syra\MySQL\ModelObject;

class Category extends ModelObject {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Category';

	protected static
		$properties = Array(
			'id' => Array('class' => 'Integer'),
			'name' => Array('class' => 'String'),
            'color' => Array('class' => 'String')
		);

    protected
        $id,
        $name,
        $color;
}
