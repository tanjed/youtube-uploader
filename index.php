<?php
require_once __DIR__ . '/YouTube.php';
try {
    echo 'WELCOME';
    (new YouTube())->upload_video_to_youtube($_SERVER["DOCUMENT_ROOT"].'/video/video.mp4','video','DESCRIPTION','TAGS');
}catch (Exception $exception)
{
    print_r($exception);
}
