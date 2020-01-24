<?php
/*
 * This file is part of the _3xAPI package.
 *
 * (c) Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace _3xAPI;

use _3xAPI\Request\CurlHandle;
use _3xAPI\Request\ParamsBag;
use _3xAPI\Helpers\Format;
use _3xAPI\Helpers\Logger;
use _3xAPI\Exceptions\ModelException;
use _3xAPI\Models\ModelInterface;
use _3xAPI\Logger\Logger;
use _3xAPI\Logger\StdRoute;
use Psr\Log\LogLevel;

/**
 * Основной класс для получения доступа к моделям
 * При реализации клиента рекомендуется использовать описание `@property type $modelName Описание`
 *
 * @package AbstractClient
 * @version 1.0.0
 * @author Alexei Dubrovski <alaxji@gmail.com>
 * @author dotzero <mail@dotzero.ru>
 */
class AbstractClient
{
    /**
     * @var Fields|null Экземпляр Fields для хранения номеров полей
     * @author dotzero <mail@dotzero.ru>
     */
    public $fields = null;

    /**
     * @var ParamsBag|null Экземпляр ParamsBag для хранения аргументов
     * @author dotzero <mail@dotzero.ru>
     */
    public $parameters = null;

    /**
     * @var CurlHandle Экземпляр CurlHandle для повторного использования
     * @author dotzero <mail@dotzero.ru>
     */
    private $curlHandle;

    /**
     * @var bool Флаг вывода отладочной информации
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $debug = false;

    /**
     * @var bool Флаг для использования куков
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    private $cookie = false;

    /**
     * @var object
     */
    private $logger;

    /**
     *
     * @var StdRoute|object
     */
    private $route;

    /**
     * AbstractClient constructor
     *
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @author dotzero <mail@dotzero.ru>
     */
    public function __construct($logger = null)
    {
        $this->parameters = new ParamsBag();
        $this->curlHandle = new CurlHandle();
        $this->logger     = new Logger();
        if (is_null($logger)) {
            $this->route = new StdRoute(LogLevel::INFO);
        } else {
            $this->route = $logger;
        }
        $this->logger->addRoute($this->route);
    }

    /**
     * Возвращает экземпляр модели для работы с amoCRM API
     *
     * @param string $name Название модели
     * @return ModelInterface
     * @throws ModelException
     * @author Alexei Dubrovski <alaxji@gmail.com>
     * @author dotzero <mail@dotzero.ru>
     */
    public function __get($name)
    {
        $rClass    = new \ReflectionClass($this);
        $namespace = $rClass->getNamespaceName();
        $classname = "\\$namespace\\Models\\".Format::upperCamelCase($name);
        if (!class_exists($classname)) {
            throw new ModelException('Model not exists: '.$name);
        }

        // Чистим GET и POST от предыдущих вызовов
        $this->parameters->clearGet()->clearPost();

        $item = new $classname($this->logger, $this->parameters, $this->curlHandle);
        $item->setDebug($this->debug)
            ->setCookies($this->cookie);

        $this->logger->debug("Создан экземпляр класса $name");

        return $item;
    }

    /**
     * Установка флага вывода отладочной информации
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setDebug($flag = false)
    {
        $this->debug = (bool) $flag;

        $level = $this->debug ? LogLevel::DEBUG : LogLevel::INFO;
        $this->logger->setLevel($level);

        return $this;
    }

    /**
     * Установка флага использования cookie
     *
     * @param bool $flag Значение флага
     * @return $this
     * @author Alexei Dubrovski <alaxji@gmail.com>
     */
    public function setCookie($flag = false)
    {
        $this->cookie = (bool) $flag;

        return $this;
    }
}