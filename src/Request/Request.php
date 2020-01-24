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

use _3xAPI\Exceptions\NetworkException;
use _3xAPI\Exceptions\Exception;
use _3xAPI\Logger\Logger;
use DateTime;

/**
 * Класс отправляющий запросы к API используя cURL
 *
 * @package _3xAPI\Request
 * @version 1.0.0
 * @author dotzero <mail@dotzero.ru>
 * @author Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Request
{
    /**
     *
     * @var bool Флаг для обработки ответа от сервера через json_decode(, true). По-умолчанию, `true`.
     * @see parseResponse()
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $parseResponse = true;

    /**
     * Включён для совместимости.
     * @var boolean включает/отключает использование параметра CURLOPT_BINARYTRANSFER, для возврата необработанного ответа при использовании константы.
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $parseTransfer = true;

    /**
     * @var bool Флаг использования протокола https. По-умолчанию, `true`.
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $https = true;

    /**
     * @var bool Флаг использования authenticated (http basic). По-умолчанию, `false`.
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $httpAuth = false;

    /**
     * При включении этого параметра все переменные авторизации, кроме `domain`, будут присоеденены к GET-запросу как `КЛЮЧ1=ЗНАЧЕНИЕ1&КЛЮЧ2=ЗНАЧЕНИЕ2...`
     * @var bool Флаг передачи пароля и пользователя в get запросе. По-умолчанию, `false`.
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $authIsGet = false;

    /**
     * @var bool Флаг для установки передачи данных в `Content-Type: application/json`. По-умолчанию, `true`.
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $json = true;

    /**
     * @var bool Флаг для включения использования куков, По-умолчанию, `false`.
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $cookies = false;

    /**
     * @var bool Флаг вывода отладочной информации. По-умолчанию, `false`.
     * @author dotzero <mail@dotzero.ru>
     */
    private $debug = false;

    /**
     * @var ParamsBag|null Экземпляр ParamsBag для хранения аргументов
     * @author dotzero <mail@dotzero.ru>
     */
    private $parameters = null;

    /**
     * @var CurlHandle Экземпляр CurlHandle
     * @author dotzero <mail@dotzero.ru>
     */
    private $curlHandle;

    /**
     * @var int|null Последний полученный HTTP код
     * @author dotzero <mail@dotzero.ru>
     */
    private $lastHttpCode = null;

    /**
     * @var string|null Последний полученный HTTP ответ
     * @author dotzero <mail@dotzero.ru>
     */
    private $lastHttpResponse = null;

    /**
     * @var object|null Экземпляр логгера для вывода информации
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $logger = null;

    /**
     * @var string|null Последняя ошибка перевода в JSON
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $lastJsonError = null;

    /**
     * @var array|null Дополнительные параметры в заколовке запроса
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $extraHeaders = null;

    /**
     * Request constructor
     *
     * @param ParamsBag       $parameters Экземпляр ParamsBag для хранения аргументов
     * @param CurlHandle|null $curlHandle Экземпляр CurlHandle для повторного использования
     * @param object|null     $logger Экземпляр логгера для вывода информации
     * @author dotzero <mail@dotzero.ru>
     */
    public function __construct(Logger $logger, ParamsBag $parameters, CurlHandle $curlHandle = null)
    {
        $this->logger     = $logger;
        $this->parameters = $parameters;
        $this->curlHandle = $curlHandle !== null ? $curlHandle : new CurlHandle();
    }

    /**
     * Установка флага вывода отладочной информации
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author dotzero <mail@dotzero.ru>
     */
    public function setDebug($flag = false)
    {
        $this->debug = (bool) $flag;

        $level = $this->debug ? LogLevel::DEBUG : LogLevel::INFO;
        $this->logger->setLevel($level);

        return $this;
    }

    /**
     * Установка флага отправки данных через JSON
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setJSON($flag = false)
    {
        $this->json = (bool) $flag;

        return $this;
    }

    /**
     * Установка флага отправки данных через HTTPS
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setHTTPS($flag = false)
    {
        $this->https = (bool) $flag;

        return $this;
    }

    /**
     * Установка флага отправки данных через HTTP[S].
     *
     * Ищет параметры авторизации в список значений параметров для авторизации по ключам `login` и `password`.
     *
     * @param bool $flag Значение флага
     * @return $this
     * @see ParamsBag::addAuth()
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setHTTPAuth($flag = false)
    {
        $this->httpAuth = (bool) $flag;

        return $this;
    }

    /**
     * Установка флага отправки параметров авторизации GET-запросом
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setGetAuth($flag = false)
    {
        $this->authIsGet = (bool) $flag;

        return $this;
    }

    /**
     * Установка флага использования cookie
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setCookies($flag = false)
    {
        $this->cookies = (bool) $flag;

        return $this;
    }

    /**
     * Установка флага обработки ответа от сервера через json_decode(, true).
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setParseResponse($flag = false)
    {
        $this->parseResponse = (bool) $flag;

        return $this;
    }

    /**
     * Установка флага возврата необработанного ответа при использовании константы.
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setParseTransfer($flag = false)
    {
        $this->parseTransfer = (bool) $flag;

        return $this;
    }

    /**
     * Возвращает последний полученный HTTP код
     *
     * @return int|null
     * @author dotzero <mail@dotzero.ru>
     */
    public function getLastHttpCode()
    {
        return $this->lastHttpCode;
    }

    /**
     * Добавление дополнительных http заголовков, затирает предыдущие
     * @param array $headers массив дополнительных http заголовков
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setHeaders($headers = array())
    {
        $this->extraHeaders = $headers;

        return $this;
    }

    /**
     * Возвращает последний полученный HTTP ответ
     *
     * @return null|string
     * @author dotzero <mail@dotzero.ru>
     */
    public function getLastHttpResponse()
    {
        return $this->lastHttpResponse;
    }

    /**
     * Возвращает экземпляр ParamsBag для хранения аргументов
     *
     * @return ParamsBag|null
     * @author dotzero <mail@dotzero.ru>
     */
    protected function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Возвращает экземпляр ParamsBag для хранения аргументов
     *
     * @return ParamsBag|null
     * @author dotzero <mail@dotzero.ru>
     */
    protected function getCurlHandle()
    {
        return $this->curlHandle;
    }

    /**
     * Выполнить HTTP GET запрос и вернуть тело ответа
     *
     * @param string $url Запрашиваемый URL
     * @param array $parameters Список GET параметров
     * @param null|string $modified Значение заголовка IF-MODIFIED-SINCE
     * @return mixed
     * @throws Exception
     * @throws NetworkException
     * @author dotzero <mail@dotzero.ru>
     */
    protected function getRequest($url, $parameters = [], $modified = null, $debug = null)
    {
        if (!empty($parameters)) {
            $this->parameters->addGet($parameters);
        }

        return $this->request($url, $modified, $debug);
    }

    /**
     * Выполнить HTTP POST запрос и вернуть тело ответа
     *
     * @param string $url Запрашиваемый URL
     * @param array $parameters Список POST параметров
     * @return mixed
     * @throws Exception
     * @throws NetworkException
     * @author dotzero <mail@dotzero.ru>
     */
    protected function postRequest($url, $parameters = [], $debug = null)
    {
        if (!empty($parameters)) {
            $this->parameters->addPost($parameters);
        }

        return $this->request($url, null, $debug);
    }

    /**
     * Подготавливает список заголовков HTTP
     *
     * @param mixed $modified Значение заголовка IF-MODIFIED-SINCE
     * @return array
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    protected function prepareHeaders($modified = null)
    {
        $headers   = [];
        $headers[] = 'Connection: keep-alive';
        if ($this->json) {
            $headers[] = 'Content-Type: application/json';
        }

        if ($modified !== null) {
            if (is_int($modified)) {
                $headers[] = 'IF-MODIFIED-SINCE: '.$modified;
            } else {
                $headers[] = 'IF-MODIFIED-SINCE: '.(new DateTime($modified))->format(DateTime::RFC1123);
            }
        }

        if (is_array($this->extraHeaders)) {
            $headers = array_merge($headers, $this->extraHeaders);
        }

        return $headers;
    }

    /**
     * Подготавливает URL для HTTP[S] запроса
     *
     * @param string $url Запрашиваемый URL
     * @return string
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    protected function prepareEndpoint($url)
    {
        $addArray = array();
        if ($this->authIsGet) {
            foreach ($this->parameters->getAuth() as $key => $value) {
                if ($key == "domain") {
                    continue;
                }
                $addArray[$key] = $value;
            }
        }
        $query    = http_build_query(array_merge($this->parameters->getGet(), $addArray), null, '&');
        $protocol = $this->https ? "https" : "http";
        $template = empty($query) ? '%s://%s%s' : '%s://%s%s?%s';
        return sprintf($template, $protocol, $this->parameters->getAuth('domain'), $url, $query);
    }

    /**
     * Выполнить HTTP[S] запрос и вернуть тело ответа
     *
     * @param string $url Запрашиваемый URL (без учёта домена)
     * @param null|string $modified Значение заголовка IF-MODIFIED-SINCE
     * @param null|boolean $debug Выводить ответ в отладочной информации. Значение NULL - использовать глобальный параметр degug.
     * @return mixed
     * @throws Exception
     * @throws NetworkException
     * @author dotzero <mail@dotzero.ru>
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    protected function request($url, $modified = null, $debug = null)
    {
        $this->logger->debug('json', $this->json);
        $this->logger->debug('cookies', $this->cookies);

        $headers  = $this->prepareHeaders($modified);
        $endpoint = $this->prepareEndpoint($url);

        $this->logger->debug('url', $endpoint);
        $this->logger->debug('headers', $headers);

        $ch = $this->curlHandle->open();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($this->cookies) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
            curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        }

        if ($this->httpAuth) {
            curl_setopt($ch, CURLOPT_USERPWD,
                $this->parameters->getAuth("login").":".$this->parameters->getAuth("password"));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        if ($this->parameters->hasPost()) {
            if ($this->json) {
                $fields = json_encode($this->parameters->getPost());
            } else {
                $fields = http_build_query($this->parameters->getPost());
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $this->logger->debug('post params', $fields);
        }
        if (!$this->parseTransfer) {
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        }
        if ($this->parameters->hasFile()) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_INFILE, $this->parameters->openFile());
            curl_setopt($ch, CURLOPT_INFILESIZE, $this->parameters->getFileSize());
            $this->logger->debug('file params', $this->parameters->getFileParams());
        }
        if ($this->parameters->hasProxy()) {
            curl_setopt($ch, CURLOPT_PROXY, $this->parameters->getProxy());
        }

        $result = curl_exec($ch);
        $info   = curl_getinfo($ch);
        $error  = curl_error($ch);
        $errno  = curl_errno($ch);

        $this->curlHandle->reset();

        $this->lastHttpCode     = $info['http_code'];
        $this->lastHttpResponse = $result;

        if ($debug !== false) {
            $this->logger->debug('curl_exec', $result);
        } else {
            $this->logger->debug('curl_exec', "Set **NOT DEBUG RESULT**");
        }
        $this->logger->debug('curl_getinfo', $info);
        $this->logger->debug('curl_error', $error);
        $this->logger->debug('curl_errno', $errno);

        if ($result === false && !empty($error)) {
            throw new NetworkException($error, $errno);
        }

        if ($this->parseResponse) {
            return $this->parseResponse($result, $info);
        } else {
            return $result;
        }
    }

    /**
     * Парсит HTTP ответ, проверяет на наличие ошибок и возвращает тело ответа
     *
     * @param string $response HTTP ответ
     * @param array $info Результат функции curl_getinfo
     * @return mixed
     * @throws Exception
     * @author dotzero <mail@dotzero.ru>
     */
    protected function parseResponse($response, $info)
    {
        $result = json_decode($response, true);
        return $result;
    }
}