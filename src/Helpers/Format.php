<?php
/*
 * This file is part of the _3xAPI package.
 *
 * (c) Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace _3xAPI\Helpers;

/**
 * Хелпер для форматирования данных
 *
 * @package _3xAPI\Helpers
 * @version 1.0.0
 * @author dotzero <mail@dotzero.ru>
 * @author Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Format
{

    /**
     * Приведение snake_case к lowerCamelCase
     *
     * @param string $string Строка в `змеином_регистре`
     * @return string Строка `стильВерблюда`
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public static function lowerCamelCase($string)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }

    /**
     * Приведение snake_case к CamelCase
     *
     * @param string $string Строка в `змеином_регистре`
     * @return string Строка `СтильВерблюда`
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public static function upperCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Выделить из строки цифры
     *
     * @param string $string
     * @return string
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public static function onlyNumbers($string)
    {
        return preg_replace('/\D/', '', $string);
    }
}