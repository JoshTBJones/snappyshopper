# Notes

This technical test includes several areas that match the supplied specs:
- **Security:** Middleware and basic API Key authentication
- **Security:** Basic API Key validation
- **Security:** Using UUIDs for unique identifiers for multiple security purposes (timing / enumeration attacks, obscuring predictable identifiers, etc.)
- **Security:** Laravel's built-in validation rules
- **Maintainability:** Custom resources for API responses, to separate the data from the logic
- **Maintainability:** The codebase is well-organized with a clear separation of concerns, making it easy to navigate and maintain.
- **Maintainability:** Use of Traits like `HasCoordinates` and `HasUuid` are used effectively to encapsulate reusable logic.
- **Testing**: The codebase includes both unit and feature tests, ensuring code reliability and facilitating future changes.
- **Database**: Migrations, Seeders and Factories are used to manage database schema changes, providing a clear history of modifications.
- **Documentation:** The functions are all documented and additional documentation files are provided.
- **Simplicity:** The codebase makes use of standard Laravel practices and patterns. Future development will be easier as a result.

## Improvements
- **Logging**: for exceptions and other events stored both locally in a lof file and externally using a logging service such as BugSnag
- **Audit Trail**: more detailed logging of events and actions taken on the system
- **Service Classes**: for API responses, imports, etc.
- **API Key Authentication**: using Laravel Sanctum for API key authentication making use of scopes to limit access to the API
- **API Rate Limiting**: using Rate Limiter to limit the number of requests to the API
- **API Versioning**: using Route Versioning to version the API for future development
- **Coordinates**: I was intending to use the `geography` type in the database to store the coordinates, but it was causing issues with the tests. I have commented out the relevant code in the migrations and models. As the distance calculations were already working using the latitude and longitude, I have left them in place.
- **Testing**: I have commented out the tests for the postcode import functionality as they were taking too long to run and test.
- **Code Quality:** The command to import the postcode data is self contained as it is the only place where CSV files are processed. For future development work, I would consider moving this to a service class.