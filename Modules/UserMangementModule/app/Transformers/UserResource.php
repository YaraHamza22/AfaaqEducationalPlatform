<?php

namespace Modules\UserMangementModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gender' => $this->gender,
            'avatar' => [
                'original' => $this->getFirstMediaUrl('avatar'),
                'thumb'    => $this->getFirstMediaUrl('avatar', 'thumb'),
                'preview'  => $this->getFirstMediaUrl('avatar', 'preview'),
            ],

            'roles' => $this->whenLoaded
                ('roles', function() {
                    return $this->roles->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name'),
                        ];
                    });


                }),
        ];
    }

    
}