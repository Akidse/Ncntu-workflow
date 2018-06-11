<?php
function folderSize($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : 0;
    }
    return $size;
}
Template::setTitle('Логи та статистика');
Template::setBackButtonUrl($router->url('/', 'admin'));

$countUsers = Database::query("SELECT COUNT(*) FROM `users`");
$countDocuments = Database::query("SELECT COUNT(*) FROM `departments_documents`");
$countDecrees = Database::query("SELECT COUNT(*) FROM `decrees`");
$countPrescriptions = Database::query("SELECT COUNT(*) FROM `prescriptions`");
$countFiles = count(scandir(PathManager::getRootDirectory().'/files/')) - 4;
$folderSize = round(folderSize(PathManager::getRootDirectory().'/files/')/1024/1024, 3);