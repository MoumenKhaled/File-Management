<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GroupService;
use App\Models\User;
use App\Models\User_Group;
use App\Models\Group;
class GroupController extends Controller
{
    protected $groupservice;

    public function __construct(GroupService $groupservice)
    {
        $this->groupservice = $groupservice;
    }
    public function create_group(Request $request){
        $group=$request->validate([
            'name'=>'required',
        ]);
        $response = $this->groupservice->create_group_service($group);
        return response($response);
    }
    public function add_file_to_group(Request $request){
        $file_group=$request->validate([
            'name'=>'required',
            'file'=>'required',
            'group_name'=>'required',
        ]);
        $response = $this->groupservice->add_file_to_group_service($file_group);
        return response($response);
    }
    public function delete_file_from_group(Request $request){
        $file_group=$request->validate([
            'file_id'=>'required',
            'group_id'=>'required'
        ]);
        $response = $this->groupservice->delete_file_from_group_service($file_group);
        return response($response);
    }
    public function add_user_to_group(Request $request){
        $Data=$request->validate([
            'user_name'=>'required',
            'group_id'=>'required'
        ]);
        $response = $this->groupservice->add_user_to_group_service($Data);
        return response($response);
    }
    public function delete_user_from_group(Request $request){
        $Data=$request->validate([
            'user_name'=>'required',
            'group_id'=>'required'
        ]);
        $response = $this->groupservice->delete_user_from_group_service($Data);
        return response($response);
    }
    public function list_user_in_my_group(Request $request,$group_id){
        $response = $this->groupservice->list_user_in_my_group_service($group_id);
        return response($response);
    }
    public function group_details(Request $request,$group_id){
        $response = $this->groupservice->group_details_service($group_id);
        return response($response);
    }
    public function list_created_groups(Request $request){
        $owner_id=auth()->id();
        $response = $this->groupservice->list_created_groups_service($owner_id);
        return response($response);
    }
    public function list_joined_groups(Request $request){
        $owner_id=auth()->id();
        $response = $this->groupservice->list_joined_groups_service($owner_id);
        return response($response);
    }
    public function list_users(Request $request){
        $response = $this->groupservice->list_users_service();
        return response($response);
    }
    public function read_file_from_group(Request $request,$file_id){
        $response = $this->groupservice->read_file_from_group_service($file_id);
        return response($response);
    }
    public function check_in_group_file(Request $request,$file_id){
       $response = $this->groupservice->check_in_group_file_service($file_id);
       return response($response);
   }
   public function check_in_list_of_group_files(Request $request){
    $file_IDs=$request->validate([
        'IDs'=>'required',
    ]);
    $response = $this->groupservice->check_in_list_of_group_files_service($file_IDs['IDs']);
    return response($response);
}
   public function check_out_group_file(Request $request){
    $file=$request->validate([
        'file_id'=>'required',
        'file'=>'required',
    ]);
    $response = $this->groupservice->check_out_group_file($file);
    return response($response);
}
    public function my_reserved_file(Request $request){
    $user_id=auth()->id();
    $response = $this->groupservice->my_reserved_file_service($user_id);
    return response($response);
    }
    public function number_of_reserved_files(Request $request){
        $user_id=auth()->id();
        $response = $this->groupservice->number_of_reserved_files_service($user_id);
        return $response;
    }
}
