# Browser Notifications for Hi-Time

This project uses simple browser notifications to notify users when tasks are assigned or their status changes. This approach works immediately without requiring complex Firebase setup or server keys.

## How It Works

### ✅ **Immediate Benefits**
- **No Setup Required**: Works out of the box with any modern browser
- **No External Dependencies**: Uses built-in browser Notification API
- **No Costs**: Completely free, no third-party services
- **Cross-Browser**: Works on Chrome, Firefox, Safari, Edge
- **Real-Time**: Notifications appear instantly when actions occur

### 🔔 **Notification Triggers**

The system automatically shows browser notifications for:

1. **Task Assignment**: When a task is assigned to a user
2. **Task Reassignment**: When a task is reassigned from one user to another
3. **Status Changes**: When a task's status changes (via drag-drop or editing)

### 🛠 **Technical Implementation**

#### Backend Services

1. **NotificationService** (`app/Services/NotificationService.php`):
   - Creates notification data when events occur
   - Stores notifications in session for immediate retrieval
   - Provides API endpoints for frontend polling

2. **Livewire Integration** (`app/Livewire/Projects/Show.php`):
   - Automatically triggers notifications when tasks are created/updated
   - Tracks assignment and status changes
   - Only notifies relevant users

#### Frontend Integration

1. **Browser Notification API**:
   - Requests permission on page load
   - Shows native browser notifications
   - Handles click actions to navigate to relevant pages

2. **Auto-Polling System**:
   - Checks for new notifications every 10 seconds
   - Immediately displays any pending notifications
   - Clears notifications after showing

## Setup & Usage

### 🚀 **Getting Started**

1. **No Configuration Needed**: The system works immediately
2. **Permission Request**: Users will be prompted to allow notifications on first visit
3. **Automatic Operation**: Notifications appear when tasks are assigned or updated

### 🧪 **Testing Notifications**

To test the notification system:

1. **Allow Notifications**: 
   - Visit your Hi-Time application
   - Click "Allow" when prompted for notification permissions

2. **Create Test Scenario**:
   - Login as an admin or user
   - Create a new task and assign it to another user
   - Switch to that user's account (or use another browser/incognito)
   - You should see a browser notification within 10 seconds

3. **Test Status Changes**:
   - Drag a task to a different column (status change)
   - The assigned user should receive a status update notification

### 🔧 **Customization**

#### Notification Frequency
Change the polling interval in `resources/views/layouts/app.blade.php`:
```javascript
// Check for notifications every 5 seconds instead of 10
setInterval(checkPendingNotifications, 5000);
```

#### Notification Content
Modify messages in `app/Services/NotificationService.php`:
```php
'title' => 'Custom Task Assigned Title',
'message' => "Custom message: {$taskTitle} in {$projectName}",
```

#### Notification Behavior
Customize notification appearance and behavior:
```javascript
const notification = new Notification(title, {
    body: message,
    icon: '/custom-icon.png',
    tag: 'hi-time-notification',
    requireInteraction: true, // Keep notification until user interacts
    silent: false // Play notification sound
});
```

## Browser Compatibility

### ✅ **Supported Browsers**
- **Chrome 50+**: Full support
- **Firefox 44+**: Full support  
- **Safari 13+**: Full support
- **Edge 79+**: Full support

### ⚠️ **Limitations**
- **HTTPS Required**: Notifications only work on HTTPS in production (HTTP works on localhost)
- **Permission Required**: Users must grant notification permission
- **Active Tab**: Some browsers limit notifications when the tab is not active

## Troubleshooting

### 🔍 **Common Issues**

1. **No Notifications Appearing**:
   - Check if notifications are allowed in browser settings
   - Look for the permission prompt that might have been dismissed
   - Open browser console and check for any JavaScript errors

2. **Notifications Not Working in Production**:
   - Ensure your site is served over HTTPS
   - Check that notification permissions are granted
   - Verify that the polling endpoints are accessible

3. **Delayed Notifications**:
   - Default polling is every 10 seconds - this is normal
   - Reduce polling interval if immediate notifications are needed
   - Consider using WebSockets for real-time updates (future enhancement)

### 🐛 **Debugging**

1. **Check Browser Console**:
   ```javascript
   // Check if notifications are supported
   console.log('Notification' in window);
   
   // Check permission status
   console.log(Notification.permission);
   ```

2. **Test API Endpoints**:
   ```bash
   # Check if notifications are being created
   curl -X GET "http://your-app.com/api/notifications/pending" \
        -H "Cookie: your_session_cookie"
   ```

3. **Laravel Logs**:
   - Check `storage/logs/laravel.log` for notification creation logs
   - Look for any errors in the NotificationService

## Future Enhancements

### 🚀 **Possible Improvements**

1. **Real-Time WebSockets**:
   - Replace polling with Laravel Echo/Pusher for instant notifications
   - Reduce server load and improve responsiveness

2. **Persistent Notifications**:
   - Store notifications in database for notification history
   - Allow users to mark notifications as read/unread

3. **Rich Notifications**:
   - Add action buttons to notifications (Mark Complete, View Task)
   - Include task thumbnails or project icons

4. **Email Fallback**:
   - Send email notifications if browser notifications fail
   - Provide user preference settings

### 🔧 **Advanced Configuration**

For high-traffic sites, consider:
- **Notification Batching**: Group multiple notifications together
- **Rate Limiting**: Limit notification frequency per user
- **Selective Notifications**: Allow users to choose notification types

## Migration from Firebase

This simple approach replaces the complex Firebase setup with:
- ✅ **Simpler Implementation**: No external service configuration
- ✅ **Better Reliability**: No dependency on third-party services
- ✅ **Easier Maintenance**: Pure JavaScript/PHP implementation
- ✅ **Cost Effective**: No external service costs
- ✅ **Privacy Friendly**: No data sent to external services

The notification functionality is identical to Firebase but with better simplicity and reliability.

## Security Considerations

- **Session-Based**: Notifications are stored in user sessions, automatically cleaned up
- **User Verification**: Only authenticated users can access notification endpoints
- **CSRF Protection**: All API endpoints are CSRF protected
- **Data Privacy**: No notification data is sent to external services
- **Permission Respect**: Respects user's browser notification preferences
