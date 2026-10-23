<?php

function cleanInput($value)
{
    if (!is_string($value)) {
        return "";
    }

    $value = trim(strip_tags($value));

    $cleaned = preg_replace(
        '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
        '',
        $value
    );

    return $cleaned ?? "";
}


function appendJsonRecord($file, $record)
{
    $directory = dirname($file);

    if (
        !is_dir($directory) &&
        !mkdir($directory, 0750, true) &&
        !is_dir($directory)
    ) {
        return false;
    }

    $handle = @fopen($file, "c+");

    if ($handle === false) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    rewind($handle);
    $contents = stream_get_contents($handle);

    if ($contents === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    $records = [];

    if (trim($contents) !== "") {
        $decoded = json_decode($contents, true);

        if (
            !is_array($decoded) ||
            json_last_error() !== JSON_ERROR_NONE ||
            (
                count($decoded) > 0 &&
                array_keys($decoded) !== range(0, count($decoded) - 1)
            )
        ) {
            flock($handle, LOCK_UN);
            fclose($handle);
            return false;
        }

        $records = $decoded;
    }

    $records[] = $record;

    $json = json_encode(
        $records,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($json === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    $output = $json . PHP_EOL;

    rewind($handle);

    if (!ftruncate($handle, 0)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    $written = fwrite($handle, $output);

    $success = (
        $written === strlen($output) &&
        fflush($handle)
    );

    flock($handle, LOCK_UN);
    fclose($handle);

    return $success;
}


function showFormResult(
    $title,
    $message,
    $success,
    $backHref,
    $backText,
    $statusCode = 200
) {
    http_response_code($statusCode);

    $title = htmlspecialchars(
        $title,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );

    $message = htmlspecialchars(
        $message,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );

    $backHref = htmlspecialchars(
        $backHref,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );

    $backText = htmlspecialchars(
        $backText,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );

    $class = $success ? "success" : "error";
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= $title ?> - StudentHub</title>

       <link rel="stylesheet" href="/practical/STUDENT%20HUB/CSS/styles.css">
        <script src="/practical/STUDENT%20HUB/JAVASCRIPT/script.js" defer></script>
    </head>

    <body>
    <div class="page">

        <header class="site-header">
            <a href="/StudentHub/PAGES/index.html" class="logo">
                StudentHub
            </a>

            <nav class="nav">
               <a href="/practical/STUDENT%20HUB/PAGES/index.html">Home</a>
<a href="/practical/STUDENT%20HUB/PAGES/register.html">Register</a>
<a href="/practical/STUDENT%20HUB/PAGES/contact.html">Contact</a>
            </nav>
        </header>

        <main class="server-message-container">
            <section class="card server-message <?= $class ?>">

                <h1><?= $title ?></h1>

                <p><?= $message ?></p>

                <a href="<?= $backHref ?>" class="btn">
                    <?= $backText ?>
                </a>

            </section>
        </main>

        <footer>
            <p>© 2026 StudentHub</p>
        </footer>

    </div>
    </body>
    </html>

    <?php
}
?>