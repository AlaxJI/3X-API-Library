<?php
/**
 * Автозагрузка классов в пространстве имён `contactsWork`
 */
spl_autoload_register(
    function ($class) {
    $prefixes = array(
        'Test_3xAPI\\' => array(
            __DIR__,
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
