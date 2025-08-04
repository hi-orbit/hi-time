<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FirebaseCloudMessagingService
{
    protected $client;
    protected $projectId;
    protected $serviceAccountPath;

    public function __construct()
    {
        $this->client = new Client();
        $this->projectId = config('services.firebase.project_id');
        $this->serviceAccountPath = storage_path('app/firebase-service-account.json');
    }

    /**
     * Get OAuth 2.0 access token for Firebase Admin SDK
     */
    private function getAccessToken()
    {
        $cacheKey = 'firebase_access_token';

        return Cache::remember($cacheKey, 3300, function () { // Cache for 55 minutes (tokens expire in 1 hour)
            try {
                // For development, we'll use a simplified approach
                // In production, you should use proper service account authentication

                // For now, let's use the legacy approach but with topics
                // This is a temporary solution until we set up service account
                return null;
            } catch (\Exception $e) {
                Log::error('Failed to get Firebase access token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Send notification using FCM topics (works without server key for now)
     */
    public function sendTaskAssignmentNotification($userId, $taskTitle, $projectName, $projectId = null)
    {
        // For now, we'll log the notification and implement browser-based notifications
        Log::info('Task assignment notification', [
            'user_id' => $userId,
            'task_title' => $taskTitle,
            'project_name' => $projectName,
            'project_id' => $projectId
        ]);

        // We'll implement this using browser-based approach instead
        return true;
    }

    public function sendTaskStatusNotification($userId, $taskTitle, $newStatus, $projectName, $projectId = null)
    {
        $statusMessages = [
            'backlog' => 'moved to backlog',
            'in_progress' => 'is now in progress',
            'in_test' => 'is ready for testing',
            'ready_to_release' => 'is ready for release',
            'done' => 'has been completed'
        ];

        $message = $statusMessages[$newStatus] ?? 'status has been updated';

        // For now, we'll log the notification
        Log::info('Task status notification', [
            'user_id' => $userId,
            'task_title' => $taskTitle,
            'new_status' => $newStatus,
            'message' => $message,
            'project_name' => $projectName,
            'project_id' => $projectId
        ]);

        return true;
    }

    /**
     * This method will be implemented once we have proper service account setup
     */
    public function subscribeToTopic($token, $topic)
    {
        Log::info('Topic subscription', [
            'token' => substr($token, 0, 20) . '...',
            'topic' => $topic
        ]);

        return true;
    }
}
