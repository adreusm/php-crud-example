<?php

class BookRepository 
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM books";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(string $id): array | false 
    {
        $sql = "SELECT * FROM books WHERE id = :book_id LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':book_id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByIsbn(string $isbn, ?string $excludeBookId = null): array | false 
    {
        $sql = "SELECT * FROM books WHERE isbn = :isbn";

        if ($excludeBookId) {
            $sql .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':isbn', $isbn);

        if ($excludeBookId) {
            $stmt->bindValue(":exclude_id", $excludeBookId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO books (title, author_id, category_id, isbn, price, publication_year, pages) 
                VALUES (:title, :author_id, :category_id, :isbn, :price, :publication_year, :pages)"
        ;

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':title', $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(':author_id', $data['author_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $data['category_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':isbn', $data['isbn'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':publication_year', $data['publication_year'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':pages', $data['pages'] ?? null, PDO::PARAM_INT);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function update(array $current, array $new): int
    {
        $sql = "UPDATE books SET title = :title, author_id = :author_id, 
                category_id = :category_id, isbn = :isbn, price = :price,
                publication_year = :publication_year, pages = :pages WHERE id = :id"
        ;

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':title', $new['title'] ?? $current['title'], PDO::PARAM_STR);
        $stmt->bindValue(':author_id', $new['author_id'] ?? $current['author_id'], PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $new['category_id'] ?? $current['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':isbn', $new['isbn'] ?? $current['isbn'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $new['price'] ?? $current['price'], PDO::PARAM_INT);
        $stmt->bindValue(':publication_year', $new['publication_year'] ?? $current['publication_year'], PDO::PARAM_INT);
        $stmt->bindValue(':pages', $new['pages'] ?? $current['pages'], PDO::PARAM_INT);
        
        $stmt->bindValue(':id', $current['id'], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = "DELETE FROM books WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}