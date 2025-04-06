<?php

//use PDO;
//use PDOException;

return [
    'db' => function () {
        $host = '127.0.0.1';
        $port = '3306'; // Eğer XAMPP'ta 3307 kullanıyorsan bunu 3307 yap
        $dbname = 'notes_api';
        $username = 'root';
        $password = '';

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }
    }
];
