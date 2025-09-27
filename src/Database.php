<?php

class Database 
{
    private $dbhost;
    private $dbport;
    private $dbname;
    private $dbuser;
    private $dbpass;

    private $connection = null;
    private ErrorHandler $errorHandler;

    public function __construct($dbconfig, $errorHandler)
    {
        $this->dbhost = $dbconfig['dbhost'];
        $this->dbport = $dbconfig['dbport'];
        $this->dbname = $dbconfig['dbname'];
        $this->dbuser = $dbconfig['dbuser'];
        $this->dbpass = $dbconfig['dbpass'];

        $this->errorHandler = $errorHandler;
    }

    public function connect(): PDO
    {

        if ($this->connection !== null) {
            return $this->connection;
        } 

        $this->connection = new PDO(
            "mysql:host={$this->dbhost};port={$this->dbport};dbname={$this->dbname}",
            $this->dbuser,
            $this->dbpass
        );

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $this->connection;
        
    }
}