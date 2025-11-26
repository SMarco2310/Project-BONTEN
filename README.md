# Project-BONTEN


/project-bonten
│
├── config/
│   └── Database.php      <-- Database Connection Class
│
├── src/
│   ├── Controllers/      <-- Handles logic (Auth, Events)
│   └── Models/           <-- Handles Database Queries (User, Event)
│
├── public/               <-- Public access point
│   ├── index.php         <-- Entry point (Router)
│   ├── css/              <-- Move your CSS here
│   ├── js/               <-- Move your JS here
│   └── assets/           <-- Move your images here
│
├── views/                <-- Move your HTML files here (rename to .php)
│   ├── login.php
│   └── dashboard.php
│
└── .htaccess             <-- For URL routing