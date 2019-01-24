<?php
namespace App\Service;

use App\DataRequest;
use App\Database;
use App\Session;
use App\Model\Message;

class Messages {
    public static function search($params = null) {
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

        $request = DataRequest::get('Message')->withFields('id', 'createdAt', 'text')
            ->innerJoin('Category')->on('Message', 'category')->withFields('id', 'name', 'color')
            ->innerJoin('User')->on('Message', 'createdBy')->withFields('id', 'nickname')
            ->innerJoin('Subscription')->on('Category', 'id', 'category')->with('', 'user', '=', $session->user->id)
            ->orderDescBy('Message', 'createdAt');

        if($params->categoryID != -1) {
            $request->where('', 'Category', 'id', '=', $params->categoryID);
        }
        else {
            // TODO search only on subscribed categories
        }

        return $request->mapAsArrays();
    }

    public static function create($params = null) {
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

        if(!property_exists($params, 'text') || !is_string($params->text)) {
            throw new \InvalidArgumentException('Text expected');
        }
        
        $text = strip_tags($params->text);

        if(mb_strlen($text) < 3) {
            throw new \Exception('Text is too short');
        }

        $category = DataRequest::get('Category')->withFields('id')
            ->where('', 'Category', 'id', '=', $params->categoryID)
            ->mapAsObject();
        // TODO check user has access to it

        if(empty($category)) {
            throw new \InvalidArgumentException('Category not found');
        }

        $message = new Message();
        $message->category = $category;
        $message->createdBy = $session->user;
        $message->createdAt = new \DateTime();
        $message->text = $text;
        $message->save();
        Database::getWriter()->commit();
        return true;
    }
}
