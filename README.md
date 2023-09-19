# Listing App

The project presents an application that allows users to add, browse, edit, and delete listings with an advanced administration system.

## Description

### User
- **Registration** - capability to create a new account
- **E-mail address verification** - after account creation, users are required to verify their e-mail address. The app's functionality is limited until email verification is completed.
- **Login and logout** - authentication for users
- **Adding listings** - a user verified by email and who has provided their phone number in their profile can add a new listing, which initially is in the "unverified" state. Administrators are notified via email about new listings awaiting verification.
- **Viewing, editing, deleting listings** - users can view listings (each view increases the counter by n+1), edit their own listings, and delete them
- **User profile** - contains first name, last name, phone number, and city
- **Viewing, editing, deleting user profiles** - users can view other profiles, edit their own, and permanently delete them along with all associated listings
- **Changing email address and password** - users can change their email (re-verification is required upon changing) and password
- **Password reset** - users can reset their password


### Administrator
- **Category management** - adding, browsing, editing, and deleting listing categories
- **Viewing unverified listings** - a list of listings waiting for administrator verification
- **Editing/deleting user listings** - administrators can edit user listings
- **Listing verification** - upon verification by the administrator, a listing changes its state from "unverified" to "verified" and becomes visible to all users
- **Adding/editing listings without verification** - administrators can add listings bypassing the verification process
- **Deleting listings** - capability to delete any listing
- **Deleting users** - ability to delete a user's account along with their listings
- **Promoting users** - ability to promote users to Administrator status
- **Degrading users** - ability to degrade users
- **Banning users** - restrict certain functionalities for a user
- **Unbanning users** - restore user access

## Technologies used in the project:
- PHP 8.2
- Symfony
- Doctrine
- Mysql
- Composer
- Twig
- Tailwind
- Phpunit
- XDebug
- Docker

## Installation

1. Clone the repository
```bash
git clone https://github.com/jakubgawor/listing-app.git
```

2. Change directory to the project
```bash
cd listing-app
```

3. Install dependencies using Composer
```bash
composer install
```

4. Rename the environment configuration file
```bash
mv .env.example .env
```


5. Manually configure the necessary environment variables in .env
```bash
DATABASE_URL
MESSENGER_TRANSPORT_DSN
MAILER_DSN
BASE_URL
```

6. Create the database
```bash
php bin/console doctrine:database:create
```

7. Migrate the database schema:
```bash
php bin/console doctrine:migrations:migrate
```


## Tests coverage
![image](https://github.com/jakubgawor/listing-app/assets/126957667/d15cf618-f839-4fb1-a203-a3d5c5eda21a)

