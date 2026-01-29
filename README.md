# 📚 BookHub - Second-Hand Book Marketplace

##  Project Overview
BookHub is a comprehensive web-based platform designed for buying and selling second-hand books. It serves as a marketplace connecting students and book enthusiasts, making quality literature more accessible and affordable.

##  Live Demo
**Website URL:** http://bookmarket2025.gt.tc

##  Key Features

### 👤 User Features
- **User Registration & Authentication**
  - Secure signup and login system
  - Password encryption
  - Session management
  
- **Book Browsing**
  - Search books by title, author, or ISBN
  - Filter by category
  - View detailed book information
  - See book condition ratings

- **Shopping Experience**
  - Add books to cart
  - Adjust quantities
  - Multiple payment options
  - Order tracking
  
- **Seller Dashboard**
  - List books for sale
  - Upload book images
  - Set pricing
  - Manage inventory
  - Track sales

###  Admin Features
- **Complete Dashboard**
  - View statistics (users, books, orders, revenue)
  - Approve/reject book listings
  - Manage users
  - Process orders
  - Update order status

##  Technologies Used

### Frontend
- HTML5
- CSS3 (Custom gradient design)
- JavaScript (Form validation)

### Backend
- PHP 8.x
- MySQL Database

### Hosting
- Platform: InfinityFree
- Database: MySQL
- Server: Apache

##  Database Structure

### Main Tables
- **users** - User accounts and profiles
- **books** - Book listings and details
- **orders** - Purchase transactions
- **cart** - Shopping cart items
- **categories** - Book categories
- **reviews** - User reviews (optional)

##  Installation Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional)

### Setup Steps

1. **Clone the repository**
```bash
\
git clone https://github.com/chethani-spec/bookhub-bookhub-secondhand-books/README.md
cd bookhub-project
```

2. **Create database**
```sql
CREATE DATABASE bookhub_db;
```

3. **Import database**
- Open phpMyAdmin
- Select `bookhub_db` database
- Import `bookhub_db.sql` file

4. **Configure database connection**
Edit `includes/config.php`:
```php
// BEFORE (Local XAMPP):
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty for XAMPP default
define('DB_NAME', 'bookhub_db');
define('BASE_URL', 'http://localhost/bookhub/'); 

// AFTER (InfinityFree hosting):
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_40734059');
define('DB_PASS', 'BookHub2025');
define('DB_NAME', 'if0_40734059_bookhub');
define('BASE_URL', 'http://bookmarket2025.gt.tc/');

5. **Set file permissions**
```bash
chmod 755 uploads/
chmod 755 uploads/books/
```

6. **Access the website**
- Local: `http://localhost/bookhub/`
- Live: `http://bookmarket2025.gt.tc`

## 🔐 Default Admin Login
```
Username: admin
Email: admin@gmail.com
Password: admin123
```

> ⚠️ **Important:** Change admin password after first login!

## 📱 User Roles

### 1. Buyer
- Browse and search books
- Add books to cart
- Place orders
- Track order status

### 2. Seller
- List books for sale
- Manage inventory
- View sales
- Update order status

### 3. Admin
- Approve book listings
- Manage all users
- Oversee all orders
- View platform statistics

## 🎓 Academic Information

**Project Details:**
- Module: [Web Development ]
- Course: [Diploma in Computer Science with Artificial intelligence ]
- Semester: [2nd Semester]
- Year: 2025

**Group Members:**
1. Chethani Thakshila  - ID: [KIC-DCSAI-251-048]
2. Abhimani Konara - ID: [KIC-DCSAI-251-028]
3. Isurika Bandara  - ID: [KIC-DCSAI-251-029]


## 🐛 Known Issues & Future Improvements

### Current Limitations
- No email notifications
- No payment gateway integration
- Limited search filters

### Planned Features
- Email verification
- Advanced search with more filters
- Real-time chat between buyers and sellers
- Rating and review system
- Wishlist functionality
- Mobile app version

## 📝 License

This project is created for educational purposes.

## 📧 Contact

For queries or support:
- Email: [your-email@example.com]
- Project Lead: [Name]

## 🙏 Acknowledgments

- Thanks to NIBM-KIC
- Thanks to our module lecturer
- Thanks to all team members

---

**© 2025 BookHub Project. All rights reserved.**
```
