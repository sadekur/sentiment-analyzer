# Content Mood Analyzer - Agent Guidelines

## Overview
This is a WordPress plugin that analyzes the mood/sentiment of post content (positive, negative, neutral). It consists of PHP backend code and React frontend components built with Tailwind CSS.

## Development Environment Setup

### Prerequisites
- Node.js 16+ and npm
- PHP 7.4+
- WordPress 6.1+
- Composer

### Installation
```bash
npm install
composer install
```

## Build Commands

### Full Build
```bash
npm run build
```
Builds all assets (admin panel, public components, and CSS) using webpack.

### Individual Builds
```bash
npm run build:admin    # Build admin panel React components
npm run build:public   # Build public-facing React components
npm run build:css      # Build Tailwind CSS
```

### Development
```bash
npm start              # Start webpack dev server
npm run watch          # Watch mode for continuous building
```

## Testing Commands

Currently, there are no automated tests configured. When adding tests:

### PHP Tests (when implemented)
```bash
composer test          # Run PHP tests (PHPUnit)
composer test:single   # Run single PHP test file
```

### JavaScript Tests (when implemented)
```bash
npm test               # Run Jest/React Testing Library tests
npm run test:watch     # Run tests in watch mode
npm run test:single    # Run single test file
```

## Linting Commands

Currently, there are no linters configured. When adding linting:

### PHP Linting (when implemented)
```bash
composer lint          # Run PHP CS Fixer
composer lint:check    # Check PHP code style without fixing
```

### JavaScript Linting (when implemented)
```bash
npm run lint           # Run ESLint
npm run lint:fix       # Auto-fix ESLint issues
```

## Code Style Guidelines

### PHP Code Style

