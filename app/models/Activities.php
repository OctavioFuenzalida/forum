<?php

namespace Phosphorum\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;

/**
 * Class Activities
 *
 * @package Phosphorum\Models
 * @property \Phosphorum\Models\Users $user
 * @property \Phosphorum\Models\Posts $post
 */
class Activities extends Model
{

    public $id;

    public $users_id;

    public $type;

    public $posts_id;

    public $created_at;

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
             'posts_id',
                 'Phosphorum\Models\Posts',
                 'id',
                 array(
                     'alias'    => 'post',
                     'reusable' => true
                 )
        );

        $this->addBehavior(
             new Timestampable(array(
                 'beforeCreate' => array(
                     'field' => 'created_at'
                 )
             ))
        );
    }

}
