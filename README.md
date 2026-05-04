# FairScore (Vanilla PHP)

FairScore is a complete, lightweight, native PHP MVC application converted from Laravel.

## Features
- No Framework Overhead: 100% pure vanilla PHP using custom router and PDO.
- Same UI/UX: Fully preserved views from the original design.
- Socket Integration: Reminders sent cleanly via raw HTTP POSTs.
- Google OAuth: Fully integrated using the official Google API Client library.

## Setup
1. Copy `.env.example` to `.env` and fill in database credentials.
2. Run `composer install` to download dependencies (Google API client).
3. Start the PHP server: `php -S 0.0.0.0:8000 -t public`
4. Make sure your MySQL database is running and import the schema if necessary.

# Portal Links
🛡️ Admin Portal → http://localhost:8000/admin/login
⚖️ Judge Portal → http://localhost:8000/judge/login

http://fairscore.local/admin/login
http://fairscore.local/judge/login

# connect to router example
http://[IP_ADDRESS]/judge/login

# jethrodavegarcera99@gmail.com
J3THR0_D4VE
