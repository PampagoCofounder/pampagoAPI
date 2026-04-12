<?php
class Database {
    
    /*
    private $host = "localhost" || "localhost";
    private $db   = "c2761701_pampadb" || "pampagodb";
    private $user = "c2761701_pampadb" || "root";
    private $pass = "VE61foweba" || "";*/
    
    
    private $host = "localhost";
    private $db = "c2761701_pampadb";
    private $user = "c2761701_pampadb";
    private $pass = "VE61foweba";

    /*
    private $host = "localhost";
    private $db   = "pampagodb";
    private $user =  "root";
    private $pass = "";*/
    
    public function connect() {
        return new PDO(
            "mysql:host={$this->host};dbname={$this->db};charset=utf8",
            $this->user,
            $this->pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}








?>