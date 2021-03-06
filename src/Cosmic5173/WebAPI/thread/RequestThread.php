<?php

namespace Cosmic5173\WebAPI\thread;

use Cosmic5173\WebAPI\request\RequestMode;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use pocketmine\utils\Internet;

class RequestThread extends Thread {

    /** @var SleeperNotifier */
    private SleeperNotifier $notifier;

    private static int $nextSlaveNumber = 0;

    protected int $slaveNumber;
    protected RequestSendQueue $bufferSend;
    protected RequestRecvQueue $bufferRecv;
    protected bool $busy = false;

    protected function __construct(SleeperNotifier $notifier, ?RequestSendQueue $bufferSend = null, ?RequestRecvQueue $bufferRecv = null) {
        $this->notifier = $notifier;

        $this->slaveNumber = self::$nextSlaveNumber++;
        $this->bufferSend = $bufferSend ?? new RequestSendQueue();
        $this->bufferRecv = $bufferRecv ?? new RequestRecvQueue();

        $this->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);
    }

    protected function onRun(): void {
        while (true) {
            try {
                $row = $this->bufferSend->fetchRequest();
                if (!is_string($row)) {
                    break;
                }
                $this->busy = true;
                [$requestId, $requestUrl, $mode, $requestParams, $requestData, $requestHeaders] = unserialize($row, ["allowed_classes" => true]);

                $url = match ($mode) {
                    RequestMode::GET, RequestMode::DELETE => $requestUrl."?".http_build_query($requestParams),
                    default => $requestUrl
                };
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_URL, $url);

                switch ($mode) {
                    case RequestMode::POST:
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $requestUrl,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $requestData,
                        ]);
                        break;
                    case RequestMode::PUT:
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $requestUrl,
                            CURLOPT_CUSTOMREQUEST => "PUT",
                            CURLOPT_POSTFIELDS => $requestData,
                        ]);
                        break;
                    case RequestMode::DELETE:
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $requestUrl."?".http_build_query($requestParams),
                            CURLOPT_CUSTOMREQUEST => "DELETE",
                        ]);
                        break;
                }
                if (!empty($requestHeaders)) {
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
                } else {
                    curl_setopt($curl, CURLOPT_HEADER, false);
                }

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                $resp = curl_exec($curl);
                curl_close($curl);

                $this->bufferRecv->publishResult($requestId, $resp ? $resp : null);
                $this->notifier->wakeupSleeper();
                $this->busy = false;
            } catch (\Exception $e) {
                $this->bufferRecv->publishResult($requestId, null);
                $this->notifier->wakeupSleeper();
                $this->busy = false;
            }
        }
    }

    /**
     * @return bool
     */
    public function isBusy(): bool {
        return $this->busy;
    }

    public function stopRunning() : void{
        $this->bufferSend->invalidate();

        parent::quit();
    }

    public function quit() : void{
        $this->stopRunning();
        parent::quit();
    }

    public function addRequest(int $requestId, string $requestUrl, int $mode, array $requestParams, array|string $requestData, array $requestHeaders): void {
        $this->bufferSend->scheduleRequest($requestId, $requestUrl, $mode, $requestParams, $requestData, $requestHeaders);
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

    public static function createFactory(): \Closure {
        return static function (SleeperNotifier $notifier, ?RequestSendQueue $bufferSend = null, ?RequestRecvQueue $bufferRecv = null) {
            return new self($notifier, $bufferSend, $bufferRecv);
        };
    }
}