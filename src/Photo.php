<?php

namespace QuetzalArc\Admin\Gallery;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public function album()
    {
        return $this->belongsTo('QuetzalArc\Admin\Gallery\Album', 'album_id', 'id');
    }
}
