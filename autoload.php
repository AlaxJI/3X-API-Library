<?php
/**
 * Автозагрузка классов в пространстве имён `contactsWork`
 */
spl_autoload_register(
    function ($class) {
    $prefixes = array(
        '_3xAPI\\' => array(
            __DIR__.'/src',
            __DIR__.'/test',
            __DIR__.'/example',
        ),
    );
    foreach ($prefixes as $prefix => $dirs) {
        $prefix_len = mb_strlen($prefix);
        if (mb_strpos($class, $prefix) !== 0) {
            continue;
        }
        $class = mb_substr($class, $prefix_len);
        $part  = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
        foreach ($dirs as $dir) {
            $dir  = str_replace('/', DIRECTORY_SEPARATOR, $dir);
            $file = $dir.DIRECTORY_SEPARATOR.$part;
            if (is_readable($file)) {
                require $file;

                return;
            }
        }
    }
}
);

require_once 'vendor/autoload.php';