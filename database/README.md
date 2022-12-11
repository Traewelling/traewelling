# database folder

This folder holds a number of files that are related to the database and the data structure behind Träwelling.

To create a new migration, please think of a speaking name and generate a migration class like this:
```cmd
php artisan make:migration "add train identifier field to statuses table"
```

If you need to change something about the staging database (which you probably don't!), you can change the `fly.toml` file.
Please mention [@jeyemwey](https://github.com/jeyemwey) in the pull request in that case, or contact him beforehand.
The staging database is not deployed automatically, and needs manual pushing.