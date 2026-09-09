# 🌍 Eco-Friendly Travel Planner
### *Redefining the Way You Wander*

[![Database-MongoDB](https://img.shields.io/badge/Database-MongoDB-green?style=for-the-badge&logo=mongodb)](https://www.mongodb.com/)
[![Language-PHP](https://img.shields.io/badge/Language-PHP-777BB4?style=for-the-badge&logo=php)](https://www.php.net/)
[![Docker-Ready](https://img.shields.io/badge/Deploy-Docker-2496ED?style=for-the-badge&logo=docker)](https://www.docker.com/)

## 📖 Overview
The **Eco-Friendly Travel Planner** is a web-based application designed to help modern travelers minimize their environmental footprint. Unlike standard travel apps, this platform integrates **sustainability scoring**, carbon emission tracking, and localized eco-tips to ensure your journey respects both the planet and local communities.

Built as part of the **CMSC 207** curriculum, this project focuses on robust CRUD operations, secure database management, and user-centric design. It runs on **MongoDB Atlas** and ships with a `Dockerfile` for one-click deployment to free hosts like Render.

---

## ✨ Key Features
* **Smart Eco-Scoring:** A dynamic engine that calculates sustainability scores (1–100) and carbon footprints ($CO_2e$) by factoring in transport emission rates, traveler count, and a logarithmic distance penalty.
* **Destination Database:** Browse locations globally with specific "Eco-Notes" for responsible tourism.
* **Overlap Prevention Logic:** Intelligent scheduling that prevents users from booking conflicting itineraries.
* **Secure Authentication:** User registration and session-based login systems.
* **Personal Dashboard:** Manage, update, and cancel your upcoming green adventures.
* **Quick Edit:** Tweak a saved trip's dates, traveler count, priority, or notes from a dashboard modal without leaving the page.
* **Edit Profile:** Update your name, email, password, and profile picture (stored directly in MongoDB — no local file storage).

---

## 🧪 Sustainability Logic
The core of this application is a custom-built scoring engine that evaluates the environmental impact of every trip.

### 1. Carbon Footprint Calculation
The application calculates the total CO₂e (Carbon Dioxide Equivalent) based on the following formula:
$$Estimated Emission = Emission Factor \times Distance (km) \times Traveler Count$$

**Emission Factors used:**
* **Walking/Biking:** 0.00
* **Train:** 0.05 | **Public Bus:** 0.10
* **Ferry/Motorcycle:** 0.12
* **Private Car:** 0.21 | **Airplane:** 0.25

### 2. Sustainability Score (1-100)
The final score is determined by balancing the transport mode, distance, and the destination's eco-rating:
1. **Base Score:** Assigned by transport mode (e.g., Walking = 100, Airplane = 25).
2. **Distance Penalty:** A logarithmic penalty is applied so that longer trips impact the score more heavily:
   $$Penalty = 8 \times \log_{10}(Distance + 1)$$
3. **Eco-Bonus:** Destinations with high eco-ratings (🍃) grant a bonus:
   $$Bonus = Destination Eco Rating \times 3$$

**Final Calculation:**
`Score = Base Score - Distance Penalty + Eco Bonus`
*(Clamped between a minimum of 5 and a maximum of 100)*

---

## 🛠️ Tech Stack
* **Backend:** PHP 8.1+ with the [MongoDB PHP extension](https://pecl.php.net/package/mongodb) and `mongodb/mongodb` (via Composer)
* **Database:** MongoDB Atlas (collections: `users`, `destinations`, `trips`) — profile pictures are stored as binary data on the user document, not on disk
* **Frontend:** Semantic HTML5, CSS3 (Custom Grid Layouts), vanilla JavaScript, Poppins/Inter (Google Fonts)
* **Environment:** PHP's built-in web server or Apache/Nginx for local dev; Docker (PHP 8.3 + Apache) for deployment

---

## 🚀 Installation & Setup

### 1. Prerequisites
* PHP 8.1+ with the `mongodb`, `openssl`, and `fileinfo` extensions enabled.
* [Composer](https://getcomposer.org/) (or use the bundled `composer.phar`).
* A [MongoDB Atlas](https://www.mongodb.com/cloud/atlas) cluster (or a local `mongod`).
* Ensure **Git** is installed on your system.

### 2. Clone the Repository
```bash
git clone https://github.com/ajbalaga/Eco-friendly-Travel-Planner-V2.git
cd Eco-friendly-Travel-Planner-V2
```

### 3. Install PHP Dependencies
```bash
composer install
```

### 4. Configure the Database Connection
1. Copy `.env.example` to `.env`.
2. In Atlas, go to your cluster → **Connect** → **Drivers** → **PHP**, and copy the connection string.
3. Fill in `.env`:
   ```
   MONGODB_URI=mongodb+srv://<username>:<password>@<cluster-host>/?appName=Cluster0
   MONGODB_DB=Eco-friendly-travel-planner
   ```
4. Create indexes (unique email, lookup indexes) and seed the destinations collection:
   ```bash
   php database/create_indexes.php
   php database/seed_destinations.php
   ```
   `database/ecoFriendly_travel_planner_db.sql` is kept as a historical reference to the project's original MySQL schema/seed data — it is no longer used at runtime.

### 5. Access the Application
```bash
php -S localhost:8000
```
Then open **[http://localhost:8000/](http://localhost:8000/)**.

---

## ☁️ Deploying (Render, free tier)

The app ships with a `Dockerfile` (PHP 8.3 + Apache + the `mongodb`/`fileinfo` extensions) so it can run on any Docker-based host. Profile pictures are stored in MongoDB itself (not on local disk), so the app has no persistent-filesystem requirement — a hard requirement for free hosts, which give containers ephemeral storage.

1. Push this repo to GitHub.
2. In [Render](https://render.com), **New → Blueprint**, point it at your repo — it will read `render.yaml` and create the web service automatically. (Or **New → Web Service** manually, choosing "Docker" as the runtime.)
3. When prompted, set the `MONGODB_URI` environment variable to your Atlas connection string (kept as a secret in Render's dashboard, never committed to git). Use a strong database password — once deployed, the app is publicly reachable.
4. Once deployed, run the one-time setup scripts against your Atlas cluster from your machine (they only need `MONGODB_URI` locally, not the live container):
   ```bash
   php database/create_indexes.php
   php database/seed_destinations.php
   ```
5. In Atlas → **Network Access**, allow `0.0.0.0/0` (Render's outbound IPs aren't static on the free tier) or use Atlas's "Allow access from anywhere" option.

**Free tier caveats:** Render's free web services spin down after 15 minutes of inactivity (the first request after that takes ~30-60s to wake up), and there's no persistent disk — which is why this app was migrated to keep all state (including images) in MongoDB rather than local files.

---

## 📂 Project Structure
* **`index.php`** — The main landing page and entry point.
* **`avatar.php`** — Streams a user's profile picture from MongoDB (falls back to the default avatar if none is set).
* **`/auth`** — User authentication logic (`login.php`, `logout.php`, `register.php`).
* **`/pages`** — Core application logic: dashboard, destinations browser, trip planner, trip results, profile editing, and their supporting processing scripts.
* **`/views`** — HTML templates (`*.view.php`) for each page, `require`-d by the matching logic file in `/pages`, `/auth`, or the root — kept separate from the PHP/database logic.
* **`/config`** — `database.php`: loads `.env` and opens the MongoDB connection (`$db`), plus the shared `isValidObjectId()` helper.
* **`/database`** — One-time setup scripts: `create_indexes.php` (unique email + lookup indexes) and `seed_destinations.php` (loads the 35 starter destinations). `ecoFriendly_travel_planner_db.sql` is the original MySQL schema, kept for reference only.
* **`/assets`** — `/css` (styling), `/images` (static media like the default avatar), and `/js` (frontend scripts).
* **`Dockerfile`, `.dockerignore`, `render.yaml`** — Container build and Render Blueprint for deployment.
* **`.env.example`** — Template for the required environment variables (`.env` itself is gitignored).

---

## 🛠️ Troubleshooting
* **Database Connection Error:** Verify `MONGODB_URI` in your `.env` file, and check Atlas **Network Access** allows your current IP.
* **"Class MongoDB\Client not found":** Run `composer install` and confirm the `mongodb` PHP extension is enabled (`php -m | grep mongodb`).
* **"Class finfo not found" on profile picture upload:** Enable the `fileinfo` PHP extension (`extension=fileinfo` in `php.ini`) — it's used to validate uploaded image types.

**Happy Eco-Traveling!** 🌿
