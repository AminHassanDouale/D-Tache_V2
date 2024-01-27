<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class TaskNotification extends Notification
{
    private $tasks;

    public function __construct(Collection $tasks)
    {
        $this->tasks = $tasks;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $taskTableContent = $this->tasks->reduce(function ($carry, $task) {
            $projectName = $task->project ? $task->project->name : 'No Project';

            return $carry . "<tr><td>{$task->name}</td><td>{$projectName}</td><td>{$task->status->name}</td></tr>";
        }, '');

        $taskTableHtml = "<table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <th>Task Name</th>
                                <th>Project</th>
                                <th>Status</th>
                            </tr>
                            {$taskTableContent}
                          </table>";

        return (new MailMessage)
                    ->subject('Task Update')
                    ->greeting('Hello!')
                    ->line('There is an update on your tasks:')
                    ->line(new HtmlString($taskTableHtml))
                    ->line('Please check the tasks for more details.');
}
}