# IPO Pulse - Live IPO Tracking Platform

A robust IPO tracking platform that displays live IPO data from external APIs with comprehensive error handling and fallback mechanisms.

## 🚀 Features

- **Live IPO Data**: Fetches real-time IPO data from external APIs
- **Robust Error Handling**: Graceful degradation when APIs are unavailable
- **Fallback Data System**: Shows sample data when live data is unavailable
- **Advanced Filtering**: Filter IPOs by status (All/Open/Upcoming/Closed)
- **Real-time Search**: Search IPOs by company name
- **Responsive Design**: Mobile-friendly interface
- **Health Monitoring**: Built-in health check endpoint
- **Automated Sync**: Periodic data synchronization

## 🏗️ System Architecture

### Backend Components

- **fetch_ipo_api.php**: Enhanced API fetching with retry logic and fallback
- **sync_api_ipos.php**: Database synchronization with comprehensive error handling
- **get_ipos.php**: Database IPO retrieval with SQLite fallback
- **health_check.php**: System health monitoring endpoint
- **periodic_sync.php**: Automated periodic synchronization
- **config.php**: Environment-aware database configuration

### Frontend Components

- **index.html**: Main IPO listing page
- **app.js**: Enhanced JavaScript with error handling and user feedback
- **ipo.html/ipo.js**: Individual IPO details page
- **admin_*.php**: Admin interface for IPO management

### Database

- **Primary**: MySQL/MariaDB for production
- **Fallback**: SQLite for local development and backup
- **Auto-initialization**: Creates tables and sample data automatically

## 🛠️ Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB (optional - SQLite fallback available)
- Web server (Apache/Nginx or PHP built-in server)
- cURL extension enabled

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd kalpesh-patel
   ```

2. **Start development server**
   ```bash
   php -S localhost:8080
   ```

3. **Access the application**
   - Main site: http://localhost:8080/index.html
   - Health check: http://localhost:8080/health_check.php
   - Admin panel: http://localhost:8080/admin_dashboard.html

### Production Setup

1. **Configure database** in `config.php` for production environment
2. **Set up cron job** for periodic sync:
   ```bash
   # Add to crontab (runs every 5 minutes)
   */5 * * * * /usr/bin/php /path/to/periodic_sync.php
   ```
3. **Configure web server** to point to the project directory

## 📊 API Integration

### External API

The system integrates with the RapidAPI Indian IPOs service:
- **Upcoming IPOs**: `https://indian-ipos1.p.rapidapi.com/upcoming-ipos`
- **Closed IPOs**: `https://indian-ipos1.p.rapidapi.com/closed-ipos`

### Fallback Mechanism

When external APIs fail, the system:
1. Shows warning message to users
2. Returns sample IPO data to maintain functionality
3. Logs all failures for monitoring
4. Continues normal operation with cached/database data

## 🔧 Configuration

### Database Configuration

The system automatically detects the environment:
- **Local Development**: Uses SQLite fallback if MySQL unavailable
- **Production**: Uses configured MySQL/MariaDB settings

### API Configuration

Add backup API keys in `fetch_ipo_api.php`:
```php
$api_keys = [
    'primary-api-key',
    'backup-api-key-1',
    'backup-api-key-2'
];
```

## 📈 Monitoring & Health Checks

### Health Check Endpoint

Access `/health_check.php` for system status:
```json
{
    "timestamp": "2025-08-13 17:00:00",
    "database": {"status": "connected", "ipo_count": 15},
    "api_endpoints": {
        "upcoming": {"status": "ok", "http_code": 200},
        "closed": {"status": "error", "error": "API timeout"}
    },
    "overall_status": "partial"
}
```

### Logging

- **API Calls**: Logged to database `api_logs` table
- **Sync Operations**: Logged to `sync.log` file
- **Errors**: Logged via PHP error_log

## 🧪 Testing

### Manual Testing

1. **Test API endpoints**:
   ```bash
   curl http://localhost:8080/fetch_ipo_api.php?type=upcoming
   curl http://localhost:8080/get_ipos.php
   ```

2. **Test health check**:
   ```bash
   curl http://localhost:8080/health_check.php
   ```

3. **Test periodic sync**:
   ```bash
   php periodic_sync.php
   ```

### Error Scenarios

The system is designed to handle:
- API service outages
- Network connectivity issues
- Database connection failures
- Invalid API responses
- Rate limiting

## 🔄 Maintenance

### Regular Tasks

1. **Monitor health check endpoint** for system status
2. **Review sync logs** for API performance
3. **Clean up old log entries** (automatic in periodic_sync.php)
4. **Update API keys** if needed

### Troubleshooting

- **Empty data**: Check health endpoint and API logs
- **Database errors**: Verify database connectivity
- **API failures**: Check API key validity and rate limits

## 🚦 System Status Indicators

- **🟢 Healthy**: All systems operational
- **🟡 Partial**: Some APIs degraded but functional
- **🔴 Degraded**: Major issues, fallback mode active

## 📝 Recent Improvements

- Enhanced error handling and logging
- SQLite fallback database support
- User-friendly status messaging
- Comprehensive health monitoring
- Automated periodic synchronization
- Robust API retry mechanisms

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make changes with proper error handling
4. Test thoroughly including error scenarios
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.