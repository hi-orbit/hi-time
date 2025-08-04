<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseCloudMessagingService
{
    protected $client;
    protected $serverKey;
    protected $projectId;

    public function __construct()
    {
        $this->client = new Client();
        $this->serverKey = config('services.firebase.server_key');
        $this->projectId = config('services.firebase.project_id');
    }

    public function sendTaskAssignmentNotification($userId, $taskTitle, $projectName, $projectId = null)
    {
        try {
            $response = $this->client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => '/topics/user_' . $userId,
                    'notification' => [
                        'title' => 'New Task Assigned',
                        'body' => "You've been assigned to task: {$taskTitle} in project: {$projectName}",
                        'icon' => '/favicon.ico',
                        'click_action' => config('app.url') . ($projectId ? "/projects/{$projectId}" : ''),
                    ],
                    'data' => [
                        'type' => 'task_assignment',
                        'user_id' => (string)$userId,
                        'task_title' => $taskTitle,
                        'project_name' => $projectName,
                        'project_id' => (string)$projectId,
                        'url' => config('app.url') . ($projectId ? "/projects/{$projectId}" : ''),
                    ]
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('FCM notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendTaskStatusNotification($userId, $taskTitle, $newStatus, $projectName, $projectId = null)
    {
        try {
            $statusMessages = [
                'backlog' => 'moved to backlog',
                'in_progress' => 'is now in progress',
                'in_test' => 'is ready for testing',
                'ready_to_release' => 'is ready for release',
                'done' => 'has been completed'
            ];

            $message = $statusMessages[$newStatus] ?? 'status has been updated';

            $response = $this->client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => '/topics/user_' . $userId,
                    'notification' => [
                        'title' => 'Task Status Update',
                        'body' => "Task '{$taskTitle}' {$message} in project: {$projectName}",
                        'icon' => '/favicon.ico',
                        'click_action' => config('app.url') . ($projectId ? "/projects/{$projectId}" : ''),
                    ],
                    'data' => [
                        'type' => 'task_status_update',
                        'user_id' => (string)$userId,
                        'task_title' => $taskTitle,
                        'new_status' => $newStatus,
                        'project_name' => $projectName,
                        'project_id' => (string)$projectId,
                        'url' => config('app.url') . ($projectId ? "/projects/{$projectId}" : ''),
                    ]
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('FCM notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function subscribeToTopic($token, $topic)
    {
        try {
            $response = $this->client->post('https://iid.googleapis.com/iid/v1/' . $token . '/rel/topics/' . $topic, [
                'headers' => [
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('FCM topic subscription failed: ' . $e->getMessage());
            return false;
        }
    }
}
