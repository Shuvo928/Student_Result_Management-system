🎓 Student Result Management System
📌 Project Overview

The Student Result Management System is a web-based application developed using PHP, MySQL, HTML, CSS, and JavaScript.
It is designed to manage and display student academic results efficiently.
Admins can insert, update, and manage student results, while students can view their results securely.

This project is suitable for academic purposes, software engineering courses, and PHP-based web application demonstrations.

🛠️ Technologies Used

Frontend: HTML, CSS, JavaScript

Backend: PHP

Database: MySQL

Server: XAMPP (Apache & MySQL)

Browser: Chrome / Edge / Firefox

📁 Project Structure
result-management-system/
│
├── admin/              # Admin panel files
├── student/            # Student result view files
├── includes/           # Database connection and common files
├── css/                # Stylesheets
├── js/                 # JavaScript files
├── database/           # SQL database file
├── index.php           # Main entry file
└── README.md           # Project documentation

⚙️ How to Run the Project (Step-by-Step)
✅ Step 1: Install XAMPP

Download and install XAMPP from:
https://www.apachefriends.org

Start:

✅ Apache

✅ MySQL

✅ Step 2: Extract Project Files

Extract result_management.zip

Copy the extracted folder

Paste it into:

C:\xampp\htdocs\
Example:
C:\xampp\htdocs\result-management-system

✅ Step 3: Create Database
Open browser
Go to:
http://localhost/phpmyadmin

Click New

Create database:

result_management

✅ Step 4: Import Database

Select the result_management database

Click Import

Choose the .sql file from the project’s database folder

Click Go

✅ Step 5: Configure Database Connection

Open:

includes/db.php


(or config.php if present)

Set:

$host = "localhost";
$user = "root";
$password = "";
$dbname = "result_management";

✅ Step 6: Run the Project

Open browser and visit:

http://localhost/result-management-system/


🎉 Project Output will be displayed

👨‍💼 Admin Login

(Admin can manage student data and results)

Default credentials (if provided):

Username: admin
Password: admin


(You can change this in the database)

👨‍🎓 Student Features

View results using student ID

Secure access

Clean and user-friendly interface

🔐 Admin Features

Add student information

Insert/update results

Manage subjects and marks

Secure admin panel

🎯 Project Purpose

Academic submission

PHP & Database learning

Software Engineering mini project

Web application demonstration

📌 Notes

Runs on local server

Compatible with Windows

👤 Developer

Name: Shuvo Chakrobortty
Course: Software Engineering 
Institution: IUBAT
Project Type: Academic Project
