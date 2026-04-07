<?php

namespace Modules\UserMangementModule\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserMangementModule\Models\Builders\AuditorBuilder;
use Modules\UserMangementModule\Database\Factories\AuditorFactory;

class Auditor extends Model 
{
    use  SoftDeletes , Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'specialization',
        'bio',
        'years_of_experience'
    ];

    public function newEloquentBuilder($query): AuditorBuilder
    {
        return new  AuditorBuilder($query);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}