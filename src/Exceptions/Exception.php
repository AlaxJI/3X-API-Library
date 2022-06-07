<?php

/*
 * This file is part of the _3xAPI package.
 *
 * (c) Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace _3xAPI\Exceptions;

/**
 * Класс для исключений API
 *
 * @package _3xAPI
 * @version 1.0.0
 * @author dotzero <mail@dotzero.ru>
 * @author Alexei Dubrovski <alaxji@gmail.com>
 */
class Exception extends \Exception
{
    /**
     * @var array Справочник ошибок и ответов API
     * @author dotzero <mail@dotzero.ru>
     */
    protected $errors = [
    ];

    /**
     * Exception constructor
     *
     * @param null|string $message Сообщения исключения
     * @param int $code Код исключения
     * @author dotzero <mail@dotzero.ru>
     */
    public function __construct($message = null, $code = 0)
    {
        if (isset($this->errors[$code])) {
            $message = $this->errors[$code];
        }

        parent::__construct($message, $code);
    }
}
