<?php

namespace Phosphorum\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * Class Posts
 *
 * @package \Phosphorum\Models
 * @property \Phosphorum\Models\Categories     $category
 * @property \Phosphorum\Models\Users          $user
 * @property \Phosphorum\Models\PostsReplies[] $replies
 * @property \Phosphorum\Models\PostsViews[]   $views
 */
class Posts extends Model
{

    public $id;

    public $users_id;

    public $categories_id;

    public $title;

    public $slug;

    public $content;

    public $number_views;

    public $number_replies;

    public $sticked;

    public $modified_at;

    public $created_at;

    public $status;

    public function initialize()
    {
        $this->belongsTo(
             'users_id',
                 'Phosphorum\Models\Users',
                 'id',
                 array(
                     'alias'    => 'user',
                     'reusable' => true
                 )
        );

        $this->belongsTo(
             'categories_id',
                 'Phosphorum\Models\Categories',
                 'id',
                 array(
                     'alias'      => 'category',
                     'reusable'   => true,
                     'foreignKey' => array(
                         'message' => 'The category is not valid'
                     )
                 )
        );

        $this->hasMany(
             'id',
                 'Phosphorum\Models\PostsReplies',
                 'posts_id',
                 array(
                     'alias' => 'replies'
                 )
        );

        $this->hasMany(
             'id',
                 'Phosphorum\Models\PostsViews',
                 'posts_id',
                 array(
                     'alias' => 'views'
                 )
        );

    }

    /**
     *
     */
    public function beforeValidationOnCreate()
    {
        $this->number_views   = 0;
        $this->number_replies = 0;
        $this->sticked        = 'N';
        $this->status         = 'A';
    }

    /**
     * Create a posts-views logging the ipaddress where the post was created
     * This avoids that the same session counts as post view
     */
    public function beforeCreate()
    {
        $postView            = new PostsViews();
        $postView->ipaddress = $this->getDI()->getRequest()->getClientAddress();
        $this->views         = $postView;
        $this->created_at    = time();
        $this->modified_at   = time();

        // Decoda parsers
        $this->content = $this->getDI()->get('decoda')->reset($this->content)->parse();
    }

    /**
     *
     */
    public function afterCreate()
    {
        /**
         * Register a new activity
         */
        if ($this->id > 0) {

            /**
             * Register the activity
             */
            $activity           = new Activities();
            $activity->users_id = $this->users_id;
            $activity->posts_id = $this->id;
            $activity->type     = 'P';
            $activity->save();

            /**
             * Notify users that always want notifications
             */
            $notification           = new PostsNotifications();
            $notification->users_id = $this->users_id;
            $notification->posts_id = $this->id;
            $notification->save();

            /**
             * Notify users that always want notifications
             */
            foreach (Users::find('notifications = "Y"') as $user) {
                if ($this->users_id != $user->id) {
                    $notification           = new Notifications();
                    $notification->users_id = $user->id;
                    $notification->posts_id = $this->id;
                    $notification->type     = 'P';
                    $notification->save();
                }
            }

            /**
             * Update the total of posts related to a category
             */
            $this->category->number_posts++;
            $this->category->save();
        }
    }

    /**
     *
     */
    public function afterSave()
    {
        if ($this->id) {
            $viewCache = $this->getDI()->getViewCache();
            $viewCache->delete('post-' . $this->id);
        }
    }

    /**
     * Returns a W3C date to be used in the sitemap
     *
     * @return string
     */
    public function getUTCModifiedAt()
    {
        $modifiedAt = new \DateTime();
        $modifiedAt->setTimezone(new \DateTimeZone('UTC'));
        $modifiedAt->setTimestamp($this->modified_at);

        return $modifiedAt->format('Y-m-d\TH:i:s\Z');
    }
}
