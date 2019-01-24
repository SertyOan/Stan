<?php
namespace App\Service;

use App\DataRequest;
use App\Database;
use App\Session;
use App\Model\Subscription;

class Subscriptions {
    public static function create($params = null) {
        $record = self::find($params);

        if(!empty($record)) {
            throw new \Exception('Subscription already exists');
        }

        $record = new Subscription();
        $record->category->id = $params->categoryID;
        $record->user = Session::get()->user;
        $record->role = 0;
        $record->save();
        Database::getWriter()->commit();
        return true;
    }

    public static function delete($params = null) {
        $record = self::find($params);

        if(empty($record)) {
            throw new \Exception('Subscription not found');
        }

        $record->delete();
        Database::getWriter()->commit();
        return true;
    }

    private static function find($params = null) {
        $session = Session::get();

        if($session->isIdentified() === false) {
            throw new \Exception('Not connected');
        }

        if(!is_object($params)) {
            throw new \InvalidArgumentException('Object expected');
        }

        if(!property_exists($params, 'categoryID') || !is_int($params->categoryID)) {
            throw new \InvalidArgumentException('Category ID expected');
        }

        $category = DataRequest::get('Category')->withFields('id')
            ->where('', 'Category', 'id', '=', $params->categoryID)
            ->mapAsObject();

        if(empty($category)) {
            throw new \Exception('Category not found');
        }

        $record = DataRequest::get('Subscription')->withFields('id')
            ->where('', 'Subscription', 'user', '=', $session->user->id)
            ->where('AND', 'Subscription', 'category', '=', $category->id)
            ->mapAsObject();
        return $record;
    }
}
