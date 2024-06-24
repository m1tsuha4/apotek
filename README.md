# Monev FTI Application Setup Guide

This guide will walk you through the steps to set up the Monev FTI application.

## Prerequisites
- PHP version "^8.2"
- Laravel version "^11.0"

## Installation Steps

1. **Clone the Project**:
   ```
   git clone <repository_url>
   ```

2. **Create Environment File**:
   Create a `.env` file in the root directory of the project and configure your database settings.

3. **Database Setup**:
   Set up your database configuration in the `.env` file.

4. **Update Dependencies**:
   Run the following command in your terminal:
   ```
   composer update
   ```

5. **Create Symbolic Link for Storage**:
   Run the following command:
   ```
   php artisan storage:link
   ```

6. **Run Migrations**:
   Execute migrations to create necessary tables:
   ```
   php artisan migrate
   ```
7. **Seed Database**:
   Seed the database with Prodi data:
   ```
   php artisan db:seed 
   ```

8. **Run the Application**:
    Start the Laravel development server:
    ```
    php artisan serve
    ```

Now you should be able to access the Apotek API application by navigating to the URL provided by the `php artisan serve` command.

If you encounter any issues during the setup process, feel free to reach out for assistance.
