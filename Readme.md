#  Resolvr - PHP Twig Ticket Management App

A lightweight ticket management application built with PHP and Twig templating engine, implementing server-side authentication, dashboard, and CRUD functionality with JSON file storage.

## 🔗 Other Versions

This project is available in multiple frameworks:

- **⚛️ React Version** - [Repository](https://github.com/Truella/resolvr-react) | [Live Demo](https://truella.github.io/Resolvr_React_Version_/)
- **🟢 Vue Version** - [Repository](https://github.com/truella/Resolvr_Vue_Version) | [Live Demo](https://truella.github.io/Resolvr_Vue_Version/#/)
- **🐘 PHP Twig Version** (You are here) - [Live Demo](https://resolvrtwigversion-production.up.railway.app/)


## ⚙️ Setup and Run

### Local Development

```bash
# Clone the repository
git clone https://github.com/truella/Resolvr_Twig_Version.git
cd Resolvr_Twig_Version

# Install PHP dependencies
composer install

# Install Node dependencies (for Tailwind)
npm install

# Build Tailwind CSS
npm run build
# or for development with watch mode:
npm run dev

# Start PHP development server
php -S localhost:8000 -t public

# Or if index.php is in root:
php -S localhost:8000
```

Open in your browser: `http://localhost:8000`

### Production Build

```bash
# Install dependencies without dev packages
composer install --no-dev --optimize-autoloader

# Build minified CSS
npm run build
```

### Deploy to Railway

```bash
# Ensure Tailwind CSS is built
npm run build

# Commit built CSS
git add public/css/output.css
git commit -m "Add built CSS"
git push origin main

# Connect to Railway
# 1. Go to railway.app and sign in with GitHub
# 2. New Project → Deploy from GitHub repo
# 3. Select your repository
# 4. Add start command: php -S 0.0.0.0:$PORT -t public
# 5. Settings → Networking → Generate Domain
```

## 📦 Dependencies

### Composer (composer.json)
```json
{
  "require": {
    "php": ">=8.1",
    "twig/twig": "^3.0"
  }
}
```

### NPM (package.json)
```json
{
  "devDependencies": {
    "tailwindcss": "^3.3.0",
    "autoprefixer": "^10.4.0",
    "postcss": "^8.4.0"
  }
}
```


## 📝 License

MIT License - Free for educational and commercial use

