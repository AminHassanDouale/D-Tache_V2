<!DOCTYPE html>
<html>
<head>
    <title>New Task Created</title>
</head>
<body>
    <h1>{{ $task->name }}</h1>
    <p>Task Description: {{ $task->description }}</p>
    <p>Assigned To: {{ $assignee->name }}</p>
    <p>Created By: {{ $creator->name }}</p>
    <p>Project: {{ $project->name }}</p>

</body>
</html>
