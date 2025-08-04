<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendTaskAssignmentNotification($userId, $taskTitle, $projectName, $projectId = null)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Create notification in database
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => 'task_assignment',
            'title' => 'New Task Assigned',
            'message' => "You've been assigned to task: {$taskTitle} in project: {$projectName}",
            'data' => [
                'task_title' => $taskTitle,
                'project_name' => $projectName,
                'project_id' => $projectId,
                'url' => $projectId ? "/projects/{$projectId}" : '/dashboard',
            ],
            'is_read' => false
        ]);

        Log::info('Task assignment notification created', [
            'notification_id' => $notification->id,
            'user_id' => $userId,
            'task_title' => $taskTitle,
            'project_name' => $projectName
        ]);

        return true;
    }

    public function sendTaskStatusNotification($userId, $taskTitle, $newStatus, $projectName, $projectId = null)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $statusMessages = [
            'backlog' => 'moved to backlog',
            'in_progress' => 'is now in progress',
            'in_test' => 'is ready for testing',
            'ready_to_release' => 'is ready for release',
            'done' => 'has been completed'
        ];

        $message = $statusMessages[$newStatus] ?? 'status has been updated';

        // Create notification in database
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => 'task_status_update',
            'title' => 'Task Status Update',
            'message' => "Task '{$taskTitle}' {$message} in project: {$projectName}",
            'data' => [
                'task_title' => $taskTitle,
                'new_status' => $newStatus,
                'project_name' => $projectName,
                'project_id' => $projectId,
                'url' => $projectId ? "/projects/{$projectId}" : '/dashboard',
            ],
            'is_read' => false
        ]);

        Log::info('Task status notification created', [
            'notification_id' => $notification->id,
            'user_id' => $userId,
            'task_title' => $taskTitle,
            'new_status' => $newStatus
        ]);

        return true;
    }

    public function getPendingNotifications($userId)
    {
        $notifications = Notification::unread()
            ->forUser($userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'created_at' => $notification->created_at->toISOString(),
                'url' => $notification->data['url'] ?? '/dashboard'
            ];
        })->toArray();
    }

    public function markNotificationAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    public function markAllAsRead($userId)
    {
        Notification::unread()
            ->forUser($userId)
            ->update(['is_read' => true]);
    }

    public function clearPendingNotifications()
    {
        // This method is kept for backward compatibility but is no longer needed
        // since we're using database storage
    }
}
