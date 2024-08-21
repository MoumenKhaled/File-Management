<?php
namespace App\Aspect;

use Closure;
use App\Models\User;
use App\Models\Public_file;
use Illuminate\Support\Facades\DB;
class TransactionAspect
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

                DB::beginTransaction();

        }

        public function After($response)
        {

                DB::commit();


        }

        public function Exception(Exception $exception)
        {
            DB::rollback();
        }
}
