<?php

namespace App\Http\Controllers\Public_Group;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PublicService;
use App\Models\Public_File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
class PublicController extends Controller
{
    protected $publicservice;

    public function __construct(PublicService $publicservice)
    {
        $this->publicservice = $publicservice;
    }
    public function list_file_public(Request $request)
    {
        
        $response = $this->publicservice->list_public_files_service();
        return response($response);
    }
    public function add_public_file(Request $request){
        $file=$request->validate([
            'name'=>'required',
            'file'=>'required',
        ]);
        $response = $this->publicservice->add_public_file_service($file);
        return response($response);
    }
    public function my_public_files(Request $request){
         $owner_file_id=auth()->id();
         $response = $this->publicservice->my_public_files_service($owner_file_id);


        return response($response);
    }
    public function check_in_public_file(Request $request,$file_id){
        $response = $this->publicservice->check_in_public_file_service($file_id);
       return response($response);
    }
    public function check_in_list_public_files(Request $request){
    $file_IDs=$request->validate([
        'IDs'=>'required',
    ]);
    $response = $this->publicservice->check_in_public_list_files_service($file_IDs['IDs']);
    return response($response);
    }
    public function check_out_public_file(Request $request){
    $file=$request->validate([
        'file_id'=>'required',
        'file'=>'required',
    ]);
    $response = $this->publicservice->check_out_public_file($file);
    return response($response);
    }
    public function read_file_from_public(Request $request,$file_id){
    $response = $this->publicservice->read_file_from_public_service($file_id);
    return response($response);
    }
    public function delete_file_from_public(Request $request,$file_id){
    $response = $this->publicservice->delete_file_from_public_service($file_id);
    return response($response);
    }
}
