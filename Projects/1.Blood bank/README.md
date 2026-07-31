# E Blood Connect

E Blood Connect is a comprehensive blood donation management system that connects blood donors with people in need. The platform facilitates blood donations, blood requests, and blood bank management.

## Features

- **User Registration and Authentication**: Secure registration and login system with age verification (18+ only).
- **Blood Request System**: Users can request blood and view active blood requests.
- **Donation System**: Users can donate blood to specific requests or to the blood bank.
- **Blood Bank**: Central repository of blood inventory managed by administrators.
- **Admin Dashboard**: Comprehensive admin interface for managing users, requests, donations, and blood inventory.
- **User Profiles**: Users can manage their profiles and view their donation/request history.
- **Compatibility Checking**: Automatic blood type compatibility verification when responding to blood requests.
- **Responsive Design**: Mobile-friendly interface that works on all devices.

## Technologies Used

- PHP 7.4+
- MySQL/MariaDB
- HTML5
- CSS3
- JavaScript
- Font Awesome

## Installation and Setup

### Prerequisites

- Web server (Apache/Nginx)
- PHP 7.4 or higher
- MySQL/MariaDB
- Web browser

### Installation Steps

1. **Clone or download** the repository to your web server's document root (e.g., htdocs for XAMPP, www for WAMP).

2. **Create a database** named `ebloodconnect` in your MySQL/MariaDB server.

3. **Navigate to the project folder** in your web browser:
   ```
   http://localhost/ebloodconnect
   ```

4. **Run the database setup script** by visiting:
   ```
   http://localhost/ebloodconnect/db_setup.php
   ```
   This will create all necessary tables and an admin user.

5. **Default Admin Credentials**:
   - Email: admin@ebloodconnect.com
   - Password: admin123

## Project Structure

```
ebloodconnect/
├── admin/              # Admin panel files
├── css/                # Stylesheets
├── images/             # Image assets
├── includes/           # PHP include files
│   ├── db.php          # Database connection
│   ├── header.php      # Site header
│   └── footer.php      # Site footer
├── js/                 # JavaScript files
├── about.php           # About page
├── blood_bank.php      # Blood bank page
├── db_setup.php        # Database setup script
├── donate.php          # Donation page
├── index.php           # Homepage
├── login.php           # Login page
├── logout.php          # Logout script
├── profile.php         # User profile page
├── register.php        # Registration page
├── request_blood.php   # Blood request page
├── respond_request.php # Respond to request page
└── README.md           # This file
```

## Usage

### For Users

1. **Register** and create an account (must be 18+).
2. **Login** with your credentials.
3. **Request Blood** if you or someone you know needs blood.
4. **Donate Blood** by responding to blood requests or donating to the blood bank.
5. **View Your Profile** to track your donation history and request history.

### For Administrators

1. **Login** with admin credentials.
2. **Access Admin Panel** from the user dropdown menu.
3. **Manage Users** - View, edit, or delete user accounts.
4. **Manage Blood Requests** - Approve, reject, or track blood requests.
5. **Manage Donations** - Approve or reject blood donations.
6. **Manage Blood Bank** - Update blood inventory levels.

## Security Considerations

- All user passwords are securely hashed.
- Input validation is implemented to prevent SQL injection.
- User authentication checks are in place for all secure pages.
- Age verification ensures only adults (18+) can register.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-source and available under the MIT License.

## Contact

For any inquiries, please contact info@ebloodconnect.com 