<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskStatusNotification extends Notification
{
    protected $tasks;

    public function __construct($tasks)
    {
        $this->tasks = $tasks;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
                       ->subject('Task Status Update')
                       ->greeting('Hello!')
                       ->line('Here is the update on your tasks:');

        // Build the table of tasks
        foreach ($this->tasks as $task) {
            $statusName = $task->status ? $task->status->name : 'No Status';
        
            $mailMessage->line("<tr><td>{$task->name}</td><td></td><td>{$statusName}</td></tr>");
        }
        

        return $mailMessage;
    }
}


