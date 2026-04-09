<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 */
class KapProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_kap',
        'nama_pic',
        'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'kap_id');
    }

    public function dataRequests()
    {
        return $this->hasMany(DataRequest::class, 'kap_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'kap_id');
    }
}
