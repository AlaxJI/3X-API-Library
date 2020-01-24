<?php
use Test_3xAPI\TestClient;
require_once 'autoload.php';
require_once 'test/TestClient.php';


$tc = new TestClient();
$tc->setDebug(true);
$tc->testModel;