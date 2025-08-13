# IPO Pulse - India's Premier IPO Tracking Platform

🚀 **Transform your IPO investment journey with real-time data and intelligent insights.**

## 🌟 Features

### For Investors
- **Live IPO Tracking** - Real-time IPO data with status updates
- **Smart Filters** - Filter by status (Upcoming, Live, Closed)
- **Mobile-First Design** - Responsive interface for all devices
- **IPO Calendar** - Never miss an IPO with comprehensive date tracking
- **Detailed Analytics** - In-depth IPO analysis and market insights

### For Administrators
- **Admin Dashboard** - Manage IPOs and news content
- **Content Management** - Add, edit, and manage IPO listings
- **User Management** - Monitor platform usage and user engagement
- **API Integration** - Sync with external IPO data sources

## 🛠️ Technical Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **API Integration**: RapidAPI IPO endpoints
- **Mobile**: Responsive design with mobile-first approach

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP development server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/kalpitkawar/kalpesh-patel.git
   cd kalpesh-patel
   ```

2. **Setup Database**
   ```bash
   # Import the database schema
   mysql -u your_username -p < db_schema.sql
   
   # Add performance indexes
   mysql -u your_username -p < db_indexes.sql
   ```

3. **Configure Database Connection**
   ```bash
   # Copy and edit config.php with your database credentials
   cp config.php.example config.php
   # Edit config.php with your database settings
   ```

4. **Start Development Server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the Application**
   - Main site: http://localhost:8000
   - Admin panel: http://localhost:8000/admin_login.html

### Environment Variables (Production)

For production deployment, set these environment variables:

```bash
export DB_HOST=your_database_host
export DB_USER=your_database_username  
export DB_PASS=your_database_password
export DB_NAME=your_database_name
```

## 📱 API Endpoints

### Public Endpoints
- `GET /get_ipos.php` - Fetch all IPOs
- `GET /get_ipo_details.php?id={id}` - Get specific IPO details
- `GET /get_news.php` - Fetch latest IPO news
- `GET /fetch_ipo_api.php?type={type}` - External API data

### Admin Endpoints (Authentication Required)
- `POST /admin_login.php` - Admin authentication
- `POST /admin_add_ipo.php` - Add new IPO
- `GET /admin_get_ipos.php` - Get all IPOs for admin

### User Endpoints
- `POST /user_register.php` - User registration
- `POST /user_login.php` - User authentication
- `GET /check_allotment.php` - Check IPO allotment status

## 🎨 UI/UX Features

### Desktop Experience
- **Modern Table Interface** - Clean, sortable IPO listings
- **Advanced Filtering** - Multi-criteria IPO search
- **Hover Effects** - Interactive elements with smooth animations

### Mobile Experience
- **Card-Based Layout** - Touch-friendly IPO cards
- **Swipe Navigation** - Intuitive mobile gestures
- **Optimized Performance** - Fast loading on mobile networks

## 🔧 Performance Optimizations

- **Database Indexing** - Optimized queries for fast data retrieval
- **Connection Pooling** - Efficient database connection management
- **Responsive Images** - Adaptive image loading
- **CSS/JS Optimization** - Minified assets for production

## 🔒 Security Features

- **SQL Injection Protection** - Prepared statements across all queries
- **Session Management** - Secure user session handling
- **Input Validation** - Comprehensive data sanitization
- **Environment Variables** - Secure configuration management

## 📚 File Structure

```
ipo-pulse/
├── assets/              # Static assets (images, icons)
├── admin_*.php         # Admin panel backend
├── admin_*.html        # Admin panel frontend
├── admin_*.js          # Admin panel JavaScript
├── user_*.php          # User management backend
├── *.html              # Main application pages
├── *.js                # Frontend JavaScript
├── style.css           # Main stylesheet
├── config.php          # Database configuration
├── db_schema.sql       # Database schema
├── db_indexes.sql      # Performance indexes
└── README.md           # This file
```

## 🧪 Testing

### Manual Testing
```bash
# Test PHP syntax
find . -name "*.php" -exec php -l {} \;

# Test database connection
php -r "require 'config.php'; $conn = get_db_connection(); echo 'Connection successful';"
```

### API Testing
```bash
# Test public endpoints
curl http://localhost:8000/get_ipos.php
curl http://localhost:8000/get_news.php
```

## 🚀 Deployment

### Production Checklist
- [ ] Set environment variables for database credentials
- [ ] Configure web server (Apache/Nginx)
- [ ] Enable SSL/HTTPS
- [ ] Set up database backups
- [ ] Configure error logging
- [ ] Enable gzip compression
- [ ] Set up monitoring

### Hosting Platforms
- **Shared Hosting**: Compatible with most PHP hosting providers
- **VPS/Cloud**: Recommended for production use
- **Docker**: Container configuration available

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check this README and code comments
- **Issues**: Report bugs via GitHub Issues
- **Contact**: [Email Support](mailto:support@ipopulse.com)

## 🏆 Roadmap

### Phase 1 (Current) ✅
- [x] Core IPO tracking functionality
- [x] Mobile-responsive design
- [x] Admin panel
- [x] API integration

### Phase 2 (Next)
- [ ] User authentication system
- [ ] IPO alerts and notifications
- [ ] Advanced analytics dashboard
- [ ] Portfolio tracking

### Phase 3 (Future)
- [ ] Mobile app (React Native)
- [ ] Real-time WebSocket updates
- [ ] AI-powered IPO recommendations
- [ ] Social features and community

---

**Made with ❤️ for Indian investors by the IPO Pulse team**