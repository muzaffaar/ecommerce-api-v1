<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\Task;

class TaskCreated extends Notification
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
                    ->line('A new task has been created.')
                    ->action('View Task', route('curier.tasks.show', ['task' => $this->task->id]))
                    ->line('You can accept or decline this task.');
    }

    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'message' => 'A new task has been created.',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'task_id' => $this->task->id,
            'message' => 'A new task has been created.',
        ]);
    }
}
