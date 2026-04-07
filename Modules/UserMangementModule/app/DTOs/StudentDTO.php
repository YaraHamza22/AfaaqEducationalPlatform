<?php
namespace Modules\UserMangementModule\DTOs;

readonly class StudentDTO
{
     public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $password,
        public ?string $phone,
        public ? string $address,
        public ?string $dateOfBirth,
        public ?string $gender,
        public ?string $educationLevel,
        public ? string $country,
        public ?string $bio,
        public ?string $specialization,
        public ?string $joined_at

    ) 
    {}

    /**
     * Summary of fromArray
     * @param array $data
     * @return StudentDTO
     */
    public static function fromArray(array $data): StudentDTO
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            gender: $data['gender'] ?? null,
            educationLevel: $data['educational_level'] ?? null,
            country: $data['country'] ?? null,
            bio: $data['bio'] ?? null,
            specialization: $data['specialization'] ?? null,
            joined_at: $data['joined_at'] ?? null,

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

    public function studentData()
    {
        $data = [
           'educational_level' => $this->educationLevel,
           'country' => $this->country,
           'bio' => $this->bio,
           'specialization' => $this->specialization,
           'joined_at' => $this->joined_at
        ];

        return array_filter($data, fn($value) => !is_null($value));
    }


}