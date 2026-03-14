## Project structure

- app/ - contains the main application code, including controllers, models, views, and services
  - controllers/ - contains controller classes that handle HTTP requests and responses
  - models/ - contains model classes that represent the data and business logic of the application
  - views/ - contains view templates for rendering HTML responses
  - services/ - contains service classes that provide reusable functionality across the application
- config/ - contains configuration files, including database configuration and localization files
- lib/ - contains shared libraries and utilities used by the application
  - active_model/ - contains the ActiveModel implementation for data validation, model-relationships management and error handling
  - helpers.php - contains helper functions for views and other parts of the application
  - logger.php - contains a simple logging utility for debugging and error tracking
- pregenerated/ - contains pre-generated code via Static Page Generator for performance optimization
- public/ - contains publicly accessible files, including the main index.php and assets (CSS, JavaScript, images)
  - assets/ - contains static assets such as stylesheets, JavaScript files, and images
    - stylesheets/ - contains CSS files for styling the application
    - javascripts/ - contains JavaScript files for client-side interactivity
    - images/ - contains image files used in the application
- tests/ - contains unit and integration tests for the application
- uploads/ - contains uploaded files, such as images or documents, organized by type and date
- vendor/ - contains third-party dependencies managed by Composer

## General Requirements

- Use modern technologies as described below for all code suggestions. Prioritize clean, maintainable code with appropriate comments.
- use the existing codebase as a reference for style and structure, but feel free to suggest improvements and refactorings where appropriate.
- Ensure that all generated code is compatible with the existing codebase and follows the same conventions and patterns.

## PHP requirements

- Target Version: PHP 8.4 or higher
- use t() function for all user-facing strings to ensure proper localization.
- For any new features or significant changes, also update the relevant localization files (e.g., `config/locales/cs.yml`) with appropriate translations.
- Features to use:
  - Named arguments
  - Constructor property promotion
  - Union types and nullable types
  - Match expressions
  - Nullsafe operator (`?->`)
  - Attributes instead of annotations
  - Typed properties with appropriate type declarations
  - Return type declarations
  - Enumerations (`enum`)
  - Readonly properties
  - Emphasize strict property typing in all generated code.
- Coding Standards:
  - Follow PSR-12 coding standards
  - Use strict typing with `declare(strict_types=1);`
  - Prefer composition over inheritance
  - Use dependency injection
- Static Analysis:
  - Include PHPDoc blocks compatible with PHPStan for static analysis
- Error Handling:
  - Use exceptions consistently for error handling and avoid suppressing errors.
  - Provide meaningful, clear exception messages and proper exception types.
- Tests:
  - Generate unit tests using PHPUnit for all new code, ensuring good coverage and testing edge cases.
  - For edited code, update existing tests to reflect changes and ensure they still pass.

## HTML requirements

- Use HTML5 semantic elements (`<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`, `<search>`, etc.)
- Include appropriate ARIA attributes for accessibility
- Ensure valid markup that passes W3C validation
- Use responsive design practices
- Generate `srcset` and `sizes` attributes for responsive images when relevant
- Prioritize SEO-friendly elements (`<title>`, `<meta description>`, Open Graph tags)

## CSS requirements

- never edit styles.css directly, apply all edits into overrides.css instead
- Use modern CSS features including:
  - CSS Grid and Flexbox for layouts
  - CSS Custom Properties (variables)
  - CSS animations and transitions
  - Media queries for responsive design
  - Logical properties (`margin-inline`, `padding-block`, etc.)
  - Modern selectors (`:is()`, `:where()`, `:has()`)
- Follow BEM or similar methodology for class naming
- Use CSS nesting where appropriate
- Use modern units (`rem`, `vh`, `vw`) instead of traditional pixels (`px`) for better responsiveness

## Javascript requirements

- Place JavaScript code in separate files under `public/assets/javascripts/custom` and link them appropriately in the HTML templates.
- ECMAScript 2024 (ES15) or higher
- Features to use:
  - Arrow functions
  - Template literals
  - Destructuring assignment
  - Spread/rest operators
  - Async/await for asynchronous code
  - Classes with proper inheritance when OOP is needed
  - Object shorthand notation
  - Optional chaining (`?.`)
  - Nullish coalescing (`??`)
  - Dynamic imports
  - BigInt for large integers
  - `Promise.allSettled()`
  - `String.prototype.matchAll()`
  - `globalThis` object
  - Private class fields and methods
  - Export \* as namespace syntax
  - Array methods (`map`, `filter`, `reduce`, `flatMap`, etc.)
- Avoid:
  - `var` keyword (use `const` and `let`)
  - jQuery or any external libraries
  - Callback-based asynchronous patterns when promises can be used
  - Internet Explorer compatibility
  - Legacy module formats (use ES modules)
  - Limit use of `eval()` due to security risks
- Error Handling:
  - Use `try-catch` blocks consistently for asynchronous and API calls, and handle promise rejections explicitly.
- Differentiate among:
- **Network errors** (e.g., timeouts, server errors, rate-limiting)
- **Functional/business logic errors** (logical missteps, invalid user input, validation failures)
- **Runtime exceptions** (unexpected errors such as null references)
- Provide **user-friendly** error messages (e.g., “Something went wrong. Please try again shortly.”) and log more technical details to dev/ops (e.g., via a logging service).
- Consider a central error handler function or global event (e.g., `window.addEventListener('unhandledrejection')`) to consolidate reporting.
- Carefully handle and validate JSON responses, incorrect HTTP status codes, etc.

## Database Requirements (MySQL)

- Leverage MySQL 8.0 features such as Common Table Expressions (CTEs), window functions, and JSON data types where appropriate.
- Use prepared statements and parameterized queries to prevent SQL injection.

## Security Considerations

- Sanitize all user inputs thoroughly.
- Parameterize database queries.
- Enforce strong Content Security Policies (CSP).
- Use CSRF protection where applicable.
- Ensure secure cookies (`HttpOnly`, `Secure`, `SameSite=Strict`).
- Limit privileges and enforce role-based access control.
- Implement detailed internal logging and monitoring.
