<?php

namespace Cosmic5173\WebAPI;

use Cosmic5173\WebAPI\request\RequestHandler;
use Cosmic5173\WebAPI\thread\RequestThread;
use Cosmic5173\WebAPI\thread\RequestThreadPool;
use pocketmine\plugin\PluginBase;

final class WebAPIHandler extends PluginBase {

    public static function create(int $workerCount = 1): RequestHandler {
        return new RequestHandler(new RequestThreadPool(RequestThread::createFactory(), $workerCount));
    }
}