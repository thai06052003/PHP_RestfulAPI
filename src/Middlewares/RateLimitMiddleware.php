<?php

namespace App\Middlewares;

//use App\Models\RateLimit;
use App\Models\RequestLog;
use Pecee\Http\Middleware\IMiddleware;
use \PalePurple\RateLimit\RateLimit;
use \PalePurple\RateLimit\Adapter\APC as APCAdapter;
use \PalePurple\RateLimit\Adapter\Stash as StashAdapter;

class RateLimitMiddleware implements IMiddleware
{
    private $requestNumber;
    private $requestPer;
    public function __construct()
    {
        $this->requestNumber = env('RATE_LIMIT_REQUEST');
        $this->requestPer = env('RATE_LIMIT_PER');
    }
    public function handle($request): void
    {
        /* $rateLimitModel = new RateLimit();
        $ip = request()->getIp();
        $rateLimit = $rateLimitModel->find($ip);
        // kiểm tra tồn tại request với ip ko && kiểm tra số request_number có lớn hơn cho phép ko
        if ($rateLimit && $rateLimit->request_number >= $this->requestNumber) {
            $seconds = time() - strtotime($rateLimit->start_time);
            if ($seconds >= $this->requestPer) {
                $this->resetRateLimit();
            } else {
                errorResponse(429, 'Rate Limit');
            }
        } else {
            $this->addRequest();
            $this->updateRateLimit();
        } */
        /* $isRate = true;
        if ($isRate) errorResponse(429, "Rate Limit"); */
        $stash = new \Stash\Pool(new \Stash\Driver\FileSystem());
        $adapter = new StashAdapter($stash);

        $rateLimit = new RateLimit("limit_ip", $this->requestNumber, $this->requestPer, $adapter); // 100 Requests / Hour

        $id = $_SERVER['REMOTE_ADDR']; // Use client IP as identity
        if (!$rateLimit->check($id)) {
            errorResponse(429, "Rate Limit");
        }
    }
    private function addRequest()
    {
        $ip = request()->getIp();
        $requestLogModel = new RequestLog();
        $requestLogModel->create([
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function updateRateLimit()
    {
        $ip = request()->getIp();
        $requestLogModel = new RequestLog();
        $count = $requestLogModel->getCount($ip);
        $rateLimitModel = new RateLimit();
        $rateLimitModel->update($ip, [
            'ip_address' => $ip,
            'request_number' => $count,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
    private function resetRateLimit()
    {
        $requestLogModel = new RequestLog();
        $rateLimitModel = new RateLimit();
        $ip = request()->getIp();
        $requestLogModel->delete($ip);
        $rateLimitModel->delete($ip);
    }
}
