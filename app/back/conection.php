<?php

  $host = "mysql.railway.internal";
  $dbname = "railway";
  $user = "root";
  $password = "skrLZnkjnAxoKQfeoqDXvNTQrxezJzGy";
  $port = 3306;

  $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

  try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
  }
?>
