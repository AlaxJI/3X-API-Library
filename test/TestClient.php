<?php

namespace Test_3xAPI;

use _3xAPI\AbstractClient;

require_once __DIR__.'/autoload.php';


/**
 *
 * @property \Test_3xAPI\Models\TestModel $testModel Описание
 *
 */

class TestClient extends AbstractClient
{
    public function __construct()
    {
        parent::__construct();
    }
}