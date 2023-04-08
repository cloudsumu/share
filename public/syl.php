<?php

$targetFolder = public_path('storage');
$linkFolder   = storage_path('app/public');
symlink($targetFolder, $linkFolder);
echo 'Symlink process successfully completed ' . $targetFolder . ' ' . $linkFolder;
