<?php

namespace Cosmic5173\WebAPI\thread;

use JetBrains\PhpStorm\Pure;

class RequestSendQueue extends \Threaded {
    /** @var bool */
    private bool $invalidated = false;
    /** @var \Threaded */
    protected \Threaded $requests;

    #[Pure]
    public function __construct() {
        $this->requests = new \Threaded();
    }

    public function scheduleRequest(int $requestId, string $requestUrl, int $mode, array $requestParams, array|string $requestData, array $requestHeaders): void {
        if($this->invalidated){
            throw new QueueShutdownException("You cannot schedule a query on an invalidated queue.");
        }
        $this->synchronized(function() use ($requestId, $requestUrl, $mode, $requestParams, $requestData, $requestHeaders) : void{
            $this->requests[] = serialize([$requestId, $requestUrl, $mode, $requestParams, $requestData, $requestHeaders]);
            $this->notifyOne();
        });
    }

    public function fetchRequest(): ?string {
        return $this->synchronized(function (): ?string {
            while($this->requests->count() === 0 && !$this->isInvalidated()) {
                $this->wait();
            }
            return $this->requests->shift();
        });
    }

    public function invalidate() : void {
        $this->synchronized(function():void{
            $this->invalidated = true;
            $this->notify();
        });
    }

    public function isInvalidated(): bool {
        return $this->invalidated;
    }
}