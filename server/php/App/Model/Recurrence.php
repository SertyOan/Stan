<?php
namespace App\Model;

use Syra\MySQL\Object;
use App\DataRequest;
use App\Model\Event;

class Recurrence extends Object {
    const
        DATABASE_CLASS = '\\App\\Database',
        DATABASE_SCHEMA = STAN_DATABASE_SCHEMA,
        DATABASE_TABLE = 'Recurrence',
        TYPE_DAILY = 'DA',
        TYPE_WEEKLY = 'WE',
        TYPE_MONTHLY = 'MO';

	protected static
		$properties = [
			'id' => ['class' => 'Integer'],
			'category' => ['class' => '\\App\\Model\\Category'],
            'from' => ['class' => 'Timestamp'],
            'to' => ['class' => 'Timestamp'],
            'timezone' => ['class' => 'String'],
            'hour' => ['class' => 'Integer'],
            'minute' => ['class' => 'Integer'],
            'duration' => ['class' => 'Integer'],
            'type' => ['class' => 'String'],
            'options' => ['class' => 'JSON'],
            'statuses' => ['class' => 'String']
		];

    protected
        $id,
        $category,
        $from,
        $to,
        $timezone,
        $hour,
        $minute,
        $duration, // NOTE stored in minutes
        $type,
        $options,
        $statuses;

    public function createEvents() {
        switch($this->type) {
            case self::TYPE_DAILY: $this->createDailyEvents(); break;
            case self::TYPE_WEEKLY: $this->createWeeklyEvents(); break;
            case self::TYPE_MONTHLY: $this->createMonthlyEvents(); break;
        }
    }

    private function createDailyEvents() {
        $day = date('Y-m-d '.$this->hour.':'.$this->minute.':00');
        $date = new \DateTime($day, new \DateTimeZone($this->timezone));

        for($i = 0; $i < 14; $i++) {
            $startAt = $date->getTimestamp();
            $endAt = $startAt + $this->duration * 60;

            $found = DataRequest::get('Event')->withFields('id')
                ->where('', 'Event', 'category', '=', $this->category->id)
                ->where('AND', 'Event', 'startAt', '=', $startAt)
                ->where('AND', 'Event', 'endAt', '=', $endAt)
                ->mapAsObject();
            
            if(empty($found)) {
                $event = new Event();
                $event->category = $this->category;
                $event->recurrence = $this;
                $event->startAt = $startAt;
                $event->endAt = $endAt;
                $event->statuses = $this->statuses;
                $event->save();
            }

            $date->modify('+1 day');
        }
    }

    private function createWeeklyEvents() {

    }

    private function createMonthlyEvents() {

    }
}
