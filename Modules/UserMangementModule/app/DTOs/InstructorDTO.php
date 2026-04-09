<?php
namespace Modules\UserMangementModule\DTOs;
use Illuminate\Http\UploadedFile;

readonly class InstructorDTO{
      public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $password,
        public ?string $phone,
        public ? string $address,
        public ?string $dateOfBirth,
        public ?string $gender,
        public ?string $bio,
        public ?string $specialization,
        public ?int $yearsOfExperience,
        public ?UploadedFile $avatar = null,

    ) 
    {}

    /**
     * Summary of fromArray
     * @param array $data
     * @return InstructorDTO   
     */
    public static function fromArray(array $data): InstructorDTO                 
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            gender: $data['gender'] ?? null,
            bio: $data['bio'] ?? null,
            specialization: $data['specialization'] ?? null,
            yearsOfExperience: $data['years_of_experience'] ?? null,
            avatar: $data['avatar'] ?? null 

        ); 
    }

    /**
     * Summary of userData
     * @return array<string|null>
     */
    public function userData()
    {
        $data = [
           'name' => $this->name,
           'email' => $this->email,
           'password' => $this->password,
           'phone' => $this->phone,
           'address' => $this->address,
           'date_of_birth' => $this->dateOfBirth,
           'gender' => $this->gender
        ];
        return array_filter($data, fn($value) => !is_null($value));
    }

    /**
     * Summary of instructorData
     * @return array{bio: string|null, specialization: string|null, years_of_experience: int|null}
     */
    public function instructorData()
    {
        $data = [
           'bio' => $this->bio,
           'specialization' => $this->specialization,
           'years_of_experience' => $this->yearsOfExperience
        ];

        return array_filter($data, fn($value) => !is_null($value));
    }


}