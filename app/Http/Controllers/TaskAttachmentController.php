<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachmentController extends Controller
{
    public function upload(Request $request, Task $task)
    {
        $request->validate([
            'attachment' => 'required|file|max:2048|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,xlsx,xls,zip,rar',
        ]);

        try {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('task-attachments', $fileName, 'public');

            TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => Auth::id(),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return back()->with('success', "File '{$originalName}' uploaded successfully!");

        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function dropzoneUpload(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:2048|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,xlsx,xls,zip,rar',
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('task-attachments', $fileName, 'public');

            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => Auth::id(),
                'original_name' => $originalName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "File '{$originalName}' uploaded successfully!",
                'attachment' => $attachment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
