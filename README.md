$readme = @"
# MA-Tools 🛠️

A collection of powerful Command-Line Interface (CLI) tools built entirely in PHP. 
Manage your todo list, launch apps, browse the web, and play games—all directly from your terminal.

## 🚀 Features
- **\`ma-todo\`**: A fast, color-coded Todo List manager.
- **\`ma-open\`**: Launch any app on your PC just by typing its name (e.g., \`chrome\`). Includes typo correction!
- **\`ma-web\`**: An interactive web navigator. Search Google or open ChatGPT, DeepSeek, and YouTube instantly.
- **\`ma-game\`**: Play Blackjack (21) with ASCII art and colors.

## 📋 Prerequisites
You must have **PHP** and **Git** installed on your system.
To check if you have PHP, open PowerShell and run:
\`\`\`bash
php -v
\`\`\`

## 📥 Installation (Windows)

1. **Clone this repository:**
   \`\`\`bash
   git clone https://github.com/YOUR_USERNAME/ma-tools.git
   cd ma-tools
   \`\`\`

2. **Run the installer script:**
   \`\`\`powershell
   .\install.ps1
   \`\`\`

3. **Restart your terminal** (Close PowerShell and open a new window).

## 🛠️ Usage

Once installed, you can use any of the commands from anywhere in your terminal.

### 1. Web Navigator (\`ma-web\`)
\`\`\`bash
ma-web
\`\`\`
*This opens a menu where you can type \`search\` to Google something, or type \`chatgpt\` to open it instantly. You can even add your own websites!*

### 2. App Launcher (\`ma-open\`)
\`\`\`bash
ma-open list
ma-open add spotify "C:\Path\To\Spotify.exe"
\`\`\`
*To launch an app, just type its name. For example: \`spotify\`*

### 3. Todo List (\`ma-todo\`)
\`\`\`bash
ma-todo add "Buy milk"
ma-todo list
\`\`\`

### 4. Blackjack Game (\`ma-game\`)
\`\`\`bash
ma-game
\`\`\`

---
👨‍💻 **Author**
Built with PHP by AM.
"@

Set-Content -Path "README.md" -Value $readme -Encoding UTF8
Write-Host "✅ README.md created perfectly!" -ForegroundColor Green
