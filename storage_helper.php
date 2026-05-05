<?php
function getStorageDir($mail = null, $storageType = null) {
    if ($mail === null || $storageType === null) {
        $notizCookie = isset($_COOKIE['Notiz']) ? $_COOKIE['Notiz'] : '';
        $settings = json_decode($notizCookie, true);
        if ($mail === null) $mail = isset($settings['mail']) ? $settings['mail'] : '';
        if ($storageType === null) $storageType = isset($settings['storage']) ? $settings['storage'] : 'local';
    }

    if ($storageType === 'local') {
        return ''; // Kein Verzeichnis auf dem Server für lokale Speicherung
    }

    $baseDir = 'global_storage';
    $userDir = '';

    if (!empty($mail) && strpos($mail, '@') !== false) {
        $userDirName = explode('@', $mail)[0];
        $userDir = $baseDir . '/' . $userDirName;
    }

    if ($userDir && !is_dir($userDir)) {
        mkdir($userDir, 0777, true);
    }

    return $userDir ?: '';
}

function getStoragePath($mail = null, $storageType = null) {
    $dir = getStorageDir($mail, $storageType);
    return ($dir === '') ? '' : $dir . '/tagebuch.json';
}
?>
