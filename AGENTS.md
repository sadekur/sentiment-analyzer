# AGENTS.md

## Build Commands
- `npm run build` - Build all bundles (admin, public) with webpack
- `npm run build:admin` - Build admin React bundle only
- `npm run build:public` - Build public React bundle only
- `npm run watch` - Watch mode for development builds

## Lint/Test Commands
No linting or testing frameworks configured. Run manual checks.

## Code Style Guidelines
### PHP
- PSR-4 autoloading: `Sentiment\` namespace for `app/` directory
- Class naming: PascalCase (e.g., `Sentiment_Analyzer`, `Menu`)
- Method naming: camelCase (e.g., `add_admin_menu`, `settings_page`)
- Use traits for shared functionality (e.g., `Hook` trait)
- WordPress hooks: Use `add_action`/`add_filter` via trait methods
- Error handling: Use `wp_die` for permission checks
- Constants: Define plugin constants in main class (e.g., `SENTIMENT_ANALYZER_URL`)

### JavaScript/React
- Functional components with arrow functions
- Import React explicitly: `import React from "react"`
- JSX: Use parentheses for multi-line JSX
- Mounting: Check element existence before rendering (e.g., `if (rootElement)`)
- No TypeScript: Use plain JS
- Naming: camelCase for variables/functions, PascalCase for components

### General
- No comments in code unless necessary
- Follow WordPress coding standards for PHP
- Use Tailwind CSS for styling (configured in `tailwind.config.js`)
- Assets: Enqueue via `Assets.php` class</content>
<parameter name="filePath">C:\Users\User\Local Sites\add\app\public\wp-content\plugins\sentiment-analyzer\AGENTS.md