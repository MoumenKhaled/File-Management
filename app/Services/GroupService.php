<?php
namespace App\Services;
use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\User_Group;
use App\Models\Public_File;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Carbon\Carbon;
class GroupService
{
    public function create_group_service($Data)
{
        $isexist=Group::where('name',$Data['name'])->first();
        if($isexist){
           return ['message'=>'This group name has been taken, please change it'];
        }
        else {
        $group = new Group();
        $group->name=$Data['name'];
        $group->owner_id=auth()->id();
        $group->save();
        return $group;
        }
}
    public function add_file_to_group_service($Data)
    {
    $group = Group::where('name', $Data['group_name'])->first();
    if (!$group) {
        return ['message' => 'This group does not exist'];
    }
    $owner_group = $group->owner_id;
    $user_id = auth()->id();

    if ($owner_group != $user_id) {
        return ['message' => 'Sorry, you cannot add a file to this group'];
    }
    $file = $Data['file'];
    $fileExtension = strtolower($file->getClientOriginalExtension());
    $allowedFileExtensions = ['txt', 'doc', 'docx', 'pdf'];
    if (!in_array($fileExtension, $allowedFileExtensions)) {
        return ['message' => 'Adding this type of file is not allowed'];
    }
    $file_name = $Data['name'] . '.' . $fileExtension;
    $file_path = 'uploads/Group_files/' . $group->name . '/' . $file_name;
    if (File::where([
        ['name', $file_name],
        ['group_id', $group->id],
    ])->exists()) {
        return ['message' => 'This file name has been taken in this group, please change it'];
    }
    $file->move(public_path("uploads/Group_files/$group->name/"), $file_name);
    $file_group = new File();
    $file_group->name = $file_name;
    $file_group->owner_file_id = auth()->id();
    $file_group->group_id = $group->id;
    $file_group->status = 'free';
    $file_group->file = $file_path;
    $file_group->save();

    return $file_group;
}
    public function delete_file_from_group_service($Data)
{
        $group=Group::where('id',$Data['group_id'])->first();
        if($group){
        $file = File::where([
            ['id', $Data['file_id']],
            ['group_id', $group->id],
        ])->first();
        if ($file){
            if ($file['status']=='free'){
                $owner_group=$group->owner_id;
                $user_id=auth()->id();
                if ($owner_group==$user_id){
                $file->delete();
                return ['message'=> "The file has been deleted successfully"];
                }
                else {
                    return ['message'=>"You do not have the right to delete this file"];
                }
            }
            else {
                return ['message'=> "This file is reserved"];
            }
        }
        else  return ['message'=> "This file does not exist"];
    }
        else  return ['message'=> "This group does not exist"];
}
    public function add_user_to_group_service($Data)
{
        $group=Group::where('id',$Data['group_id'])->first();
        $user=User::where('user_name',$Data['user_name'])->first();
        if($group && $user){
        $owner_group=$group->owner_id;
        $isexist = User_Group::where([
            ['user_id', $user->id],
            ['group_id', $group->id],
        ])->first();

        $user_id=auth()->id();
            if ($owner_group==$user_id){
                if ($isexist){
                    return ['message'=>"This user already exists"];
                }
                else {
                $user_group=new User_Group();
                $user_group->user_id=$user->id;
                $user_group->group_id=$group->id;
                $user_group->save();
                return $user_group;
                }
            }
            else {
                return ['message'=> "You do not have the right to add a user"];
            }
        }
        else {
            return ['message'=>"The operation failed because this group or user does not exist"];
        }
}
    public function delete_user_from_group_service($Data)
{
        $group=Group::where('id',$Data['group_id'])->first();
        $user=User::where('user_name',$Data['user_name'])->first();
        if($group && $user){
        $owner_group=$group->owner_id;
        $user_group = User_Group::where([
            ['user_id', $user->id],
            ['group_id', $group->id],
        ])->first();
        $user_id=auth()->id();
        $file_reseved=File::where([
            ['user_file_id', $user->id],
            ['group_id',  $group->id],
            ['status','reserved']
        ])->first();
            if ($owner_group==$user_id){
                if ($user_group){
                    if ($file_reseved){
                        return ['message'=>"This user has reserved a file, which you cannot delete"];
                    }
                    else {
                       $user_group->delete();
                       return ['message'=>'The user has been deleted successfully'];
                    }
                }
                else {
                    return ['message'=>"This user does not exist in this group"];
                }
            }
            else {
                return ['message'=>"You do not have the right to delete this user"] ;
            }
        }
        else {
            return ['message'=>"The operation failed because this group or user does not exist"];
        }
}
    public function list_user_in_my_group_service($Data)
{
        $owner_id=auth()->id();
        $user_group = User_Group::where('group_id', $Data)->get();
            $allUsers = [];
            foreach ($user_group as $user) {
                if ($user['user_id']) {
                    $users = User::where('id', $user['user_id'])->first();
                    $user['users'] = $users;
                    $allUsers[] = $users;
                }
            }
        return $allUsers;
}
    public function group_details_service($Data)
{
        $owner_id=auth()->id();
        $group_users = User_Group::where('group_id', $Data)->get();
        $group_files = File::where('group_id', $Data)->get();
        $group_info= Group::where('id',$Data)->get();
        if ($group_users) {
            $allUsers = [];
            foreach ($group_users as $user) {
                if ($user['user_id']) {
                    $users = User::where('id', $user['user_id'])->first();
                    $user['users'] = $users;
                    $allUsers[] = $users;
                }
            }
            foreach ($group_files as $file){
                if ($file->status=="reserved"){
                    $user_file = User::where('id', $file->user_file_id)->first();
                    $file['used_from']=$user_file->user_name;
                }
            }
        }
        $response=[
            'Group_info'=>$group_info,
            'group_files'=>$group_files,
            'group_users'=>$allUsers
               ];
        return $response;
}
    public function list_created_groups_service($Data)
{
        $groups=Group::where('owner_id',$Data)->get();
        foreach($groups as $group){
            $group['file_count']=count(File::where('group_id',$group->id)->get());
            $group['name_owner']=User::where('id',$Data)->first()->user_name;
            $group['users_number']=count(User_Group::where('group_id',$group->id)->get());
        }
        return $groups;
}
    public function list_joined_groups_service($Data)
{
        $groups=User_Group::where('user_id',$Data)->get();
        foreach($groups as $group){
            $group_owner=Group::where('id',$group->group_id)->first();
            $group['group_name']=$group_owner->name;
            $group['file_count']=count(File::where('group_id',$group->group_id)->get());
            $group['name_owner']=User::where('id',$group_owner->owner_id)->first()->user_name;
            $group['users_number']=count(User_Group::where('group_id',$group->group_id)->get());
        }
        return $groups;
}
    public function list_users_service()
{
    return User::get();
}
    public function read_file_from_group_service($Data)
{
        $file=File::where('id',$Data)->first();
        $group=Group::where('id',$file->group_id)->first();
        if ($file->status=='free'){
            $path = public_path() . "/uploads/Group_files/$group->name/" . $file->name;
            $content = file_get_contents($path);
            $cleanString = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
            return $cleanString;
        }
        else {
           return ['message'=>'Sorry, this file is reserved, you cannot read it'];
        }
}
    public function check_in_group_file_service($Data)
{
    $my_file = File::where('id', $Data)->first();
    $group = Group::where('id', $my_file->group_id)->first();
    $user_id = auth()->id();
    $user_info = User::where('id', $user_id)->first();

    $download_file = [];

    if ($user_info->number_of_files < 10 && $my_file->status == "free") {
        // Get the current version of the file
        $currentVersion = $my_file->version;

        // Increment the version number
        $my_file->version++;

        // Update the file with the new version and check if the update was successful
        $updated = File::where('id', $Data)
            ->where('version', $currentVersion)
            ->update([
                'status' => 'reserved',
                'user_file_id' => $user_id,
                'version' => $my_file->version,
            ]);

        if ($updated) {
            // Update the user's file count
            $user_info->number_of_files += 1;
            $user_info->save();

            $file = public_path() . "/uploads/Group_files/$group->name/" . $my_file->name;
            $this->createReport($user_id, $my_file, $user_info, $group); // إنشاء التقرير

            return ['message' => $file];
        } else {
            exit( "Sorry, this file is reserved, and you cannot reserve it");
        }
    } else {
        return ['message' => "Sorry,  you cannot reserve it"];
    }
}
    public function check_in_list_of_group_files_service($Data)
{
    $user_id = auth()->id();
    $user = User::find($user_id);
    $response['message'] = "Files have been reserved successfully";
    $response['URLs'] = [];

    foreach ($Data as $file_id) {
        $file = File::find($file_id);

        if ($file->status == "free") {
            if ($user->number_of_files >= 10) {
                exit("Exceeded maximum number of files");
            } else {
                // Get the current version of the file
                $currentVersion = $file->version;

                // Increment the version number
                $file->version++;

                // Update the file with the new version and check if the update was successful
                $updated = File::where('id', $file_id)
                    ->where('version', $currentVersion)
                    ->update([
                        'user_file_id' => $user_id,
                        'status' => 'reserved',
                        'version' => $file->version,
                    ]);

                if ($updated) {
                    $response['URLs'][] = $file->file;
                    $user->number_of_files += 1;
                    $user->save();

                    $group = Group::where('id', $file->group_id)->first();
                    $this->createReport($user_id, $file, $user, $group);
                } else {
                    exit("File $file->name has been modified by someone else");
                }
            }
        } else {
            exit("File $file->name is not free");
        }
    }

    return $response;
}
    public function check_out_group_file($Data)
{
        $file = $Data['file'];
        $file_name = $file->getClientOriginalName();
        $orginal_file=file::where('id',$Data['file_id'])->first();
        $group=Group::where('id',$orginal_file->group_id)->first();
        $user_info = User::where('id',auth()->id())->first();
        if ($orginal_file['name']==$file_name){
            $destinationPath = public_path() . "/uploads/Group_files/$group->name/";
            $file->move($destinationPath, $file_name);
             $orginal_file->status='free';
             $orginal_file->user_file_id=null;
             $orginal_file->save();

             $user_info->number_of_files=($user_info->number_of_files)-1;
             $user_info->save();
             
               //report check_out_date
               $report = Report::where([
                ['file_id', $orginal_file->id],
                ['group_id',$group->id],
                ['group_name',$group->name]
               ])->latest()->first();
               if (is_null($report->check_out_date)) {
                  $report->check_out_date=Carbon::now();
                  $report->save();
              }
              return $orginal_file;
        }
        else {
            return ['message'=>'Sorry, this file is different from the one that was reserved'];
        }
}
    public function my_reserved_file_service($Data)
{
        $public_files=Public_file::where('user_file_id',$Data)->get();
        $group_files=File::where('user_file_id',$Data)->get();
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

    public function number_of_reserved_files_service($Data)
{
        $number_of_files['number_of_reserved_files']=User::where('id',$Data)->first()->number_of_files;
        return $number_of_files;
}

    private function createReport($user_id, $my_file, $user_info, $group)
{
    $report = new Report();
    $report->user_file_id = $user_id;
    $report->owner_file_name = User::where('id', $my_file->owner_file_id)->first()->user_name;
    $report->user_name = $user_info->user_name;
    $report->group_id = $group->id;
    $report->group_name = $group->name;
    $report->file_id = $my_file->id;
    $report->file_name = $my_file->name;
    $report->check_in_date = Carbon::now();
    $report->save();
}
}
