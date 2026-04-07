<?php

namespace Modules\UserMangementModule\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
// use Modules\UserMangementModule\Database\Factories\Builders/InstructorBuilderFactory;

class InstructorBuilder extends Builder
{
    public function search(string $term)
    {
        return $this->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('specialization', 'LIKE', "%{$term}%");
        });
    }

    public function experience(int $years)
    {
        return $this->where('years_of_experience',$years);
    }

}