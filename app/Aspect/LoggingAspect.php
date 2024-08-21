<?php
namespace App\Aspect;

use Closure;
use Exception;
use App\Models\Log;


class LoggingAspect
{
    public function __construct()
    {
        $this->id = 0;
    }

    public function handle($request, Closure $next)
    {
        try {
            //before
            $this->Before($request);
            //after
            $response = $next($request);
            $this->After($response);
        } catch (Exception $exception) {
            //exception
            $this->Exception($exception);
        }

        return $response;
    }

    public function Before($request)
    {
        $log = new Log();
        $log->request = $request->fullUrl();
        $log->save();
        $this->id = $log->id;
    }

    public function After($response)
    {
        $log = Log::find($this->id);
        $log->response = $response->getContent();
        $log->save();
    }

    public function Exception(Exception $exception)
    {
        $log = Log::find($this->id);
        $log->error = $exception->getMessage();
        $log->save();
    }
}

