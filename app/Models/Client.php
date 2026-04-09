<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'kap_id',
        'nama_client',
        'nama_pic',
        'no_contact',
        'alamat',
        'tahun_audit',
    ];

    protected function casts(): array
    {
        return [
            'tahun_audit' => 'integer',
        ];
    }

    public function kapProfile()
    {
        return $this->belongsTo(KapProfile::class, 'kap_id');
    }

    public function dataRequests()
    {
        return $this->hasMany(DataRequest::class);
    }
}
