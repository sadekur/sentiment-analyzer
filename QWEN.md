# Sentiment Analyzer WordPress Plugin

## Project Overview

The Sentiment Analyzer is a WordPress plugin that provides sentiment analysis capabilities for WordPress posts. It allows users to analyze the sentiment of their content (positive, negative, or neutral) based on customizable keyword lists. The plugin includes both admin and front-end functionality with a React-based admin interface.

### Key Features:
- Sentiment analysis of WordPress posts based on keyword matching
- Customizable positive, negative, and neutral keyword lists
- Post filtering by sentiment via shortcode
- Bulk analysis of all posts
- Caching for performance
- REST API endpoints for sentiment operations
- React-based admin settings interface

### Architecture:
- **Backend**: PHP with WordPress REST API integration
- **Frontend**: React.js with Tailwind CSS
- **Build System**: Webpack with Babel, PostCSS, and Tailwind CSS
- **Database**: WordPress post meta storage for sentiment data

## File Structure

```
sentiment-analyzer/
├── app/                    # PHP application code
│   ├── Abstracts/          # Abstract classes
│   ├── API/               # API classes
│   ├── Controllers/       # Controller classes (Admin, Common, Front)
│   ├── Helpers/           # Helper functions
│   ├── Interfaces/        # Interfaces
│   ├── Models/            # Data models
│   └── Traits/            # PHP traits
├── assets/                # Static assets
├── build/                 # Built JavaScript files
├── spa/                   # React source files
│   ├── admin/            # Admin React components
│   └── public/           # Front-end React components
├── node_modules/          # Node.js dependencies
├── vendor/                # PHP dependencies (Composer)
├── composer.json          # PHP dependencies
├── package.json           # JavaScript dependencies
├── webpack.config.js      # Webpack configuration
├── tailwind.config.js     # Tailwind CSS configuration
└── sentiment-analyzer.php # Main plugin file
```

## Building and Running

### Prerequisites
- WordPress installation (requires at least version 6.1)
- PHP 7.4 or higher
- Node.js and npm for building React components

### Setup Instructions
1. Place the plugin folder in your WordPress `wp-content/plugins/` directory
2. Install PHP dependencies using Composer (if any):
   ```bash
   composer install
   ```
3. Install JavaScript dependencies:
   ```bash
   npm install
   ```
4. Build the React components:
   ```bash
   npm run build
   ```
5. Activate the plugin in WordPress admin

### Development Commands
- `npm run build`: Build all assets for production
- `npm run start`: Start development server with hot reloading
- `npm run watch`: Watch files and rebuild on changes
- `npm run build:admin`: Build only admin bundle
- `npm run build:public`: Build only public bundle
- `npm run build:css`: Build only CSS assets

### Key PHP Classes and Functions
- `Sentiment_Analyzer`: Main plugin class
- `Sentiment\Controllers\Common\Assets`: Asset management
- `Sentiment\Controllers\Common\API`: REST API routes and logic
- `Sentiment\Controllers\Admin\Menu`: Admin menu registration
- `Sentiment\Controllers\Front\Shortcode`: Shortcode functionality
- `Sentiment\API\Sentiment_Data`: Core sentiment analysis logic
- Helper functions in `app/Helpers/functions.php`

### API Endpoints
The plugin exposes several REST API endpoints under `sentiment-analyzer/v1`:
- `GET /settings` - Retrieve plugin settings
- `POST /settings` - Update plugin settings
- `POST /analyze/{id}` - Analyze a single post
- `POST /analyze/bulk` - Bulk analyze all posts
- `GET /sentiment/{id}` - Get sentiment for a specific post
- `POST /cache/clear` - Clear sentiment cache
- `GET /posts/{sentiment}` - Get posts filtered by sentiment

### Frontend Components
- Shortcode `[sentiment-filter sentiment="positive|negative|neutral" posts_per_page="10"]` for displaying posts by sentiment
- Admin interface built with React for managing settings
- Responsive design using Tailwind CSS

## Development Conventions

### PHP Coding Standards
- PSR-4 autoloading standards
- WordPress coding standards
- Proper escaping and sanitization for security
- Internationalization (i18n) ready with `__()` functions

### JavaScript/React Conventions
- Modern React with hooks
- Tailwind CSS for styling
- ES6+ syntax with Babel transpilation
- Component-based architecture

### Security Considerations
- Input validation and sanitization using WordPress functions
- Nonce verification where appropriate
- Capability checks for admin functions
- Sanitization of output with `esc_*` functions

### Database Storage
- Plugin settings stored in WordPress options table
- Post sentiment data stored as post meta
- Transient API used for caching results