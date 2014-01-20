<?php

namespace Phosphorum\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * Class PostsReplies
 *
 * @package Phosphorum\Models
 * @property \Phosphorum\Models\Posts $post
 * @property $user \Phosphorum\Models\Users
 */
class PostsReplies extends Model
{

    public $id;

    public $posts_id;

    public $users_id;

    public $content;

    public $created_at;

    public $modified_at;

    public function initialize()
    {
        $this->belongsTo(
             'posts_id',
                 'Phosphorum\Models\Posts',
                 'id',
                 array(
                     'alias' => 'post'
                 )
        );

        $this->belongsTo(
             'users_id',
                 'Phosphorum\Models\Users',
                 'id',
                 array(
                     'alias' => 'user'
                 )
        );

        $params = array(
            'beforeCreate' => array(
                'field' => 'created_at'
            ),
            'beforeUpdate' => array(
                'field' => 'modified_at'
            )
        );
        $this->addBehavior(new Timestampable($params));
    }

    public function beforeSave()
    {
        $this->content = $this->getDI()->get('decoda')->reset($this->content)->parse();
    }

    /**
     *
     */
    public function afterCreate()
    {

        if ($this->id > 0) {

            $activity           = new Activities();
            $activity->users_id = $this->users_id;
            $activity->posts_id = $this->posts_id;
            $activity->type     = 'C';
            $activity->save();

            $toNotify = array();

            /**
             * Notify users that always want notifications
             */
            foreach (Users::find('notifications = "Y"') as $user) {
                if ($this->users_id != $user->id) {
                    $notification                   = new Notifications();
                    $notification->users_id         = $user->id;
                    $notification->posts_id         = $this->posts_id;
                    $notification->posts_replies_id = $this->id;
                    $notification->type             = 'C';
                    $notification->save();
                    $toNotify[$user->id] = true;
                }
            }

            /**
             * Register the user in the post's notifications
             */
            if (!isset($toNotify[$this->users_id])) {

                $parameters       = array(
                    'users_id = ?0 AND posts_id = ?1',
                    'bind' => array($this->users_id, $this->posts_id)
                );
                $hasNotifications = PostsNotifications::count($parameters);

                if (!$hasNotifications) {
                    $notification           = new PostsNotifications();
                    $notification->users_id = $this->users_id;
                    $notification->posts_id = $this->posts_id;
                    $notification->save();
                }
            }

            /**
             * Notify users that commented in the same post
             */
            $postsNotifications = PostsNotifications::findByPostsId($this->posts_id);
            foreach ($postsNotifications as $postNotification) {
                if (!isset($toNotify[$postNotification->users_id])) {
                    if ($postNotification->users_id != $this->users_id) {
                        $notification                   = new Notifications();
                        $notification->users_id         = $postNotification->users_id;
                        $notification->posts_id         = $this->posts_id;
                        $notification->posts_replies_id = $this->id;
                        $notification->type             = 'C';
                        $notification->save();
                        $toNotify[$postNotification->users_id] = true;
                    }
                }
            }

        }
    }

    /**
     *
     */
    public function afterSave()
    {
        if ($this->id) {
            $viewCache = $this->getDI()->getViewCache();
            $viewCache->delete('post-' . $this->posts_id);
        }
    }
}
