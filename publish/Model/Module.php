<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'module';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'section', 'url', 'inactive'];

    public function scopeActive($query)
    {
        return $query->where('inactive', 0);
    }
    
}
