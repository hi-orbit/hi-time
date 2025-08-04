# Firebase Setup - Next Steps

## ✅ Completed
- Firebase project created: `hi-time-e7056`
- Web app configuration obtained
- Environment variables updated with web config

## 🔄 Still Needed

### 1. Get Firebase Server Key (Legacy)

**Important**: You need the Firebase Server Key to send notifications from your Laravel backend.

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project: `hi-time-e7056`
3. Click the **Settings gear icon** → **Project settings**
4. Go to the **Cloud Messaging** tab
5. Look for **"Project credentials"** section
6. Copy the **"Server key"** (this is a long string starting with something like `AAAA...`)

### 2. Generate VAPID Key

1. In the same **Cloud Messaging** tab
2. Scroll down to **"Web configuration"**
3. If you don't see a key pair, click **"Generate key pair"**
4. Copy the **"Key pair"** value (this is your VAPID key)

### 3. Update Configuration

Once you have both keys:

1. **Update .env file**:
   ```env
   FIREBASE_SERVER_KEY=AAAA_your_actual_server_key_here
   ```

2. **Update layout file** with VAPID key:
   - Open `resources/views/layouts/app.blade.php`
   - Find the line with `YOUR_VAPID_KEY`
   - Replace it with your actual VAPID key

### 4. Enable Cloud Messaging

Make sure Cloud Messaging is enabled:
1. In Firebase Console, go to **Build** → **Cloud Messaging**
2. If prompted, click **"Enable"** or **"Get started"**

## 🧪 Testing

After completing the above steps:

1. Start your Laravel development server
2. Visit your Hi-Time application
3. Allow notifications when prompted
4. Create a task and assign it to a user
5. Check if notifications are received

## 🔍 Troubleshooting

If notifications don't work:

1. **Check browser console** for any Firebase errors
2. **Check Laravel logs** (`storage/logs/laravel.log`) for FCM API errors
3. **Verify server key** is correct in `.env`
4. **Check notification permissions** in browser settings

## 📞 Current Status

Your Firebase project is set up with:
- ✅ Project ID: `hi-time-e7056`
- ✅ Web API Key: `AIzaSyB8uycMx1nmJVvU6yMI0hZNAvX6LzumKQQ`
- ✅ Messaging Sender ID: `372998319073`
- ✅ App ID: `1:372998319073:web:8e191e35d3f215b54b40a0`
- ❌ Server Key: Still needed
- ❌ VAPID Key: Still needed
