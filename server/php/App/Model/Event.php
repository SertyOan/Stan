<?php
namespace App\Model;

use Syra\MySQL\Object;

class Event extends Object {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Event';

	protected static
		$properties = [
			'id' => ['class' => 'Integer'],
            'recurrence' => ['class' => '\\App\\Model\\Recurrence'],
			'category' => ['class' => '\\App\\Model\\Category'],
            'startAt' => ['class' => 'Timestamp'],
            'endAt' => ['class' => 'Timestamp'],
            'cancelled' => ['class' => 'Integer'],
            'statuses' => ['class' => 'String']
		];

    protected
        $id,
        $recurrence,
        $category,
        $startAt,
        $endAt,
        $cancelled,
        $statuses;
}
