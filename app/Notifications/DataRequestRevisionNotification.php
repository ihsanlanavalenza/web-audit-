<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DataRequest;

class DataRequestRevisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $timeout = 120;
    public int $maxExceptions = 3;

    public DataRequest $dataRequest;

    public function __construct(DataRequest $dataRequest)
    {
        $this->dataRequest = $dataRequest;
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'mail',
            'database' => 'default',
        ];
    }

    public function backoff(): array
    {
        return [60, 180, 600];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Permintaan Revisi Dokumen - ' . $this->dataRequest->client->nama_client)
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Dokumen yang Anda kirimkan untuk Data Request ' . $this->dataRequest->section . ' (' . $this->dataRequest->description . ') memerlukan revisi.')
            ->line('Catatan Auditor: ' . $this->dataRequest->comment_auditor)
            ->action('Lihat Schedule', url('/schedule'))
            ->line('Mohon segera melengkapi dan mengunggah dokumen yang direvisi.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'data_request_id' => $this->dataRequest->id,
            'message' => "Revisi dibutuhkan: {$this->dataRequest->section} - {$this->dataRequest->description}",
            'comment' => $this->dataRequest->comment_auditor,
        ];
    }
}
