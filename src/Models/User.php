<?php
// src/Models/User.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Interfaces\Authenticatable;
use App\Traits\Validatable;

/**
 * Abstract User
 * Di-extend oleh Student dan Instructor
 */
abstract class User extends Model implements Authenticatable
{
    use Validatable;

    protected string $email = '';
    protected string $password = '';
    protected string $name = '';
    protected string $phone = '';

    // Setiap turunan harus punya role
    abstract public function getRole(): string;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    protected function fill(array $data): void
    {
        $this->email = $data['email'] ?? $this->email;
        $this->name  = $data['name']  ?? $this->name;
        $this->phone = $data['phone'] ?? $this->phone;

        if (isset($data['password']) && $data['password'] !== '') {
            $this->setPassword($data['password']);
        }
    }

    // Enkapsulasi password (hash)
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    // Untuk hydrasi dari DB (hash sudah jadi)
    public function setHashedPassword(string $hash): void
    {
        $this->password = $hash;
    }

    public function authenticate(string $email, string $password): bool
    {
        return $this->email === $email && password_verify($password, $this->password);
    }

    public function hasPermission(string $permission): bool
    {
        // Default false, override di subclass kalau perlu
        return false;
    }

    public function validate(): bool
    {
        $this->clearErrors();

        $this->validateRequired('email', $this->email, 'Email');
        $this->validateEmail('email', $this->email);
        $this->validateRequired('name', $this->name, 'Name');
        $this->validateRequired('phone', $this->phone, 'Phone');

        return !$this->hasErrors();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'email'      => $this->email,
            'name'       => $this->name,
            'phone'      => $this->phone,
            'role'       => $this->getRole(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    // Tabel didefinisikan di subclass (Student/Instructor)
    protected static function getTableName(): string
    {
        return '';
    }

    protected function insert(): bool
    {
        return false;
    }

    protected function update(): bool
    {
        return false;
    }

    public function delete(): bool
    {
        return false;
    }
}
