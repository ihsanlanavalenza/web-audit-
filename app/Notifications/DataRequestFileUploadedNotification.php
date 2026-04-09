<?php

namespace App\Notifications;

use App\Models\DataRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DataRequestFileUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(public DataRequest $dataRequest) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $versionCount = is_array($this->dataRequest->input_file)
            ? count($this->dataRequest->input_file)
            : 0;

        return [
            'message'         => "Auditi mengunggah dokumen (v{$versionCount}) pada Data Request {$this->dataRequest->section}",
            'data_request_id' => $this->dataRequest->id,
        ];
    }
}
