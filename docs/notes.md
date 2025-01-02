# Notes

This technical test includes several areas that match the supplied specs:
- **Security:** Middleware and basic API Key authentication
- **Security:** Basic API Key validation
- **Validation:** Laravel's built-in validation rules
- **API Resources:** Custom resources for API responses, to separate the data from the logic
- **Eloquent:** Model relationships and custom methods
- **Database:** Seeding and querying with raw SQL
- **API Routes:** API routes and resource controllers
- **Code Organization**: The codebase is well-organized with a clear separation of concerns, making it easy to navigate and maintain.
- **Use of Traits**: Traits like `HasCoordinates` and `HasUuid` are used effectively to encapsulate reusable logic.
- **Testing**: The codebase includes both unit and feature tests, ensuring code reliability and facilitating future changes.
- **Database Migrations**: Migrations are used to manage database schema changes, providing a clear history of modifications.
- **Environment Configuration**: The use of `.env` files and `phpunit.xml` for environment-specific configurations is well-implemented.
- **Code Quality:** The command to import the postcode data is self contained as it is the only place where CSV files are processed. For future development work, I would consider moving this to a service class.
- **Coordinates**: I was intending to use the `geography` type in the database to store the coordinates, but it was causing issues with the tests. I have commented out the relevant code in the migrations and models. As the distance calculations were already working using the latitude and longitude, I have left them in place.