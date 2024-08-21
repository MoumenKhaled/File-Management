<?php
// app/Services/UserService.php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Public_File;
use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Cache;
class PublicService
{
    public function list_public_files_service()
{
        $page = request()->query('page', 1);
        $cacheKey = 'public_files_' . $page;

        if (Cache::has($cacheKey)) {
            $public_files = Cache::get($cacheKey);
        } else {
            $public_files = Public_File::where('status', 'free')->paginate(100);
            Cache::put($cacheKey, $public_files, now()->addHours(1));
        }

        return $public_files;
}
    public function add_public_file_service($Data)
{
    $file = $Data['file'];
    $file_name = $Data['name'] . '.' . $file->getClientOriginalExtension();
    $file->move(public_path('uploads/publicfiles'), $file_name);
    $path = "public/uploads/publicfiles/" . $file_name;

    $isexist = Public_file::where('name', $file_name)->first();
    if ($isexist) {
        return ['message' => "This name of file has already been taken, please change it"];
    } else {

        $allowedExtensions = ['txt', 'doc', 'docx','pdf'];
        $fileExtension = $file->getClientOriginalExtension();
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['message' => "Adding this type of file is not allowed"];
        }
        $publicfile = new Public_file();
        $publicfile->owner_file_id = auth()->id();
        $publicfile->name = $file_name;
        $publicfile->file = $path;
        $publicfile->save();
        // تهيئة الكاش
        Cache::flush();
        return $publicfile;
    }
}

    public function my_public_files_service($Data)
{

        $my_files = Public_file::where('owner_file_id', $Data)->get();
        if ($my_files) {
            foreach ($my_files as $file) {
                if ($file['user_file_id']) {
                    $user = User::where('id', $file['user_file_id'])->first();
                    $file['file_using_from'] = $user;
                }
            }
        }

        return $my_files;
}
    public function check_in_public_file_service($Data)
{ 
        $my_files = Public_file::where('id', $Data)->lockForUpdate()->first();
        $user_id=auth()->id(); 
        $user_info = User::where('id',$user_id)->first(); 
        $download_file=[];
        if ($my_files && $this->canReserveFile($my_files, $user_info)) {
            $this->reserveFile($my_files, $user_info);
            $user_info->number_of_files=($user_info->number_of_files)+1;
            $user_info->save(); 
            // download file
           $file = public_path() . "/uploads/publicfiles/" . $my_files->name;
           $download_file=['message'=>$file];
            ////report
            $this->createReport($user_id, $my_files, $user_info, 'public');
            //تهيئة الكاش
            Cache::flush();
            return $download_file;
        }  
        else {
            return ['message'=>"Sorry, you cannot reserve this file"];
        }
}
    public function check_in_public_list_files_service($Data)
{
        $user_id = auth()->id();
        $user = User::find($user_id);
        $response['message']="Files have been reserved successfully";
        $response['URLs'] = [];
        foreach ($Data as $file_id) {
            $file = Public_file::where('id', $file_id)->lockForUpdate()->first();
            if ($file->status == "free") {
                if($user->number_of_files>=10){
                exit("Exceeded maximum number of files");
                }
                else {
                $response['URLs'][] = $file->file;
                $file->user_file_id = $user_id;
                $file->status = "reserved";
                $file->save();

                $user->number_of_files = ($user->number_of_files) + 1;
                $user->save();
                //report
                $this->createReport($user_id, $file, $user, 'public');
            }
        }
        else {
              exit("File $file->name is not free");
            }
        }
        //تهيئة الكاش
        Cache::flush();
        return $response;
}
    public function  check_out_public_file($Data)
{
        $file = $Data['file'];
        $file_name = $file->getClientOriginalName();
        $orginal_file=Public_file::where('id',$Data['file_id'])->first();
        $user_info = User::where('id',auth()->id())->first();
        
        if ($orginal_file['name']==$file_name){
             $file->move(public_path('uploads/publicfiles'),$file_name);
             $orginal_file->status='free';
             $orginal_file->user_file_id=null;
             $orginal_file->save();
             
             $user_info->number_of_files=($user_info->number_of_files)-1;
             $user_info->save();
             //report check_out_date
             $report = Report::where([
                ['file_id', $orginal_file->id],
                ['group_name','public']
             ])->latest()->first();
             if (is_null($report->check_out_date)) {
                $report->check_out_date=Carbon::now();
                $report->save();
            }
            Cache::flush();
            return $orginal_file;
        }
        else {
            return ['message'=>'Sorry, this file is different from the one that was reserved'];
        }
}
    public function read_file_from_public_service($Data)
{
    $file=Public_file::where('id',$Data)->first();
    if ($file->status=='free'){
        $path = public_path() . "/uploads/publicfiles/" . $file->name;
        $content = file_get_contents($path);
        $cleanString = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        return $cleanString;
    }
    else {
        return ['message'=>'Sorry, this file is reserved, you cannot read it'];
    }
}
    public function delete_file_from_public_service($Data)
{
    $file=Public_file::where('id',$Data)->first();
    $user_id=auth()->id();
    if ($file->status=='free'){
        if ($file->owner_file_id==$user_id){
        $file->delete();
        Cache::flush();
        return ['message'=>'The file has been deleted successfully'];
    }
    else {
        return ['message'=>'Sorry, You do not have the right to delete this file'];
    }
    }
    else {
        return ['message'=>'Sorry, this file is reserved, you cannot delete it'];
    }
}
    private function createReport($user_id, $my_file, $user_info, $group)
{
    
    $report = new Report();
    $report->user_file_id = $user_id;
    $report->owner_file_name = User::where('id', $my_file->owner_file_id)->first()->user_name;
    $report->user_name = $user_info->user_name;
    $report->group_id = 1;
    $report->group_name = $group;
    $report->file_id = $my_file->id;
    $report->file_name = $my_file->name;
    $report->check_in_date = Carbon::now();
    $report->save();
}
    private function canReserveFile($file, $user_info)
{
    return $user_info->number_of_files < 10 && $file->status == "free";
}
    private function reserveFile($file, $user_info)
{
    $file->status = "reserved";
    $file->user_file_id = $user_info->id;
    $file->save();

    $user_info->increment('number_of_files');
    $user_info->save();
}
}
