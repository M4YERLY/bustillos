<?php
class Conexion {
    private $host = 'localhost'; // Cambia esto si es necesario
    private $db = 'bustillos'; // Nombre de la base de datos
    private $user = 'root'; // Usuario de la base de datos
    private $pass = ''; // Contraseña de la base de datos
    private $charset = 'utf8mb4'; // Codificación
    private $pdo;

    public function conectar() {
        try {
            // Conectar a la base de datos utilizando PDO
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db};charset={$this->charset}", $this->user, $this->pass);
            // Establecer el modo de error para excepciones
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->pdo;
        } catch (PDOException $e) {
            // Manejar la excepción en caso de error
            echo "Error de conexión a la base de datos: " . $e->getMessage();
            exit();
        }
    }
}
