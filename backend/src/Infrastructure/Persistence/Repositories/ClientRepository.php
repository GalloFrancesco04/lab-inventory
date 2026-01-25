<?php

require_once __DIR__ . '/../../../Domain/Entities/Client.php';

use PDO;


class ClientRepository
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    //Create a new Client in the database
    public function create(Client $client): int
    {
        $stmt = $this->connection->prepare("
        INSERT INTO client (email, name, surname, state)
        VALUES (:email, :name, :surname, 1)");

        $stmt->execute([
            'email' => $client->getEmail(),
            'name'  => $client->getName(),
            'surname' => $client->getSurname(),
        ]);

        //Set the generated ID to the Client entity
        $client->setId((int) $this->connection->lastInsertId());
        return $client->getId();
    }

    //Update client's information
    public function update(Client $client) :void
    {
        $stmt = $this->connection->prepare("
            UPDATE client 
            SET email = :email, name = :name, surname = :surname
            WHERE id = :id
            ");

        $stmt->execute([
            'email' => $client->getEmail(),
            'name'  => $client->getName(),
            'surname' => $client->getSurname(),
            'id'    => $client->getId()
        ]);
    }

    
    public function blockClient(int $id) :void
    {
        $stmt = $this->connection->prepare("
        UPDATE client 
        SET state = 0
        WHERE id = :id");

        $stmt->execute([
            'id'    => $id
        ]);
    }

    public function unblockClient(int $id) :void
    {
        $stmt = $this->connection->prepare("
        UPDATE client 
        SET state = 1
        WHERE id = :id");

        $stmt->execute([
            'id'    => $id
        ]);
    }

    //Find Client by ID
    public function findById(int $id): ?Client 
    {
        $stmt = $this->connection->prepare("SELECT * FROM client WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToClient($row);
        }

        return null; // Client not found
    }

    //Find Client by email
    public function findByEmail(string $email): ?Client 
    {
        $stmt = $this->connection->prepare("SELECT * FROM client WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($row) {
            return $this->mapRowToClient($row);
        }

        return null; // Client not found
    }

    public function getAll(): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM client");
        $stmt->execute();
        
        $clients = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clients[] = $this->mapRowToClient($row);
        }

        return $clients;
    }

    public function findAllBlocked(): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM client WHERE state = 0");
        $stmt->execute();
        
        $clients = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clients[] = $this->mapRowToClient($row);
        }

        return $clients;
    }

    public function findAllUnlocked(): array 
    {
        $stmt = $this->connection->prepare("SELECT * FROM client WHERE state = 1");
        $stmt->execute();
        
        $clients = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clients[] = $this->mapRowToClient($row);
        }

        return $clients;
    }

    private function mapRowToClient(array $row): Client
    {
        $client = new Client(
            $row['email'],
            $row['name'],
            $row['surname'],
            $row['state']
        );
        $client->setId((int) $row['id']);
        return $client;
    }
}
?>