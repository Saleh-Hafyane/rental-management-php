
# RentNow - Car & Apartment Rental Platform

RentNow is a web-based platform for renting cars and apartments. It provides a fast, secure, and transparent way for users to find and book rental properties.

## Features

- **User Authentication:** Secure login and registration system with password hashing.
- **Property Management:** Admins and agents can add, edit, and delete properties (cars and apartments).
- **Search and Filter:** Users can easily search for properties by name and filter by type.
- **Reservation System:** Users can book available properties for specific dates.
- **Admin Dashboard:** Admins have access to a dashboard with key statistics, reservation history, and user management.
- **Responsive Design:** The website is fully responsive and works on all devices.

## Technologies Used

- **Frontend:** HTML, CSS, Bootstrap, JavaScript
- **Backend:** PHP
- **Database:** MySQL

## How to Install

1.  **Clone the repository:**
    ```
    git clone https://github.com/Saleh-Hafyane/rental-management-php.git
    ```
2.  **Import the database:**
    - Create a new database named `location_app` in your MySQL server.
    - Import the `database.sql` file into the `location_app` database.
3.  **Configure the database connection:**
    - Open the `config/db.php` file and update the database credentials if necessary.
4.  **Run the application:**
    - Place the project files in your web server's document root (e.g., `htdocs` for XAMPP).
    - Open your web browser and navigate to `http://localhost/rentnow`.

## Admin Credentials

- **Email:** admin@example.com
- **Password:** Admin
