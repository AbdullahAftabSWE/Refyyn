# Contributing to Refyyn

Thank you for your interest in contributing to Refyyn! We welcome contributions from the community.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:
- A clear, descriptive title
- Steps to reproduce the issue
- Expected behavior vs actual behavior
- Screenshots (if applicable)
- Your environment (OS, PHP version, browser, etc.)

### Suggesting Features

We love feature suggestions! Please create an issue with:
- A clear description of the feature
- Why it would be useful
- Any implementation ideas you have

### Pull Requests

1. **Fork the repository** and create your branch from `master`
2. **Make your changes** following our coding standards
3. **Test your changes** - ensure existing tests pass and add new tests if needed
4. **Update documentation** - update README.md or CLAUDE.md if needed
5. **Submit a pull request** with a clear description of your changes

## Development Setup

See the [README.md](README.md) for detailed setup instructions.

Quick start:
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

## Coding Standards

### PHP/Laravel

- Follow PSR-12 coding standards
- Use Laravel best practices and conventions
- Run code formatter before committing:
  ```bash
  ./vendor/bin/pint
  ```
- Keep controllers thin - move business logic to services/actions when needed
- Use meaningful variable and method names

### Vue/JavaScript

- Follow Vue 3 Composition API patterns
- Use TypeScript-style JSDoc comments for complex functions
- Keep components focused and reusable
- Use Tailwind CSS classes (avoid custom CSS when possible)

### Database

- Always create migrations for schema changes
- Never edit existing migrations that have been pushed to master
- Write descriptive migration names: `create_feedback_table` not `add_stuff`

### Commits

- Write clear, descriptive commit messages
- Use present tense: "Add feature" not "Added feature"
- Reference issue numbers when applicable: "Fix #123: ..."

## Testing

Run tests before submitting:
```bash
composer run test
```

Add tests for new features when applicable.

## Code Review Process

1. All submissions require review
2. We may suggest changes, improvements, or alternatives
3. Once approved, a maintainer will merge your PR

## What We're Looking For

**High Priority:**
- Bug fixes
- Performance improvements
- Security fixes
- Documentation improvements
- Tests

**Welcome:**
- New features that align with Refyyn's vision
- UI/UX improvements
- Accessibility improvements
- Internationalization/localization

**Please Discuss First:**
- Major architectural changes
- Breaking changes
- New dependencies

## Questions?

Feel free to open an issue for any questions about contributing.

## License

By contributing to Refyyn, you agree that your contributions will be licensed under the MIT License.
