<?php

namespace Modules\UserMangementModule\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\UserMangementModule\Models\Builders\InstructorBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\UserMangementModule\Database\Factories\InstructorFactory;

class Instructor extends Model implements HasMedia
{
    use HasFactory, SoftDeletes , InteractsWithMedia,Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'specialization',
        'bio',
        'years_of_experience'
    ];

    protected function casts(): array
    {
        return [
            'years_of_experience' => 'integer'
        ];
    }

    public function newEloquentBuilder($query): InstructorBuilder
    {
        return new InstructorBuilder($query);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cv')
             ->acceptsMimeTypes(['application/pdf'])
             ->singleFile();
    }
}
