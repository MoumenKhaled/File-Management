<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReportService;
class ReportController extends Controller
{
    protected $reportservice;

    public function __construct(ReportService $reportservice)
    {
        $this->reportservice = $reportservice;
    }
    public function report_for_user(Request $request){
        $user_id=auth()->id();
        $response = $this->reportservice->report_for_user_service($user_id);
        return response($response);
    }
    public function report_for_file(Request $request){
        $report_info=$request->validate([
            'file_id'=>'required',
            'group_name'=>'required',
        ]);
        $response = $this->reportservice->report_for_file_service($report_info);
        return response($response);
    }
}
