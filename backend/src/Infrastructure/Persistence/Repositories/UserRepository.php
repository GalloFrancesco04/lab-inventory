<?php

use PDO;

require_once __DIR__ . '/../../../Domain/Entities/User.php';

class UserRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    //Create a new User in the database
    public function create(User $user): void
    {
        $stmt = $this->connection->prepare("
        INSERT INTO user (email, pass,role)
        VALUES (:email,:pass, :role)
        ");

        $stmt->execute([
            'email' => $user->getEmail(),
            'pass'  => $user->getPass(), //hashed Password
            'role'  => $user->getRole() 
        ]);

        //Set the generated ID to the User entity
        $user->setId((int) $this->connection->lastInsertId());
    }

    //Find User(Admn or Technician) by ID
    public function findUserById(int $id): ?User 
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToUser($row);
        }

        return null; // User not found
    }

    public function findAdminById(int $id): ?User 
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE id = :id AND role = 1 ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToUser($row);
        }

        return null; // Admin not found
    }

    public function findTechnicianById(int $id): ?User 
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE id = :id AND role = 2 ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToUser($row);
        }

        return null; // Technician not found
    }

    //Find User(Admin or Technician) by Email
    public function findUserByEmail(string $email): ?User 
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToUser($row);
        }

        return null; // User not found
    }

    public function findAdminByEmail(string $email): ?User 
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE email = :email AND role = 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToUser($row);
        }

        return null; // Admin not found
    }

    public function findTechnicianByEmail(string $email): ?User 
    {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE email = :email AND role = 2");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToUser($row);
        }

        return null; // Technician not found
    }

    public function getAllTechnicians() : array {
        $stmt = $this->connection->prepare("SELECT * FROM user WHERE role = 2");
        $stmt->execute();
        
        $technicians = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $technicians[] = $this->mapRowToUser($row);
        }

        return $technicians;
    }

    //Convert a database row to a User entity
    private function mapRowToUser(array $row): User
    {
        $user = new User(
            $row['id'],
            $row['email'], 
            $row['pass'], 
            $row['role']
        );
        return $user;
    }

}
?>