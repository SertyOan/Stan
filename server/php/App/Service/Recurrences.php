<?php
namespace App\Service;

use App\Session;
use App\Database;
use App\DataRequest;
use App\Model\Recurrence;
use App\Model\User;
use App\Model\Subscription;

class Recurrences {
    public static function create($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        Helpers::checkParams($params, 'categoryID', 'is_int');
        Helpers::checkParams($params, 'type', 'is_string');
        Helpers::checkParams($params, 'hour', 'is_int');
        Helpers::checkParams($params, 'minute', 'is_int');
        Helpers::checkParams($params, 'duration', 'is_int');
        Helpers::checkParams($params, 'statuses', 'is_array');

        $statuses = [];

        foreach($params->statuses as $status) {
            if(is_string($status)) {
                $status = strip_tags($status);
                $status = trim($status);

                if(!empty($status)) {
                    $statuses[] = $status;
                }
            }
        }

        if(sizeof($statuses) < 1) {
            throw new \Exception('Not enough statuses');
        }


        $request = DataRequest::get('Category')->withFields('id', 'name', 'color')
            ->where('', 'Category', 'id', '=', $params->categoryID);

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            $request->innerJoin('Subscription')->on('Category', 'id', 'category')
                ->with('', 'user', '=', $session->user->id)
                ->with('AND', 'owner', '=', 1);
        }

        $category = $request->mapAsObject();

        if(empty($category)) {
            throw new \Exception('Groupe non trouvé');
        }

        if($params->hour < 0 || $params->hour > 23) {
            throw new \Exception('Invalid hour');
        }

        if($params->minute < 0 || $params->minute > 59) {
            throw new \Exception('Invalid minute');
        }

        $recurrence = new Recurrence();
        $recurrence->category = $category;
        $recurrence->duration = $params->duration;
        $recurrence->hour = $params->hour;
        $recurrence->minute = $params->minute;
        $recurrence->timezone = 'Europe/Paris'; // TODO review

        switch($params->type) {
            case Recurrence::TYPE_DAY:
            case Recurrence::TYPE_WEEKDAY:
                break;
            case Recurrence::TYPE_WEEK:
                Helpers::checkParams($params, 'weekDay', 'is_int');
                $recurrence->weekDay = $params->weekDay % 7;
                break;
            case Recurrence::TYPE_MONTH:
                Helpers::checkParams($params, 'monthDay', 'is_int');
                $recurrence->monthDay = $params->monthDay % 31;
                break;
            default:
                throw new \InvalidArgumentException('Invalid type');
        }

        $recurrence->type = $params->type;
        $recurrence->statuses = implode('|', $statuses);
        $recurrence->save();
        Database::getWriter()->commit();
        return true;
    }

    public static function delete($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        Helpers::checkParams($params, 'recurrenceID', 'is_int');

        $request = DataRequest::get('Recurrence')->withFields('id')
            ->where('', 'Recurrence', 'id', '=', $params->recurrenceID);

        if(($session->user->role & User::ROLE_ADMINISTRATOR) === 0) {
            $request->innerJoin('Category')->on('Recurrence', 'category')
                ->innerJoin('Subscription')->on('Category', 'id', 'category')
                    ->with('', 'user', '=', $session->user->id)
                    ->with('AND', 'owner', '=', 1);
        }

        $recurrence = $request->mapAsObject();

        if(empty($recurrence)) {
            throw new \Exception('Récurrence non trouvée');
        }

        $recurrence->delete();
        Database::getWriter()->commit();
        return true;
    }
}
