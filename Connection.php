<?php

namespace Database;

use PDO;

class Connection {
    public static function getConnection() {
        return new PDO('mysql:host=db;port=3306;dbname=postagem_app', 'root', 'root', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}