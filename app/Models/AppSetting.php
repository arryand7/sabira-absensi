<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'sso_base_url',
        'sso_client_id',
        'sso_client_secret',
        'sso_redirect_uri',
        'sso_scopes',
        'app_name',
        'app_description',
        'app_logo',
        'app_favicon',
    ];
}
