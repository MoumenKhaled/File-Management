<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Public_File extends Model
{
    protected $table='public_files';
    protected $fillable = ['status', 'user_file_id', 'version'];
    use HasFactory;
}
