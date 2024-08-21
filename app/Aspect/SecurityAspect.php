<?php
namespace App\Aspect;

use Closure;

use Exception;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SecurityAspect
{
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
            if (Auth::check()) {
                $user = Auth::user();
                $user->ip_address = $request->ip();
                $user->save();
            }

            $maxSize = 10 * 1024 * 1024; 

            $requestSize = $request->server('CONTENT_LENGTH');
            if ($requestSize > $maxSize) {
                throw new BadRequestException('Payload too large');
            }
        }

        public function After($response)
        {
            if (strlen($response->getContent()) > 1048576) {
                $response->setContent(gzencode($response->getContent(), 9));
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', strlen($response->getContent()));
            }
        }
        public function Exception(Exception $exception)
         {
            exit ("There are an excption , which is : $exception");
         }

}


