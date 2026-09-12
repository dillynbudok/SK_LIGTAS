SK LIGTAS - FINAL CLIENT PACKAGE

Contents
- PHP web system
- Admin dashboard
- Assets and uploaded images/logos
- Database schema/data: database.sql
- Database compatibility migration: database_update.sql

LOCAL TESTING
1. Create a MySQL/MariaDB database named sk_ligtas.
2. Import database.sql.
3. If updating an older SK LIGTAS database, run database_update.sql.
4. Set database credentials in api/config.php.
5. Run the project through a PHP server/XAMPP.

HOSTING
1. Create a MySQL/MariaDB database on the hosting provider.
2. Import database.sql.
3. Run database_update.sql only when upgrading an existing/older database.
4. Update api/config.php with the hosting database host, database name, username and password.
5. Upload the project files to the hosting web root.
6. Connect the domain after hosting is working.

SMS
SMS configuration is included but is not configured in this package.
Do not place a real Semaphore API key in a public Git repository.
The placeholder configuration is in api/semaphore_config.php.

IMPORTANT
- The .git folder is intentionally NOT included in this client ZIP.
- Test/debug/backup files from the development workspace were removed.
- Keep database.sql as a backup of the database structure/data.
