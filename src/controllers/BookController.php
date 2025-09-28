<?php

class BookController 
{
    public function __construct(private BookRepository $repository)
    {   
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id)
    {
        $book = $this->repository->get($id);
        
        if (! $book) {
            http_response_code(404);
            echo json_encode(['message' => 'Book not found']);
            return;
        }

        switch ($method) {

            case 'GET':
                echo json_encode($book);
                break;

            case 'PATCH':
                $data = (array) json_decode(file_get_contents('php://input'), true);

                $errors = $this->getValidationErrors($data, false);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                if (isset($data['isbn']) && $this->repository->getByIsbn($data['isbn'], $id)) {
                    http_response_code(409);
                    echo json_encode(['error' => 'ISBN already exists']);
                    break;
                }

                $rows = $this->repository->update($book, $data);

                echo json_encode([
                    'message' => "Book $id updated",
                    'rows' => $rows
                ]);

                break;
            
            case 'DELETE':
                $rows = $this->repository->delete($id);

                echo json_encode([
                    'message' => "Book $id deleted",
                    'rows' => $rows
                ]);

                break;
            
            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
        }
    }

    private function processCollectionRequest(string $method)
    {
        switch ($method) {

            case "GET":
                echo json_encode($this->repository->getAll());
                break;
                
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                if (isset($data['isbn']) && $this->repository->getByIsbn($data['isbn'])) {
                    http_response_code(409);
                    echo json_encode(['error' => 'ISBN already exists']);
                    break;
                }
                
                $id = $this->repository->create($data);
                
                http_response_code(201);

                echo json_encode([
                    "message" => "Book created",
                    "id" => $id
                ]);

                break;
            
            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    private function getValidationErrors(array $data, bool $isNew = true): array
    {
        $errors = [];

        if ($isNew) {
            if (empty($data['title'])) {
                $errors[] = 'Title is required';
            }
            
            if (empty($data['price'])) {
                $errors[] = 'Price is required';
            }
        }

        if (isset($data['title'])) {
            $title = trim($data['title']);
            if (empty($title)) {
                $errors[] = 'Title cannot be empty';
            } elseif (strlen($title) > 200) {
                $errors[] = 'Title must be less than 200 characters';
            }
        }

        if (isset($data['author_id'])) {
            if (!is_numeric($data['author_id']) || $data['author_id'] <= 0) {
                $errors[] = 'Author ID must be a positive integer';
            }
        }

        if (isset($data['category_id'])) {
            if (!is_numeric($data['category_id']) || $data['category_id'] <= 0) {
                $errors[] = 'Category ID must be a positive integer';
            }
        }

        if (isset($data['isbn'])) {
            $isbn = trim($data['isbn']);
            if (!empty($isbn)) {
                if (strlen($isbn) > 20) {
                    $errors[] = 'ISBN must be less than 20 characters';
                } elseif (!preg_match('/^[0-9\-]+$/', $isbn)) {
                    $errors[] = 'ISBN can only contain numbers and hyphens';
                }
            }
        }

        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] < 0) {
                $errors[] = 'Price must be a non-negative number';
            } elseif ($data['price'] > 99999999.99) {
                $errors[] = 'Price is too large';
            }
        }

        if (isset($data['publication_year'])) {
            $current_year = (int)date('Y');
            if (!is_numeric($data['publication_year']) || 
                $data['publication_year'] < 1000 || 
                $data['publication_year'] > $current_year + 5) {
                $errors[] = "Publication year must be between 1000 and " . ($current_year + 5);
            }
        }

        if (isset($data['pages'])) {
            if (!is_numeric($data['pages']) || $data['pages'] <= 0 || $data['pages'] > 50000) {
                $errors[] = 'Pages must be a positive integer between 1 and 50000';
            }
        }

        return $errors;
    }
}