<?php
namespace App\Services;
use App\Models\Report;

class ReportService
{
    public function report_for_user_service($Data){
        $report=Report::where('user_file_id',$Data)->get();
        return $report;
    }
    public function report_for_file_service($Data){
        $report=Report::where([
            ['file_id',$Data['file_id']],
            ['group_name',$Data['group_name']]
            ])->get();
        return $report;
    }
}
