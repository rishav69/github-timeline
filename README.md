# GitHub Timeline Email Notifier

This project fetches the latest public activity from the GitHub timeline and automatically sends updates via email to all subscribed users every 5 minutes using a CRON job.

## 📌 Features

- ✅ User email verification before subscription
- ✅ Sends formatted GitHub timeline events to subscribers
- ✅ Includes unsubscribe link in each email
- ✅ Stores emails and one-time codes securely
- ✅ Fully automated with CRON (Linux/macOS or manual for Windows)

---

## 🚀 How It Works

1. **User enters their email** on the subscription page.
2. **Verification code is emailed** to confirm subscription.
3. Once verified, the email is saved.
4. Every 5 minutes, a **CRON job fetches GitHub events** and emails all subscribers.
5. Each email includes an **unsubscribe link**.

---

## 📁 Project Structure

```plaintext
src/
├── index.php              # Subscription page
├── verify.php             # Verifies email and code
├── unsubscribe.php        # Handles unsubscribe requests
├── functions.php          # Core logic (mailing, API fetch, formatting)
├── cron.php               # Script run by CRON
├── setup_cron.sh          # (Optional) Shell script to set up CRON on Linux
├── registered_emails.txt  # (Gitignored) Stores verified emails
├── codes/                 # (Gitignored) Stores temp verification codes
├── cron_debug.txt         # (Gitignored) Logs CRON output for debugging
🛠️ Requirements
PHP 8.x with curl and mail enabled

Apache/XAMPP or any PHP server

Internet connection (to call GitHub API)

⏱️ CRON Setup (Linux/macOS)
Use the provided script to auto-setup CRON:

bash
Copy code
chmod +x setup_cron.sh
./setup_cron.sh
It schedules cron.php to run every 5 minutes.

📧 Sample Email Screenshot
Includes latest GitHub events and an unsubscribe link.

🔒 .gitignore Highlights
gitignore
Copy code
# Email data
registered_emails.txt
codes/
cron_debug.txt
/tmp/gh-timeline.log

# OS/Editor files
.DS_Store
Thumbs.db
.vscode/
.idea/
.env
📤 Deployment
Clone repo:

bash
Copy code
git clone https://github.com/your-username/github-timeline.git
cd github-timeline
Host src/ in your local Apache server (e.g., XAMPP).

Open http://localhost/github-timeline/src in browser.

📜 License
This project is for educational/demo purposes.

✍️ Author
Rishav Raj
📧 rishav6973@gmail.com
🔗 github.com/rishav69