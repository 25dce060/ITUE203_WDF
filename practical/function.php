<?php

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

function postString($key)
{
    $value = $_POST[$key] ?? "";

    if (!is_string($value)) {
        return "";
    }

    return trim($value);
}