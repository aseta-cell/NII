<?php

require_once "config.php";

header("Content-Type: application/json; charset=UTF-8");

$result = $conn->query("

    SELECT
        id,
        type,
        title,
        description,
        content,
        media_url,
        thumbnail_url,
        published_at

    FROM editorial_content

    WHERE status = 'Published'

    ORDER BY published_at DESC

");

$content = [];

while ($row = $result->fetch_assoc()) {

    $content[] = $row;

}

echo json_encode(
    $content,
    JSON_UNESCAPED_UNICODE
);