#### File Structure
- Use PSR-4 autoloading: `Content_Mood\` namespace maps to `app/` directory
- Classes: `PascalCase` (e.g., `Content_Mood_Model`)
- Files: `snake_case` with `.php` extension
- Traits: End with `Trait` (e.g., `HookTrait`)
- Interfaces: End with `Interface` (e.g., `Payment_GatewayInterface`)

#### Naming Conventions
- **Classes**: PascalCase (e.g., `Content_Mood_Analyzer`)
- **Methods**: camelCase (e.g., `initPlugin()`, `defineConstants()`)
- **Properties**: camelCase for public, snake_case for private/protected
- **Constants**: UPPER_SNAKE_CASE (e.g., `CONTENT_MOOD_ANALYZER_VERSION`)
- **Functions**: snake_case (e.g., `content_mood_analyzer()`)

#### Formatting
- Use 4 spaces for indentation (WordPress standard)
- Opening braces on same line as control structures
- Space after control structure keywords: `if (`, `foreach (`, etc.
- Space around operators: `$a = $b + $c`
- No trailing whitespace
- Unix line endings (LF)

#### Documentation
- Use PHPDoc comments for all classes, methods, and properties
- Include `@param`, `@return`, `@var` tags
- Describe parameters and return values clearly

#### Example
```php
<?php
namespace Content_Mood\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Content_Mood_Model
 * Handles content mood data model operations.
 *
 * @package Content_Mood\Models
 */
class Content_Mood_Model {

    /**
     * Get posts with filters
     *
     * @param array $args Filters
     * @param int $per_page Posts per page
     * @param int $offset Offset for pagination
     * @param string $sort Sort order (asc or desc)
     * @return array
     */
    public static function list( $args = array(), $per_page = 2, $offset = 0, $sort = 'desc' ) {
        // Implementation
    }
}
```

### JavaScript/React Code Style

#### File Structure
- Components: PascalCase (e.g., `Settings.jsx`, `Pagination.jsx`)
- Utilities: camelCase (e.g., `apiHelpers.js`)
- Files: PascalCase for components, camelCase for utilities

#### React Components
- Use functional components with hooks
- Prefer arrow functions for component definitions
- Use destructuring for props
- Export default components

#### Naming Conventions
- **Components**: PascalCase (e.g., `SentimentAnalyzer`)
- **Functions**: camelCase (e.g., `fetchSentimentPosts`)
- **Variables**: camelCase (e.g., `selectedSentiment`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `API_BASE_URL`)

#### Imports
- Group imports: React, third-party libraries, local components/utilities
- Use absolute imports when possible
- No wildcard imports (`import *`)

#### Formatting
- Use 4 spaces for indentation
- Semicolons required
- Single quotes for strings (except JSX attributes)
- Trailing commas in multi-line structures
- Max line length: 100 characters

#### Example Component
```jsx
import React, { useState, useEffect } from 'react';

const SentimentAnalyzer = ({ initialSentiment = 'positive' }) => {
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedSentiment, setSelectedSentiment] = useState(initialSentiment);

    useEffect(() => {
        fetchSentimentPosts();
    }, [selectedSentiment]);

    const fetchSentimentPosts = async () => {
        setLoading(true);
        try {
            const response = await fetch(`${API_BASE_URL}/posts/${selectedSentiment}`);
            const result = await response.json();
            setPosts(result.posts || []);
        } catch (error) {
            console.error('Error fetching posts:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="sentiment-analyzer">
            {/* JSX content */}
        </div>
    );
};

export default SentimentAnalyzer;
```

### CSS/Styling (Tailwind CSS)

#### Class Naming
- Use Tailwind utility classes
- Custom classes: kebab-case (e.g., `sentiment-analyzer-public`)
- BEM methodology for custom components when needed

#### Responsive Design
- Mobile-first approach
- Use Tailwind responsive prefixes: `sm:`, `md:`, `lg:`, `xl:`

#### Custom Properties
- Define custom colors, spacing, etc. in `tailwind.config.js`
- Use semantic names (e.g., `ec-title` for title color)

### Error Handling

#### PHP
- Use WordPress error handling patterns
- Return `WP_Error` objects for API failures
- Use try-catch for external API calls
- Log errors appropriately

#### JavaScript
- Use try-catch for async operations
- Handle API errors gracefully
- Show user-friendly error messages
- Use console.error for debugging

### Security Best Practices

#### PHP
- Always escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize input: `sanitize_text_field()`, `wp_kses()`
- Use prepared statements for database queries
- Nonce verification for forms and AJAX

#### JavaScript
- Sanitize user inputs before API calls
- Use HTTPS for all external requests
- Avoid inline event handlers
- Validate data on both client and server

### WordPress Integration

#### Hooks and Filters
- Use WordPress action/filter hooks appropriately
- Prefix custom hooks: `content_mood_`
- Follow WordPress naming conventions

#### Database
- Use `$wpdb` for custom queries
- Prefix table names: `wp_content_mood_`
- Use post meta for post-related data: `_post_sentiment`

#### REST API
- Namespace: `content-mood-analyzer/v1`
- Use WP_REST_Controller for custom endpoints
- Proper permission callbacks

### File Organization

```
content-mood-analyzer/
├── app/                    # PHP backend
│   ├── Abstracts/         # Abstract classes
│   ├── Controllers/       # Controller classes
│   ├── Helpers/          # Utility functions
│   ├── Interfaces/       # Interface definitions
│   ├── Models/           # Data models
│   └── Traits/           # Reusable traits
├── spa/                   # React frontend
│   ├── admin/            # Admin panel components
│   ├── public/           # Public components
│   └── common/           # Shared components
├── assets/                # Static assets
├── build/                 # Compiled assets
└── vendor/                # Composer dependencies
```

### Git Workflow

#### Commit Messages
- Use imperative mood: "Add feature" not "Added feature"
- Start with type: feat:, fix:, docs:, style:, refactor:, test:, chore:
- Keep first line under 50 characters
- Add detailed description for complex changes

#### Branching
- `main`/`master`: Production code
- `develop`: Development branch
- `feature/*`: New features
- `bugfix/*`: Bug fixes
- `hotfix/*`: Urgent production fixes

### Deployment

#### Build Process
1. Run full build: `npm run build`
2. Test in staging environment
3. Deploy to production
4. Clear caches if needed

#### Versioning
- Follow semantic versioning: MAJOR.MINOR.PATCH
- Update version in `content-mood-analyzer.php`
- Update package.json if needed

## Additional Notes

- No Cursor rules or Copilot instructions found
- Plugin follows WordPress coding standards
- Uses modern JavaScript (ES6+) and React hooks
- Tailwind CSS for styling with custom theme extensions
- PSR-4 autoloading for PHP classes</content>
<parameter name="filePath">C:\Users\User\Local Sites\add\app\public\wp-content\plugins\content-mood-analyzer\AGENTS.md