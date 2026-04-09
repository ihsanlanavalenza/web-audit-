<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public function markAsRead($notificationId = null)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) return;

        if ($notificationId) {
            DatabaseNotification::query()
                ->where('id', $notificationId)
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            DatabaseNotification::query()
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return view('livewire.notification-bell', [
                'notifications' => collect(),
                'unreadCount' => 0,
            ]);
        }

        $notifications = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('livewire.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
