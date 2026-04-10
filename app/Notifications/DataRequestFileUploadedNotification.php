<?php

namespace App\Notifications;

use App\Models\DataRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DataRequestFileUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;
    public int $timeout = 120;
    public int $maxExceptions = 3;

    public function __construct(public DataRequest $dataRequest)
    {
        $this->afterCommit();
    }

    public function via($notifiable): array
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

    public function toMail($notifiable): MailMessage
    {
        $clientName = $this->dataRequest->client?->nama_client ?? '-';
        $description = $this->dataRequest->description ?: '-';

        return (new MailMessage)
            ->subject('Upload Dokumen Baru - ' . $clientName)
            ->greeting('Halo, ' . ($notifiable->name ?? 'User') . '!')
            ->line('Auditi telah mengunggah dokumen baru untuk Data Request berikut:')
            ->line('Section: ' . $this->dataRequest->section)
            ->line('Deskripsi: ' . $description)
            ->action('Lihat Schedule', url('/schedule'))
            ->line('Silakan tinjau dokumen terbaru di sistem WebAudit.');
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
