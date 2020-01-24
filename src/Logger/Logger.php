<?php
/*
 * This file is part of the _3xAPI package.
 *
 * (c) Alexei Dubrovski <alaxji@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace _3xAPI\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use \SplObjectStorage;


/**
 * Description of Logger
 *
 * @author Alexei Dubrovski <alaxji@gmail.com>
 */
class Logger extends AbstractLogger implements LoggerInterface
{
	/**
	 * @var SplObjectStorage Список роутов
	 */
	private $routes;


	public function __construct()
	{
		$this->routes = new SplObjectStorage();
	}

    /**
     *
     * @param object $route
     */
    public function addRoute($route)
    {
        $this->routes->attach($route);
    }

    public function log($level, $message, array $context = array())
    {
		foreach ($this->routes as $route)
		{
			$route->log($level, $message, $context);
		}
    }

    /**
     *
     * @param LogLevel $level
     */
    public function setLevel($level)
    {
		foreach ($this->routes as $route)
		{
            if ($route instanceof StdRoute){
                $route->setLevel($level);
            }
		}
    }
}