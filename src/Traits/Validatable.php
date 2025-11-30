<?php
// src/Traits/Validatable.php

namespace App\Traits;

trait Validatable
{
    protected array $errors = [];
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
    
    protected function clearErrors(): void
    {
        $this->errors = [];
    }
    
    protected function validateRequired(string $field, $value, string $label): void
    {
        if ($value === null || $value === '' ) {
            $this->addError($field, "{$label} is required");
        }
    }
    
    protected function validateEmail(string $field, string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "Invalid email format");
        }
    }
    
    protected function validateMinLength(string $field, string $value, int $min, string $label): void
    {
        if (strlen($value) < $min) {
            $this->addError($field, "{$label} must be at least {$min} characters");
        }
    }
}
