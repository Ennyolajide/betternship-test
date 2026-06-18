# Customer Feedback Tracker

A simple customer feedback tracker built with Laravel. Customers submit feedback through an
AJAX form, and submissions appear on a dashboard where each entry can be marked as reviewed —
all without page reloads.

## Features

- Feedback submission form (name, email, feedback) with server-side validation
  (all fields required, valid email, feedback at least 20 characters).
- jQuery AJAX submission: no page refresh, inline validation errors, success message,
  and the submit button is disabled while the request is in flight.
- Dashboard listing every submission with name, email, and status.
- "Mark Reviewed" button that updates a row's status from `Pending` to `Reviewed`
  via AJAX and persists the change.

## Requirements

- PHP 8.3+
- Composer
- SQLite (default; the database file lives at `database/database.sqlite`)
- Node.js & npm (only needed to build front-end assets / run the welcome page; the feedback
  pages load jQuery from a CDN and work without a build step)

## Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create your environment file
cp .env.example .env

# 3. Generate the application key
php artisan key:generate

# 4. Create the SQLite database file
touch database/database.sqlite

# 5. Run migrations and seed sample feedback
php artisan migrate --seed

# 6. (Optional) Install and build front-end assets
npm install
npm run build
```

## Running the app

Using PHP's built-in server:

```bash
php artisan serve
```

Then open http://127.0.0.1:8000 — the home page redirects to the feedback dashboard
at `/feedbacks`.

If you use [Laravel Herd](https://herd.laravel.com/), the app is served automatically at
your project's `.test` domain (for example `https://betternship.test/feedbacks`).

## Routes

| Method | URI                           | Description                       |
| ------ | ----------------------------- | --------------------------------- |
| GET    | `/`                           | Redirects to the dashboard        |
| GET    | `/feedbacks`                  | Dashboard + submission form       |
| POST   | `/feedback`                   | Store new feedback (AJAX)         |
| PATCH  | `/feedback/{feedback}/status` | Mark feedback as reviewed (AJAX)  |

## Testing

```bash
php artisan test
```

The feature tests cover the dashboard, validation, storing feedback, and the
"mark reviewed" flow. They run against an in-memory SQLite database, so they do not
touch your development data.
