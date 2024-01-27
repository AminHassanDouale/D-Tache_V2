<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskNotification; // You'll create this notification
use Illuminate\Support\Facades\Notification;

class SendTaskNotification extends Command
{
    protected $signature = 'task:notify';
    protected $description = 'Send notifications for tasks where status_id is not 3';

    public function handle()
    {
        $tasks = Task::with('status')
             ->where('status_id', '!=', 3)
             ->get();

$groupedTasks = $tasks->groupBy('assignee_id');

foreach ($groupedTasks as $assigneeId => $tasks) {
    $assignee = User::find($assigneeId);
    if ($assignee) {
        Notification::send($assignee, new TaskNotification($tasks));
        }


    }  }
    
}
