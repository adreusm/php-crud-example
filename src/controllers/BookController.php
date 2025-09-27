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

                // $errors = $this->getValidationErrors($data, false);
                
                // if ( ! empty($errors)) {
                //     http_response_code(422);
                //     echo json_encode(["errors" => $errors]);
                //     break;
                // }

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
                
                // $errors = $this->getValidationErrors($data);
                
                // if ( ! empty($errors)) {
                //     http_response_code(422);
                //     echo json_encode(["errors" => $errors]);
                //     break;
                // }
                
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
}