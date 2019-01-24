<?php
namespace App\Model;

use Syra\MySQL\Object;

class Attendee extends Object {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Attendee';

	protected static
		$properties = [
			'id' => ['class' => 'Integer'],
            'event' => ['class' => '\\App\\Model\\Event'],
			'createdBy' => ['class' => '\\App\\Model\\User'],
			'createdAt' => ['class' => 'Timestamp'],
            'status' => ['class' => 'String'],
            'guest' => ['class' => 'String']
		];

    protected
        $id,
        $event,
        $createdBy,
        $createdAt,
        $status,
        $guest;
}
