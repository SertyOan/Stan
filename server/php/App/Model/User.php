<?php
namespace App\Model;

use Syra\MySQL\ModelObject;

class User extends ModelObject {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'User',
        ROLE_ADMINISTRATOR = 1;

	protected static
		$properties = [
			'id' => ['class' => 'Integer'],
			'email' => ['class' => 'String'],
			'secretKey' => ['class' => 'String'],
            'nickname' => ['class' => 'String'],
            'role' => ['class' => 'Integer'],
            'lastSeen' => ['class' => 'Timestamp']
		];

    protected
        $id,
        $email,
        $secretKey,
        $nickname,
        $role,
        $lastSeen;
}
