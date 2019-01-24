<?php
namespace App\Model;

use Syra\MySQL\Object;

class Event extends Object {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Event';

	protected static
		$properties = Array(
			'id' => Array('class' => 'Integer'),
            'recurrence' => Array('class' => '\\App\\Model\\Recurrence'),
			'category' => Array('class' => '\\App\\Model\\Category'),
            'startAt' => Array('class' => 'Timestamp'),
            'endAt' => Array('class' => 'Timestamp'),
            'statuses' => Array('class' => 'String')
		);

    protected
        $id,
        $recurrence,
        $category,
        $startAt,
        $endAt,
        $statuses;
}
