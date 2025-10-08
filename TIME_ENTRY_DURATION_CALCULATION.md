# Time Entry Automatic Duration Calculation Implementation

## Overview
This implementation adds automatic duration calculation when editing time entries. When users edit the start time or end time fields, the duration (in minutes) is automatically recalculated and updated in real-time.

## Changes Made

### 1. Updated Livewire Components

The following Livewire components have been updated to include automatic duration calculation:

- **`app/Livewire/TimeTracking/Index.php`**
- **`app/Livewire/Reports/MyTimeToday.php`**
- **`app/Livewire/Reports/TimeByUserLivewire.php`**

### 2. New Functionality Added

#### Lifecycle Methods
- `updatedEditStartTime()` - Automatically triggered when start time changes
- `updatedEditEndTime()` - Automatically triggered when end time changes

#### Calculation Method
- `calculateDurationFromTimes()` - Private method that calculates duration from start and end times

### 3. Features Implemented

#### Real-time Calculation
- When a user changes the start time, the duration is automatically recalculated
- When a user changes the end time, the duration is automatically recalculated
- The duration field updates instantly without requiring a page refresh

#### Overnight Shift Support
- Handles cases where work spans midnight (e.g., 23:00 to 07:00)
- Automatically detects when end time is earlier than start time and adds a day

#### Error Handling
- Gracefully handles invalid time formats
- Preserves existing duration if parsing fails
- Only calculates when both start and end times are provided

#### Validation on Save
- Recalculates duration one final time before saving to ensure accuracy
- Maintains all existing validation rules

## Technical Details

### Duration Calculation Logic
```php
private function calculateDurationFromTimes()
{
    if ($this->editStartTime && $this->editEndTime) {
        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->editStartTime);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $this->editEndTime);
            
            // Handle case where end time is next day (crosses midnight)
            if ($endTime->lessThan($startTime)) {
                $endTime->addDay();
            }
            
            $diffInMinutes = $startTime->diffInMinutes($endTime);
            $this->editDuration = $diffInMinutes;
        } catch (\Exception $e) {
            // If parsing fails, don't update duration
        }
    }
}
```

### Livewire Integration
- Uses Livewire's property update lifecycle methods (`updated{PropertyName}`)
- Integrates seamlessly with existing `wire:model` bindings
- No changes required to existing Blade templates

## Files Affected

### Core Implementation
- `/app/Livewire/TimeTracking/Index.php`
- `/app/Livewire/Reports/MyTimeToday.php`
- `/app/Livewire/Reports/TimeByUserLivewire.php`

### Test Files Added
- `/tests/Unit/TimeCalculationTest.php` - Unit tests for calculation logic
- `/tests/Feature/TimeEntryEditTest.php` - Feature tests (requires database setup)
- `/database/factories/TaskNoteFactory.php` - Factory for testing

### Template Files (No Changes Required)
The following templates already use `wire:model="editDuration"` and will automatically display updated values:
- `/resources/views/livewire/time-tracking/index.blade.php`
- `/resources/views/livewire/time-tracking/index-table.blade.php`
- `/resources/views/livewire/reports/my-time-today.blade.php`
- `/resources/views/livewire/reports/time-by-user-livewire.blade.php`

## Usage Examples

### Standard Time Entry
- Start: 09:00
- End: 17:30
- **Calculated Duration: 510 minutes (8.5 hours)**

### Overnight Shift
- Start: 23:00
- End: 07:00
- **Calculated Duration: 480 minutes (8 hours)**

### Short Break
- Start: 14:00
- End: 14:30
- **Calculated Duration: 30 minutes**

## Benefits

1. **User Experience**: Eliminates manual calculation errors
2. **Efficiency**: Reduces time spent on data entry
3. **Accuracy**: Ensures consistent duration calculations
4. **Real-time Feedback**: Users see changes immediately
5. **Backward Compatibility**: Works with existing time entry workflows

## Testing

All functionality has been tested with unit tests covering:
- ✅ Basic time calculation
- ✅ Overnight shifts
- ✅ Invalid input handling
- ✅ Partial input scenarios
- ✅ Multiple component implementations

Run tests with:
```bash
php artisan test --filter=TimeCalculationTest
```

## Browser Testing

To test the functionality in a browser:

1. Navigate to any time tracking page
2. Click "Edit" on a time entry
3. Change the start time or end time
4. Observe that the duration field updates automatically
5. Save the entry to verify the calculated duration is persisted
