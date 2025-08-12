# Hi-Time Project Management System

Hi-Time is a comprehensive project management application built with Laravel 12 and Livewire 3. It features task management with Kanban boards, time tracking, customer management, proposal generation, file attachments (including video support), and reporting capabilities.

## 🚀 Features

- **Project Management**: Create and manage projects with Kanban-style task boards
- **Task Management**: Drag-and-drop task organization with status tracking
- **File Attachments**: Upload documents and videos (up to 20MB for videos, 10MB for documents)
- **Time Tracking**: Built-in time tracking with detailed reporting
- **Customer Management**: Maintain customer records and relationships
- **Proposal System**: Generate, preview, and send professional proposals with PDF export
- **Lead Management**: Convert leads to customers and projects
- **User Management**: Role-based access control with admin features
- **Reporting**: Comprehensive time and project reports
- **Real-time Updates**: Livewire-powered reactive interface
- **Notifications**: Simple notification system for important events

## 📋 Requirements

- **PHP**: ^8.2
- **Composer**: Latest version
- **Node.js**: ^18.0
- **Database**: SQLite (default) or MySQL/PostgreSQL
- **Web Server**: Apache/Nginx or Laravel's built-in server

## 🛠️ Installation

### Option 1: Standard Laravel Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd hi-time.test
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

### Option 2: Docker Deployment

For production or containerized environments, use the Docker configuration:

1. **Configure PHP settings**
   
   Mount the provided `docker-configs/php-custom.conf` to your PHP-FPM container at `/usr/local/etc/php/conf.d/custom.conf` to support video uploads up to 20MB.

2. **Use Laravel Sail (recommended for development)**
   ```bash
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan migrate --seed
   ```

## ⚙️ Configuration

### Environment Variables

Key environment variables to configure:

```bash
APP_NAME="Hi-Time"
APP_URL=http://your-domain.com

# Database (SQLite is default)
DB_CONNECTION=sqlite

# Mail configuration (for proposal sending)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=your-email
```

### File Upload Configuration

The application supports differential file size limits:
- **Videos**: 20MB maximum (MP4, AVI, MOV, WMV, FLV, WebM, MKV, M4V, 3GP)
- **Documents/Images**: 10MB maximum

For production deployments, ensure your web server and PHP configuration support these limits:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20
max_execution_time = 300
memory_limit = 256M
```

## 👤 Default Users

After running the database seeder, you'll have access to default accounts:

- **Admin User**: Check the `database/seeders/DatabaseSeeder.php` for default credentials
- **Regular Users**: Additional test users may be created by the seeder

## 🎯 Getting Started

1. **Login** to the application using the default credentials
2. **Create a Customer** from the customers section
3. **Create a Project** and assign it to the customer
4. **Add Tasks** to your project using the Kanban board
5. **Upload Files** by clicking on tasks and using the file upload area
6. **Track Time** using the built-in time tracking features
7. **Generate Reports** to analyze project progress and time allocation

## 📁 Project Structure

```
app/
├── Http/Controllers/          # API and web controllers
├── Livewire/                 # Livewire components
├── Models/                   # Eloquent models
└── Services/                 # Business logic services

resources/
├── views/                    # Blade templates
├── css/                      # Styling (Tailwind CSS)
└── js/                       # Frontend JavaScript

database/
├── migrations/               # Database schema
└── seeders/                  # Sample data

docker-configs/               # Docker configuration files
```

## 🔧 Key Technologies

- **Backend**: Laravel 12, Livewire 3, PHP 8.2+
- **Frontend**: Alpine.js, Tailwind CSS 4.0, Vite
- **Database**: SQLite/MySQL with Eloquent ORM
- **File Upload**: Custom dropzone integration with differential size validation
- **PDF Generation**: DomPDF for proposal exports
- **Containerization**: Docker support with custom PHP configurations

## 📊 Available Reports

- Time tracking by customer (current/previous month)
- Time tracking by user with enhanced filtering
- Individual daily time reports
- Project progress and task completion metrics

## 🚀 Development

### Running in Development Mode

```bash
# Start the Laravel development server
php artisan serve

# Watch and compile assets
npm run dev

# Run background queue processing (if needed)
php artisan queue:work
```

### Database Management

```bash
# Create new migration
php artisan make:migration create_example_table

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database with sample data
php artisan db:seed
```

### Creating New Features

```bash
# Generate Livewire component
php artisan make:livewire ComponentName

# Generate controller
php artisan make:controller ExampleController

# Generate model with migration
php artisan make:model Example -m
```

## 🐛 Troubleshooting

### File Upload Issues

If video uploads fail:
1. Check PHP configuration limits (`upload_max_filesize`, `post_max_size`)
2. Verify web server configuration
3. Ensure storage directory permissions are correct
4. Check Laravel logs for detailed error messages

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Cache Issues

```bash
# Clear all caches
php artisan optimize:clear

# Or individually
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the Laravel framework license for details.

## 🙋‍♂️ Support

For support and questions:
- Check the Laravel documentation
- Review the Livewire documentation for component-specific issues
- Check GitHub issues for known problems
- Create a new issue for bug reports or feature requests

---

**Built with ❤️ using Laravel, Livewire, and modern web technologies**
