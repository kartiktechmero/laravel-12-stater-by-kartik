# Laravel Backend 

A Laravel-based backend project

---

## 🚀 Getting Started

Follow these steps to set up the project locally.

### 1. Clone the Repository

```bash
git clone https://github.com/kartiktechmero/
cd 
```

### 2. Install Dependencies

```bash
composer install
```

If you’re also using Node.js for frontend/build tools:

```bash
npm install
```

---

## ⚙️ Environment Setup

### 3. Create `.env` File

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

### 4. Generate App Key

```bash
php artisan key:generate
php artisan storage:link
```

### 5. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Make sure the database exists:

```bash
mysql -u your_username -p -e "CREATE DATABASE your_database_name;"
```

---

## 🗄️ Run Migrations & Seeders

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Seed Database (fresh reset with seeders)

```bash
php artisan migrate:fresh --seed
```

### 8. Passport Install for Keys

```bash
php artisan passport:install
php artisan passport:client --personal // For live init cred

```

---

## 🛠 Developer Tools

### IDE Helper & Model Generator

```bash
php artisan ide-helper:generate
php artisan ide-helper:models -W
```

### Generate ERD Diagram

```bash
php artisan generate:erd
```

---

## ▶️ Run the Project

### 9. Start the Laravel Development Server

```bash
php artisan serve
```

Your app will be available at:

```
http://127.0.0.1:8000
```

Or if you’re using **Laravel Valet**:

```
http://ai-speak-english.test
```

---

## 🌍 Sharing Laravel Valet Project with ngrok

Useful for testing APIs/webhooks (Twilio, Stripe, etc.).

### 1. Install ngrok on macOS

```bash
brew install ngrok/ngrok/ngrok
```

Or download from [ngrok.com](https://ngrok.com/download).

### 2. Authenticate ngrok

```bash
ngrok config add-authtoken YOUR_AUTHTOKEN
```

### 3. Set ngrok as the Valet share tool

```bash
valet share-tool ngrok
```

### 4. Start your Valet site

```
http://project-name.test
```

### 5. Share the site via ngrok

```bash
cd ~/Sites/project-name
valet share
```

Output example:

```
Forwarding    https://abcd-1234-5678.ngrok-free.app -> http://project-name.test
```

### 6. Stop sharing

Press **CTRL + C** to stop the tunnel.

---

## ✅ Contribution Workflow (Important)

To maintain code quality and prevent conflicts, follow this workflow:

1. **Always update your branch before starting work**

   ```bash
   git checkout main
   git pull --rebase
   ```

2. **Never commit directly to `main`**

    * Create a new branch from `main` for each feature/bugfix:

   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Check code formatting before commit (Laravel Pint)**

   ```bash
   ./vendor/bin/pint
   ```

4. **After creating models, regenerate IDE helpers**

   ```bash
   php artisan ide-helper:generate
   php artisan ide-helper:models -W
   ```

5. **If needed, regenerate ERD diagram**

   ```bash
   php artisan generate:erd
   ```

6. **Push to your branch & create a Pull Request (PR)**

   ```bash
   git push origin feature/your-feature-name
   ```
7. **Before Push to git always do**
    ```bash
    pint
    composer analyse
    ```
---

## 🧹 Quick Commands Reference

| Task                   | Command                            |
|------------------------|------------------------------------|
| Install dependencies   | `composer install && npm install`  |
| Run migrations         | `php artisan migrate`              |
| Fresh DB + seed        | `php artisan migrate:fresh --seed` |
| Generate Passport keys | `php artisan passport:install`     |
| Format PHP code (Pint) | `./vendor/bin/pint`                |
| Generate IDE helper    | `php artisan ide-helper:generate`  |
| Generate model helpers | `php artisan ide-helper:models -W` |
| Generate ERD           | `php artisan generate:erd`         |
| Run dev server         | `php artisan serve`                |
| Share via ngrok        | `valet share`                      |

---

## Saloon PHP
### Create new saloon connector
```bash 
php artisan saloon:connector
```

### Create new saloon Request
```bash 
php artisan saloon:request
```


### Set Cron:

At 12:00

  ```bash 
  crontab -e
  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  * * * * * cd /home/replydm/webapps/replydm && php artisan schedule:run >> /dev/null 2>&1crontab -l

  ```

- see cron crontab -l

---
