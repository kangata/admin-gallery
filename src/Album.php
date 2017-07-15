<?php

namespace QuetzalArc\Admin\Gallery;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    public function categories()
    {
        return $this->belongsToMany('QuetzalArc\Admin\Category\Category', 'album_category', 'album_id', 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany('QuetzalArc\Admin\Tag\Tag', 'album_tag', 'album_id', 'tag_id');
    }

    public function photos()
    {
        return $this->hasMany('QuetzalArc\Admin\Gallery\Photo', 'album_id', 'id');
    }

    public function getCoverAttribute($value)
    {
        if ($value) {
            return route('asset.photo', $value);
        } else {
            if ($this->photos->count() > 0) {
                return route('asset.photo', $this->photos()->first()->filename);
            } else {
                return url('vendor/quetzalarc/img/default.png');
            }
        }
    }
}
