<?php
namespace App\Model;

use Syra\MySQL\ModelObject;

class Message extends ModelObject {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Message';

	protected static
		$properties = [
			'id' => ['class' => 'Integer'],
            'category' => ['class' => '\\App\\Model\\Category'],
            'createdBy' => ['class' => '\\App\\Model\\User'],
            'createdAt' => ['class' => 'Timestamp'],
            'text' => ['class' => 'String']
		];

    protected
        $id,
        $category,
        $createdBy,
        $createdAt,
        $text;
}
