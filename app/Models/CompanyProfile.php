<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = ['company_name', 'address', 'logo_path', 'contact', 'date_format', 'currency'];
}