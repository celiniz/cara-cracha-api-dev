<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BadgePhoto extends Model
{
	protected $fillable = ['badge_id', 'filename'];

    public function badge()
	{
		return $this->belongsTo('App/Badge', 'badge_id');
    }
    
    /**
	 * Get the full path of the image
	 * @return String
	*/
	public function getFullPath()
	{
		return config('uris.adm.badges') . '/' . $this->badge_id . '/' . $this->filename;
	}

	public function getPhotoUrl($size = 'thumb')
	{
		return Config::get('uris.badges.'.$size).'/'.$this->filename;
	}
}
