# GitHub Timeline Emailer

A PHP project that fetches public GitHub activity every 5 minutes and emails it to registered users.

## Features

- Email verification with one-time code
- Subscribe & unsubscribe system
- CRON job to fetch GitHub activity
- Sends formatted timeline updates via email

## Setup Instructions

1. Clone the repo
2. Configure PHP mail on your local or server
3. Run `src/setup_cron.sh` (Linux/macOS) to activate cron
4. Visit `index.php` to subscribe
5. Check your inbox for updates!

## Notes

- Make sure `curl` is enabled in `php.ini`
- Emails are logged using `mail()` – use a real SMTP setup in production
- Unsubscribing removes your email from the list

## License

MIT
