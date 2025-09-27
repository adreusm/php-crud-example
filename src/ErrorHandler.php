<?php

class ErrorHandler 
{
    private string $logPath;
    private bool $showErrors;

    public function __construct(string $logPath, bool $showErrors = false)
    {
        $this->logPath = $logPath;
        $this->showErrors = $showErrors; // false в production, true в development
    }

    public function handleException(Throwable $exception): void
    {
        $this->logError($exception);
        
        http_response_code(500);
        
        if ($this->showErrors) {

            echo json_encode([
                "error" => [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage(),
                    "file" => $exception->getFile(),
                    "line" => $exception->getLine(),
                    "type" => get_class($exception)
                ]
            ], JSON_PRETTY_PRINT);

        } else {
    
            echo json_encode([
                "error" => [
                    "message" => "Something went wrong. Please try again later."
                ]
            ]);
        }
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool 
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    private function logError(Throwable $exception): void
    {
        if (file_exists($this->logPath)) {
            $timestamp = (new DateTime())->format('Y-m-d H:i:s');
            
            $data = "[$timestamp] " . get_class($exception) . 
                    ": " . $exception->getMessage() . 
                    " in " . $exception->getFile() . 
                    " on line " . $exception->getLine() . 
                    "\nStack trace:\n" . $exception->getTraceAsString() . 
                    "\n" . str_repeat("-", 100) . "\n";
            
            file_put_contents($this->logPath, $data, FILE_APPEND | LOCK_EX);
        }
    }
}