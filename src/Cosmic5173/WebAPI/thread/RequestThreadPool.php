<?php

namespace Cosmic5173\WebAPI\thread;

use Cosmic5173\WebAPI\request\RequestHandler;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;

class RequestThreadPool {

    private SleeperNotifier $notifier;
    private \Closure $workerFactory;
    /** @var RequestThread[] */
    private array $workers = [];
    private int $workerLimit;

    /** @var RequestSendQueue */
    private RequestSendQueue $bufferSend;
    /** @var RequestRecvQueue */
    private RequestRecvQueue $bufferRecv;

    private RequestHandler $handler;

    public function __construct(\Closure $workerFactory, int $workerLimit = 10) {
        $this->notifier = new SleeperNotifier();
        Server::getInstance()->getTickSleeper()->addNotifier($this->notifier, function() {
            $this->handler->checkResults();
        });

        $this->workerFactory = $workerFactory;
        $this->workerLimit = $workerLimit;
        $this->bufferSend = new RequestSendQueue();
        $this->bufferRecv = new RequestRecvQueue();

        $this->addWorker();
    }

    private function addWorker() : void{
        $this->workers[] = ($this->workerFactory)($this->notifier, $this->bufferSend, $this->bufferRecv);
    }

    public function join() : void{
        foreach($this->workers as $worker){
            $worker->join();
        }
    }

    public function stopRunning() : void{
        foreach($this->workers as $worker){
            $worker->stopRunning();
        }
    }

    public function addRequest(int $requestId, string $requestUrl, int $mode, array $requestParams, array|string $requestData, array $requestHeaders): void {
        $this->bufferSend->scheduleRequest($requestId, $requestUrl, $mode, $requestParams, $requestData, $requestHeaders);

        // check if we need to increase worker amount
        foreach($this->workers as $worker){
            if(!$worker->isBusy()){
                return;
            }
        }
        if(count($this->workers) < $this->workerLimit){
            $this->addWorker();
        }
    }

    public function readResults(array &$closures): void {
        while ($this->bufferRecv->fetchResults($requestId, $response)){
            if(!isset($closures[$requestId])){
                throw new \InvalidArgumentException("Missing handler for query #$requestId");
            }

            $closures[$requestId]($response);
            unset($closures[$requestId]);
        }
    }

    public function getLoad() : float{
        return $this->bufferSend->count() / (float) $this->workerLimit;
    }

    /**
     * @param RequestHandler $handler
     */
    public function setHandler(RequestHandler $handler): void {
        $this->handler = $handler;
    }
}