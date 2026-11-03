# LifeLine - Blood Donation & Emergency Management System

## Features Included
1. **Donor Directory & Search:** Filter donors by Blood Group (A+, O-, etc.) and City with direct calling buttons.
2. **Emergency Blood Requests:** Citizens can raise urgent blood requests with unit count, hospital details, and urgency levels.
3. **User Authentication (Login & Register):** Secure user sessions with password hashing (`password_hash`), supporting donor registration and login.
4. **Admin Dashboard (`admin.php`):**
   - Overview statistics (Total donors, pending requests, total requests, camps).
   - Manage/Fulfill or delete emergency blood requests.
   - View registered donors directory and remove accounts if needed.
   - Manage upcoming blood donation camps.
5. **Modern Professional Front-End:** Built with Bootstrap 5 and FontAwesome icons, featuring a responsive navbar, hero banner, modals, and stat cards.

## Setup Instructions
1. Extract this ZIP file directly into your server root directory (e.g., `C:/xampp/htdocs/blood-donation/`).
2. Start **Apache** and **MySQL** in XAMPP.
3. Open `http://localhost/phpmyadmin`, create a database named `blood_donation_db`, and import `schema.sql`.
4. Open your browser and navigate to:
   `http://localhost/blood-donation/index.php`

### Default Admin Login Credentials
- **Email:** `admin@lifeline.com`
- **Password:** `admin123`
