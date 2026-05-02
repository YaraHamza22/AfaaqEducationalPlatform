<?php

namespace Modules\UserMangementModule\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
// use Modules\UserMangementModule\Database\Factories\Builders/UserBuilderFactory;

class UserBuilder extends Builder
{
     public function byRole(array $roles):self
    {
        return $this->role($roles);
    }

    public function search(string $term)
    {
        return $this->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%");
        });
    }

    public function gender(string $gender)
    {
        return $this->where('gender',$gender);
    }

    public function filters(array $filters): self
    {
        return $this->when($filters['term'] ?? null, function ($query, $term) {
            $query->search($term);
        })->when($filters['gender'] ?? null, function ($query, $gender) {
            $query->gender($gender);
        });
    }
}