<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/functions.php";

$search = trim($_GET["search"] ?? "");
$course = trim($_GET["course"] ?? "");
$status = $_GET["status"] ?? "active";

if (!in_array($status, ["active", "deleted", "all"], true)) {
    $status = "active";
}

$where = [];
$params = [];
$types = "";

if ($status === "active") {
    $where[] = "is_deleted = 0";
} elseif ($status === "deleted") {
    $where[] = "is_deleted = 1";
}

if ($search !== "") {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $term = "%" . $search . "%";
    array_push($params, $term, $term, $term, $term);
    $types .= "ssss";
}

if ($course !== "") {
    $where[] = "course = ?";
    $params[] = $course;
    $types .= "s";
}

$where_sql = $where ? " WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT id, first_name, last_name, email, phone, course,
               year_level, is_deleted, created_at
        FROM students" . $where_sql . "
        ORDER BY id ASC";

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=students.csv");

$output = fopen("php://output", "w");

fputcsv($output, [
    "ID",
    "First Name",
    "Last Name",
    "Email",
    "Phone",
    "Course",
    "Year",
    "Status",
    "Created At"
]);

while ($student = $result->fetch_assoc()) {
    fputcsv($output, [
        $student["id"],
        $student["first_name"],
        $student["last_name"],
        $student["email"],
        $student["phone"],
        $student["course"],
        $student["year_level"],
        $student["is_deleted"] ? "Deleted" : "Active",
        $student["created_at"]
    ]);
}

fclose($output);
exit;