<?php

class UploadHelper
{
    public static function isValidUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        return filter_var(trim($url), FILTER_VALIDATE_URL) !== false;
    }
}
