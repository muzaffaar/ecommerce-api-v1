<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Task;

class TaskAssigned extends Notification
{
    use Queueable;

    private $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('You have been assigned a new task.')
                    ->action('View Task', route('curier.tasks.show', ['task' => $this->task->id]))
                    ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'message' => 'You have been assigned a new task.',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'task_id' => $this->task->id,
            'message' => 'You have been assigned a new task.',
        ]);
    }
}
