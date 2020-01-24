<?php
/*
 * This file is part of the _3xAPI package.
 *
 * (c) Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _3xAPI\Request;

use _3xAPI\Exceptions\Exception;

/**
 * Класс для хранения аргументов
 *
 * @package _3xAPI\Request
 * @version 1.0.0
 * @author dotzero <mail@dotzero.ru>
 * @author Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ParamsBag
{
    /**
     * @var array Список значений параметров для авторизации
     */
    private $authParams = [];

    /**
     * Параметр авторизации
     * @var string Доменное имя сервера
     */
    private $domain = null;

    /**
     * Параметр авторизации
     * @var string логин
     */
    private $login = null;

    /**
     * Параметр авторизации
     * @var string Пароль
     */
    private $password = null;

    /**
     * @var array Список значений GET параметров
     */
    private $getParams = [];

    /**
     * @var array Список значений POST параметров
     */
    private $postParams = [];

    /**
     * @var string|null Прокси сервер для отправки запроса
     */
    private $proxy = null;

    /**
     * @var stirng Имя файла для передачи
     */
    private $file = null;

    /**
     * @var resource Указадель на файл
     */
    private $fileHandle = null;

    /**
     * Добавление прокси сервера
     *
     * @param string $proxy Прокси сервер для отправки запроса
     * @see http://php.net/manual/ru/function.curl-setopt.php
     * @return $this
     */
    public function addProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Добавление значений параметров для авторизации
     *
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @param string $name Название параметра
     * @param mixed $value Значение параметра
     * @return $this
     */
    public function addAuth($name, $value)
    {
        if ($name == "login") {
            $this->login = $value;
        } elseif ($name == "password") {
            $this->password = $value;
        } elseif ($name == "domain") {
            $this->domain = $value;
        } else {
            $this->authParams[$name] = $value;
        }
        return $this;
    }

    /**
     * Получение параметра для авторизации по ключу или список параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @param string $name Название параметра
     * @return array|null Значение параметра или список параметров
     */
    public function getAuth($name = null)
    {
        if ($name !== null) {
            if ($name == "login") {
                return $this->login;
            } elseif ($name == "password") {
                return $this->password;
            } elseif ($name == "domain") {
                return $this->domain;
            } else {
                return isset($this->authParams[$name]) ? $this->authParams[$name] : null;
            }
        }

        return $this->authParams;
    }

    /**
     * Добавление значений GET параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @param string|array $name Название параметра
     * @param mixed $value Значение параметра
     * @return $this
     */
    public function addGet($name, $value = null)
    {
        if (is_array($name) && $value === null) {
            $this->getParams = array_merge($this->getParams, $name);
        } else {
            $this->getParams[$name] = $value;
        }

        return $this;
    }

    /**
     * Получение GET параметра по ключу или массив с GET параметрами
     *
     * @author dotzero <mail@dotzero.ru>
     * @param string $name Название параметра.
     * @return mixed|null Значение параметра или массив с GET параметрами. Если название параметране будет `null`, то
     * вернёт массив спаска массив с GET параметрами. Если параметр не найден, вернёт `null`.
     */
    public function getGet($name = null)
    {
        if ($name !== null) {
            return isset($this->getParams[$name]) ? $this->getParams[$name] : null;
        }

        return $this->getParams;
    }

    /**
     * Получение информации о наличии GET параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @return boolean имеются ли GET параметры
     */
    public function hasGet()
    {
        return count($this->getParams) ? true : false;
    }

    /**
     * Очистка всех GET параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @return $this
     */
    public function clearGet()
    {
        $this->getParams = [];

        return $this;
    }

    /**
     * Добавление значений POST параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @param string|array $name Название параметра
     * @param mixed $value Значение параметра
     * @return $this
     */
    public function addPost($name, $value = null)
    {
        if (is_array($name) && $value === null) {
            $this->postParams = array_merge($this->postParams, $name);
        } else {
            $this->postParams[$name] = $value;
        }

        return $this;
    }

    /**
     * Получение POST параметра по ключу или массив с POST параметрами
     *
     * @author dotzero <mail@dotzero.ru>
     * @param string $name Название параметра.
     * @return mixed|null Значение параметра или массив с POST параметрами. Если название параметране будет `null`, то
     * вернёт массив спаска массив с POST параметрами. Если параметр не найден, вернёт `null`.
     */
    public function getPost($name = null)
    {
        if ($name !== null) {
            return isset($this->postParams[$name]) ? $this->postParams[$name] : null;
        }

        return $this->postParams;
    }

    /**
     * Получение информации о наличии POST параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @return boolean имеются ли POST параметры
     */
    public function hasPost()
    {
        return count($this->postParams) ? true : false;
    }

    /**
     * Очистка всех POST параметров
     *
     * @author dotzero <mail@dotzero.ru>
     * @return $this
     */
    public function clearPost()
    {
        $this->postParams = [];

        return $this;
    }

    /**
     * Получить прокси сервер для отправки запроса
     *
     * @author dotzero <mail@dotzero.ru>
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Получение информации о необходимости использования прокси сервера
     *
     * @author dotzero <mail@dotzero.ru>
     * @return bool
     */
    public function hasProxy()
    {
        return is_string($this->proxy);
    }

    /**
     * Определение файла для отправки (возможно устаноаить только 1 файл для отправки).
     * Исключение вызываются если файл не найден или недоступен для чтения.
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @param string $filename Имя файла
     * @return $this
     * @throws _3xAPI\Exceptions\Exception
     */
    public function setFile($filename)
    {
        if (!is_file($filename)) {
            throw new Exception("Файл $filename не найден");
        }
        if (false === $fileHandle = fopen($filename, "rb")) {
            throw new Exception("Не удалось открыть файл $filename");
        }
        fclose($fileHandle);
        $this->file = $filename;
        return $this;
    }

    /**
     * Получить имя файла
     *
     * @return string Имя файла
     */
    public function getFilename()
    {
        return $this->file;
    }

    /**
     * Получить информацию о файле
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @return array Массив с информацией о файле: `file name`, `file size`, или `false`, если файл не установлен.
     */
    public function getFileParams()
    {
        if (!is_null($this->file)) {
            return array("file name" => $this->file, "file size" => filesize($this->file));
        }

        return false;
    }

    /**
     * Возвращает размер файла
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @return int Размер указанного файла в байтах или `false` (и генерирует ошибку уровня E_WARNING) в случае возникновения ошибки.
     */
    public function getFileSize()
    {
        return filesize($this->file);
    }

    /**
     * Открывает файл для чтения в бинарном режиме
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @return resource Указатель на файл в случае успешной работы, или `false` в случае возникновения ошибки
     */
    public function openFile()
    {
        $this->fileHandle = fopen($this->file, "rb");
        return $this->fileHandle;
    }

    /**
     * Закрывает открытый дескриптор файла
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @return boolean `true` в случае успешного завершения или `false` в случае возникновения ошибки.
     */
    public function closeFile()
    {
        return fclose($this->fileHandle);
    }

    /**
     * Получение информации о наличии файла для передачи
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @return boolean имеется ли файл для передачи
     */
    public function hasFile()
    {
        return !is_null($this->file);
    }
}