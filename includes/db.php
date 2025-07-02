<?php

const DB_HOST = 'localhost';
const DB_DATABASE = 'book_db';
const DB_USERNAME= 'root';
const DB_PASSWORD = '';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>