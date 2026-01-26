<?php

class PasswordHasher{

    public function hashPassword(string $password): string
    {
        //PASSWORD_DEFAULT use Bcrypt algorithm to hash the password and generates a random salt 
        return password_hash($password, PASSWORD_DEFAULT); 
    }

    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}
?>