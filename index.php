<?php
require_once __DIR__ . '/YouTube.php';
$config = include(__DIR__.'/config.php');
try {
    $access_key = file_get_contents(__DIR__.'/token.txt');
    if(!$access_key){
        header('Location: /authorize.php');
    }
    else{

        $file = scandir($config['video_storage_path']);
        $file = array_pop($file);
        $file_path = $config['video_storage_path'].'/'.$file;
        $video_title = 'My YouTube Video ('.date('d/m/Y').')';
        (new YouTube())
            ->upload_video_to_youtube($file_path,$video_title,$config['video_description'],$config['video_tags']);
    };
}catch (Exception $exception){
    die($exception->getMessage());
}
