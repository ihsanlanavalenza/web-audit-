<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kap_id
 * @property string $nama_client
 * @property string $nama_pic
 * @property string $no_contact
 * @property string|null $alamat
 * @property Carbon|null $tahun_audit
 */
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
            'tahun_audit' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Client $client) {
            $client->syncKapOwnerAccess();
        });

        static::updated(function (Client $client) {
            if ($client->wasChanged('kap_id')) {
                $client->syncKapOwnerAccess((int) $client->getOriginal('kap_id'));
            }
        });
    }

    public function kapProfile()
    {
        return $this->belongsTo(KapProfile::class, 'kap_id');
    }

    public function dataRequests()
    {
        return $this->hasMany(DataRequest::class);
    }

    public function authorizedUsers()
    {
        return $this->belongsToMany(User::class, 'client_user_access')
            ->withTimestamps();
    }

    public function syncKapOwnerAccess(?int $oldKapId = null): void
    {
        $newOwnerId = KapProfile::query()->whereKey($this->kap_id)->value('user_id');
        if ($newOwnerId) {
            $this->authorizedUsers()->syncWithoutDetaching([(int) $newOwnerId]);
        }

        if ($oldKapId && $oldKapId !== (int) $this->kap_id) {
            $oldOwnerId = KapProfile::query()->whereKey($oldKapId)->value('user_id');
            if ($oldOwnerId && (int) $oldOwnerId !== (int) $newOwnerId) {
                $this->authorizedUsers()->detach([(int) $oldOwnerId]);
            }
        }
    }
}
