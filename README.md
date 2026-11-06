# 🌐 LumiNUs Web Admin System

> **A Centralized Web-Based Administrative Management System for Alumni Engagement, Geolocation Mapping, and Institutional Tracer Studies.**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![Database](https://img.shields.io/badge/Database-Supabase%20PostgreSQL-3ECF8E?logo=supabase&logoColor=white)](https://supabase.com/)
[![Deployment](https://img.shields.io/badge/Deployed_on-Railway-0B0D0E?logo=railway&logoColor=white)](https://railway.app/)

---

## 📌 Project Overview

**LumiNUs** is an integrated mobile and web ecosystem developed as a capstone project for the **National University Lipa – Alumni Affairs Office**.

The **LumiNUs Web Admin System** serves as the administrative command center, empowering university coordinators to manage alumni records, analyze institutional tracer study data, coordinate official events, manage partner merchant perks, and communicate directly with graduates.

> **Note on Public Repository:** This repository contains a sanitized, showcase-ready release of the administrative web portal source code. All production environment configurations, sensitive database credentials, API secrets, and proprietary alumni records have been decoupled.

---

## ✨ Key Features

- 👥 **Alumni Directory & Account Management:** Centralized alumni management supporting secure bulk account generation via CSV importation and automated credential provisioning.
- 📊 **Tracer Study Analytics & Reporting:** Interactive survey builder and data dashboard tracking employment status, industry distribution, and curriculum alignment metrics for institutional quality assurance.
- 🗺️ **Geolocation Mapping Dashboard:** Visual mapping interface displaying alumni geographical dispersion and regional hubs to assist institutional networking initiatives.
- 🎁 **Perks & Privilege Management:** Administrative catalog for establishing, categorizing, and auditing promotional partner merchant benefits and university privileges.
- 📢 **Campus Announcements & Event Coordination:** Real-time content management system for broadcasting official announcements, tracking RSVPs, and coordinating outreach events.
- 🛡️ **REST API Services:** High-performance, token-authenticated RESTful backend powering the companion **LumiNUs Alumni Mobile App**.

---

## 🛠️ Tech Stack & Architecture

- **Backend Framework:** Laravel (PHP 8.2+)
- **Frontend / Templating:** Blade, Tailwind CSS, Alpine.js / JavaScript
- **Database:** PostgreSQL via Supabase (Local testing compatible with SQLite/MySQL)
- **Containerization / Local Environment:** Docker Desktop / Laravel Sail (Ubuntu on WSL2)
- **Production Hosting:** Railway (CI/CD connected)

---

## 🚀 Getting Started

Follow these steps to set up and run the web application locally.

### Prerequisites

- [PHP](https://www.php.net/) 8.2 or higher
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) & npm
- [Git](https://git-scm.com/)
- *Optional:* Docker Desktop (if using Laravel Sail)

