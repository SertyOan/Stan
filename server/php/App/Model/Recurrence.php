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
        TYPE_DAY = 'DA',
        TYPE_WEEKDAY = 'WD',
        TYPE_WEEK = 'WE',
        TYPE_MONTH = 'MO';

	protected static
		$properties = [
			'id' => ['class' => 'Integer'],
			'category' => ['class' => '\\App\\Model\\Category'],
            'from' => ['class' => 'Timestamp'],
            'to' => ['class' => 'Timestamp'],
            'timezone' => ['class' => 'String'],
            'monthDay' => ['class' => 'Integer'],
            'weekDay' => ['class' => 'Integer'],
            'minute' => ['class' => 'Integer'],
            'duration' => ['class' => 'Integer'],
            'type' => ['class' => 'String'],
            'statuses' => ['class' => 'String']
		];

    protected
        $id,
        $category,
        $from,
        $to,
        $timezone,
        $monthDay,
        $weekDay,
        $hour,
        $minute,
        $duration, // NOTE stored in minutes
        $type,
        $statuses;

    public function createEvents() {
        switch($this->type) {
            case self::TYPE_DAY: $this->createDailyEvents([0, 1, 2, 3, 4, 5, 6]); break;
            case self::TYPE_WEEKDAY: $this->createDailyEvents([1, 2, 3, 4, 5]); break;
            case self::TYPE_WEEK: $this->createWeeklyEvents(); break;
            case self::TYPE_MONTH: $this->createMonthlyEvents(); break;
        }
    }

    private function createDailyEvents($weekDays) {
        $this->createTypedEvents('w', $weekDays);
    }

    private function createWeeklyEvents() {
        $this->createTypedEvents('w', [$this->weekDay]);
    }

    private function createMonthlyEvents() {
        $this->createTypedEvents('j', [$this->monthDay]);
    }

    private function createTypedEvents($dateType, $allowedValues) {
        $day = date('Y-m-d '.$this->hour.':'.$this->minute.':00');
        $date = new \DateTime($day, new \DateTimeZone($this->timezone));

        for($i = 0; $i < 14; $i++) {
            $startAt = $date->getTimestamp();
            $endAt = $startAt + $this->duration * 60;
            $weekDay = (Integer) date('w', $startAt);

            if(in_array($weekDay, $weekDays)) {
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
            }

            $date->modify('+1 day');
        }
    }
}
