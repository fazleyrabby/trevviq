<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VideoLiked extends Notification
{
    use Queueable;

    public function __construct(public User $liker, public Video $video)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_username' => $this->liker->username,
            'video_id' => $this->video->id,
            'video_title' => $this->video->title,
            'message' => $this->liker->name . ' liked your video.',
        ];
    }
}
