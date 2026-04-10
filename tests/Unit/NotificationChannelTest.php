<?php

namespace Tests\Unit;

use App\Models\DataRequest;
use App\Notifications\DataRequestFileUploadedNotification;
use App\Notifications\DataRequestRevisionNotification;
use Tests\TestCase;

class NotificationChannelTest extends TestCase
{
    public function test_data_request_file_uploaded_notification_uses_mail_and_database_channels(): void
    {
        $notification = new DataRequestFileUploadedNotification(new DataRequest());

        $this->assertSame(['mail', 'database'], $notification->via(new \stdClass()));
    }

    public function test_data_request_revision_notification_uses_mail_and_database_channels(): void
    {
        $notification = new DataRequestRevisionNotification(new DataRequest());

        $this->assertSame(['mail', 'database'], $notification->via(new \stdClass()));
    }
}
