<?php

namespace Cosmic5173\WebAPI\request;

use Cosmic5173\WebAPI\thread\RequestThreadPool;
use Cosmic5173\WebAPI\request\types\{GetRequest, PostRequest, PutRequest, DeleteRequest};
use pocketmine\Server;

final class RequestHandler {

    private RequestThreadPool $threadPool;
    /** @var \Closure[] */
    private array $handlers = [];
    public int $requests = 0;

    public function __construct(RequestThreadPool $threadPool) {
        $this->threadPool = $threadPool;
    }

    /**
     * @return RequestThreadPool
     */
    public function getThreadPool(): RequestThreadPool {
        return $this->threadPool;
    }

    public function sendGetRequest(GetRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $startTime = microtime(true);
        Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId created.");
        $this->handlers[$requestId] = static function (?string $response = null) use ($closure, $startTime, $requestId) {
            Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId completed in " . (microtime(true) - $startTime) . " seconds.");
            if (isset($closure)) $closure($response);
        };

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), $request->getParams(), "", $request->getHeaders());
    }

    public function sendPostRequest(PostRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $startTime = microtime(true);
        Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId created.");
        $this->handlers[$requestId] = static function (?string $response = null) use ($closure, $startTime, $requestId) {
            Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId completed in " . (microtime(true) - $startTime) . " seconds.");
            if (isset($closure)) $closure($response);
        };

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), [], json_encode($request->getParams()), $request->getHeaders());
    }

    public function sendPutRequest(PutRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $startTime = microtime(true);
        Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId created.");
        $this->handlers[$requestId] = static function (?string $response = null) use ($closure, $startTime, $requestId) {
            Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId completed in " . (microtime(true) - $startTime) . " seconds.");
            if (isset($closure)) $closure($response);
        };

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), [], $request->getPayload(), $request->getHeaders());
    }

    public function sendDeleteRequest(DeleteRequest $request, \Closure $closure = null): void {
        $requestId = $this->requests++;
        $startTime = microtime(true);
        Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId created.");
        $this->handlers[$requestId] = static function (?string $response = null) use ($closure, $startTime, $requestId) {
            Server::getInstance()->getLogger()->debug("[WebAPI] Request #$requestId completed in " . (microtime(true) - $startTime) . " seconds.");
            if (isset($closure)) $closure($response);
        };

        $this->threadPool->addRequest($requestId, $request->getUrl(), $request->getMode(), $request->getParams(), "", $request->getHeaders());
    }

    public function addHandler(int $requestId, \Closure $closure): void {
        $this->handlers[$requestId] = $closure;
    }

    public function removeHandler(int $requestId): void {
        unset($this->handlers[$requestId]);
    }

    public function getHandlers(): array {
        return $this->handlers;
    }

    public function checkResults() : void{
        $this->threadPool->readResults($this->handlers);
    }

    public function close() : void{
        $this->threadPool->stopRunning();
    }
}