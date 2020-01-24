<?php

namespace Test_3xAPI\Models;

use _3xAPI\Models\AbstractModel;

/**
 * Class Lead
 *
 * Класс тестовая модель
 *
 * @package Test_3xAPI\Models
 * @author AlaxJI <alaxji@gmail.com>
 * @version 0.1.0
 * @property int $id ID
 * @property string $name Name
 *
 */
class TestModel extends AbstractModel
{
    protected $fields = [
        "id",
        "name",
    ];

    /**
     * @var int № Заказа
     */
    function __construct($parameters, $curlHandle = null)
    {
        parent::__construct($parameters, $curlHandle);
        $this->setJSON(false)
            ->setCookies(true)
            ->setHTTPS(true);
    }

    /**
     * Список заказов
     *
     * Метод для получения списка заказов
     *
     * @param boolean $confirm Сразу подтверждать получение заказов
     * @link https://example.pro/1c_exchange.php?type=order_bot
     * @return array|object Экземпляр сделки или массив экземпляров сделок
     */
    public function load($confirm = false)
    {

        $response = $this->getRequest('/1c_exchange.php', array("type" => "order_bot"));
        if (is_null($response)) {
            return $this;
        }
        if ($confirm) {
            $this->confirm();
        }
        $orders = array();
        foreach ($response as $key => $orderArray) {
            $order     = new \REDMOND\Models\Order($this->getParameters(), $this->getCurlHandle());
            $order->id = $orderArray["ORDERID"];
            foreach ($orderArray as $key => $value) {
                $order[$key] = trim($value);
            }
            $orders[$orderArray["ORDERID"]] = $order;
        }

        return $orders;
    }

    /**
     * Подтверждение получения заказов
     * @link https://example.pro/1c_exchange.php?type=order_bot&complete=Y
     */
    public function confirm()
    {
        $response = $this->getRequest('/1c_exchange.php', array("type" => "order_bot", "complete" => "Y"));
    }

    public function update($orders = array())
    {
        if (empty($orders)) {
            $orders[] = $this;
        }
        $ordersOut = array();
        foreach ($orders as $order) {
            $orderOut = new \stdClass();
            foreach ($order->getValues() as $offSet => $value) {
                $orderOut->$offSet = $value;
            }
            $ordersOut[] = $orderOut;
        }
        $this->setJSON(true);
        $this->getParameters()->addPost($ordersOut);
        $response = $this->getRequest('/1c_exchange.php', array("type" => "order_bot_result"));
        return $response;
    }
}