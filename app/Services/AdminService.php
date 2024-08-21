<?php
// app/Services/UserService.php
namespace App\Services;

use App\Models\File;
use App\Models\Group;
use App\Models\Public_File;


class AdminService
{
    public function list_files_service(){
            $public_files=Public_file::get();
            $group_files=File::get();
            foreach ($group_files as $index => $file) {
                $group = Group::where('id', $file->group_id)->first();
                $group_files[$index]['group_name'] = $group->name;
            }
            $response=[
               'Public Group'=> $public_files,
                'Groups'=>$group_files
            ];
            return $response;
         }
}
