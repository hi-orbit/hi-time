# Docker Configuration for Hi-Time Application

## PHP Configuration

The application supports file uploads with the following size limits:
- **Images and Documents**: Up to 10MB
- **Videos**: Up to 20MB

### Required PHP Settings

For video upload support, ensure your PHP configuration includes:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20
max_execution_time = 300
memory_limit = 256M
```

### Implementation

1. **Apache**: The `.htaccess` file in the `public` directory includes these settings
2. **Nginx**: Use the `php-custom.conf` file and mount it to your PHP-FPM container
3. **Docker/Sail**: Mount `php-custom.conf` to `/usr/local/etc/php/conf.d/custom.conf`

### Supported Video Formats

- MP4 (recommended)
- AVI
- MOV
- WMV
- FLV
- WebM
- MKV
- M4V
- 3GP
