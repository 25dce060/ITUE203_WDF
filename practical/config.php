
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        "localhost",
        "root",
        "",
        "student_db"
    );

    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    exit("Database connection failed. Check XAMPP and import setup.sql.");
}
?>
