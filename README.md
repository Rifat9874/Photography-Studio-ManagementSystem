# 📸 Photography Studio Management System


<p align="center">

<img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white">

<img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white">

<img src="https://img.shields.io/badge/Architecture-MVC%20Pattern-success?style=for-the-badge">

<img src="https://img.shields.io/badge/Project-Full%20Stack%20Web%20Application-orange?style=for-the-badge">

</p>


<h2 align="center">
📸 A Complete Digital Management Solution for Modern Photography Studios
</h2>


<p align="center">

A full-stack web application designed to manage photography studio operations including customer management, booking workflows, photographer scheduling, service packages, portfolio management, and business administration.

</p>


---

# 📌 Project Overview


The **Photography Studio Management System** is a full-stack web application developed to digitize and simplify the daily operations of professional photography studios.


Many photography businesses still rely on manual processes for customer records, booking management, photographer coordination, and service tracking.

This system provides a centralized platform that helps studios:

- Organize customer information
- Manage photography bookings
- Coordinate photographers
- Maintain service packages
- Track studio activities
- Improve operational efficiency


The project demonstrates practical software engineering concepts including backend development, database design, authentication, authorization, and real-world business workflow implementation.


---

# 🎯 Project Objectives


The main objectives of this project are:


✔ Automate studio management activities  
✔ Reduce manual booking processes  
✔ Improve customer experience  
✔ Organize photographer schedules  
✔ Maintain structured business records  
✔ Provide efficient administrative control  


---

# ✨ Key Features


## 🔐 Authentication & Role-Based Access Control


The system provides secure authentication with different permissions based on user roles.


### Supported User Roles


| Role | Responsibilities |
|---|---|
| 👑 Admin | Complete system management and control |
| 🏢 Manager | Booking and operational management |
| 📷 Photographer | Schedule and portfolio management |
| 👤 Customer | Booking, reviews and complaint management |



---

# 📅 Booking Management Module


A complete booking workflow for photography services.


### Customer Features

- Browse photography packages
- Submit booking requests
- View booking status
- Manage booking history


### Management Features

- Review booking requests
- Approve or reject bookings
- Assign photographers
- Track service progress



---

# 📷 Photographer Management


The system helps manage photographer activities efficiently.


Features:

- Photographer profiles
- Availability management
- Assigned booking tracking
- Portfolio showcase
- Work information management



---

# 📦 Package Management


Studio managers can organize photography services.


Features:

- Create service packages
- Update package details
- Manage pricing information
- Display available services



---

# 🖼 Portfolio Management


A dedicated portfolio system for showcasing photography works.


Features:

- Upload photography projects
- Organize creative works
- Display photographer achievements



---

# ⭐ Customer Relationship Management


Improve customer interaction through:


- Customer reviews
- Complaint submission
- Feedback monitoring
- Service improvement tracking



---

# 📊 Admin Dashboard


Centralized management dashboard including:


- User management
- Booking overview
- Package management
- Complaint monitoring
- Reports
- Data management



---

# 🏗 System Architecture


The application follows an organized MVC-inspired structure.


```
                User Interface

                      |

                    Views

                      |

                Controllers

                      |

                   Models

                      |

              MySQL Database

```


## Project Structure


```
Photography-Studio-ManagementSystem

│
├── controller
│     Business logic handlers
│
├── model
│     Database models and entities
│
├── view
│     User interface components
│
├── includes
│     Shared components
│
├── database
│     SQL database files
│
└── db.php
      Database configuration

```


---

# 🛠 Technology Stack


| Category | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP 8+ |
| Database | MySQL |
| Architecture | MVC Pattern |
| Server | Apache |
| Hosting | InfinityFree |


---

# 🗄 Database Design


The system uses a relational database structure to maintain organized business data.


## Main Entities


```
Users

Customers

Photographers

Bookings

Packages

Categories

Reviews

Complaints

Reports

Availability

```


Database focuses on:

- Data consistency
- Structured relationships
- Efficient information management
- Scalable design


---

# ⚙️ Installation Guide


## 1. Clone Repository


```bash
git clone https://github.com/Rifat9874/Photography-Studio-ManagementSystem.git
```


---

## 2. Database Setup


Create a MySQL database:


```
photography_studio
```


Import SQL file:


```
database/photography_studio.sql
```


---

## 3. Configure Database Connection


Update:


```
db.php
```


Example:


```php
$db_host = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "photography_studio";
```


---

## 4. Run Application


Using XAMPP:


```
htdocs/Photography-Studio-ManagementSystem
```


Open:


```
http://localhost/Photography-Studio-ManagementSystem
```


---

# 🔒 Security Implementation


Implemented:


✔ Session-based authentication  
✔ Role-based authorization  
✔ Input validation  
✔ Protected database connection  
✔ User access control  


Future improvements:


- Password hashing enhancement
- CSRF protection
- Two-factor authentication
- API security layer


---

# 📸 Application Screenshots


Screenshots will be added soon.


---

# 🚀 Future Enhancements


Planned improvements:


- 💳 Online payment integration
- 📧 Email booking notifications
- ☁ Cloud image storage
- 📱 Mobile application
- 📈 Advanced analytics dashboard
- 🤖 AI-based photographer recommendation


---

# 🌐 Live Demo


🔗 Website:

https://photostudio.kesug.com



---

# 👨‍💻 Developer


## Rifat9874


Computer Science & Engineering Student


GitHub:

https://github.com/Rifat9874



---

# ⭐ Project Highlights


This project demonstrates:


✔ Full-stack web development  
✔ PHP backend programming  
✔ MySQL database design  
✔ MVC architecture  
✔ Authentication systems  
✔ Role-based access control  
✔ Real-world business workflow development  


---

<p align="center">

⭐ If you find this project useful, consider giving it a star.

</p>
