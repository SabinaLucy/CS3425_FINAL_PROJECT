<?php
session_start();

function connectDB() {
    $config = parse_ini_file("/local/my_web_files/slbimbi/db.ini"); 
    
    try {
        $dbh = new PDO($config['dsn'], $config['username'], $config['password']);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $dbh;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>