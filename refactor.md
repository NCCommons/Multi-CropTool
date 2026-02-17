# Code Refactoring Plan - NccropTool

## Project Overview

NccropTool is a web application for cropping multiple photos at once using CropTool by Dan Michael O. Heggø, designed for use at nccommons.org. The project consists of multiple tools:

1. **Multi CropTool** (`multi2/`) - Batch cropping functionality
2. **Mass Upload** (`mass2/`) - Mass file upload capability
3. **Commons to NCCommons** (`c2ncc/`) - Transfer files between Wikimedia Commons and NCCommons

---

## Critical Weaknesses & Issues

### 1. Security Vulnerabilities

#### 1.1 Insecure File Upload
- **Location**: `public_html/mass2/save.php:6-7`, `public_html/auth/api.php:114`
- **Issue**: No file type validation, no file size limits, predictable filenames
- **Risk**: Arbitrary file upload, potential RCE
```php
// Dangerous: Direct use of user-provided filename
$file_name = $file['name'];
$file_destination = $targetDirectory . $file_name;
```

#### 1.2 Path Traversal Vulnerability
- **Location**: `public_html/mass2/save.php:13`
- **Issue**: No sanitization of filenames
- **Risk**: Files can be written to arbitrary locations

#### 1.3 SSL Verification Disabled
- **Location**: `public_html/multi2/header.php:71`, `public_html/auth/api.php:71`
- **Issue**: `CURLOPT_SSL_VERIFYPEER` set to false
- **Risk**: Man-in-the-middle attacks

#### 1.4 Deprecated FILTER_SANITIZE_STRING
- **Location**: `public_html/auth/api.php:89,90,133`
- **Issue**: Using deprecated PHP filter
- **Risk**: Potential security issues

#### 1.5 Hardcoded API URLs
- **Location**: Multiple JS files (`upload.js:31`, `crops.js:94`, `imginfo.js:108`)
- **Issue**: Production URLs hardcoded in client-side code
- **Risk**: Debug information leakage, inflexibility

#### 1.6 Global Variable Usage
- **Location**: `public_html/mass2/api_x.php:4`
- **Issue**: Using global variables
- **Risk**: Unpredictable state, potential for variable pollution

### 2. Code Quality Issues

#### 2.1 Duplicate Code
- **Location**: Multiple similar functions across files
- **Examples**:
  - `count_crop_plus_one()` in `crops.js:2`
  - `count_up_plus_one()` in `upload.js:2`
  - `count_info_plus_one()` in `imginfo.js:2`
  - `change_color()` duplicated in multiple files

#### 2.2 Mixed Languages and Encoding
- **Location**: `public_html/mass2/js/up.js:187,189`
- **Issue**: Arabic comments mixed with English code
- **Impact**: Code maintainability

#### 2.3 Inconsistent Naming Conventions
- **Location**: Throughout codebase
- **Issues**:
  - Mixed camelCase and snake_case
  - Variables like `$uu`, `$u`, `$lal` (non-descriptive)
  - Functions like `gp()` (non-meaningful name)

#### 2.4 Missing Type Hints and Return Types
- **Location**: All PHP files
- **Issue**: No parameter type declarations or return type declarations

#### 2.5 No Error Logging
- **Location**: Throughout codebase
- **Issue**: Errors only shown to users, not logged for debugging

#### 2.6 Dead/Duplicate Code in `old/` Directory
- **Location**: `public_html/old/`
- **Issue**: Multiple versions of old code not cleaned up
- **Impact**: Confusion, potential security risks if accidentally served

### 3. Architecture Issues

#### 3.1 Tight Coupling
- **Location**: Throughout
- **Issue**: Business logic mixed with presentation
- **Example**: HTML generation within PHP functions

#### 3.2 No Dependency Injection
- **Location**: All PHP files
- **Issue**: Hardcoded dependencies, difficult to test

#### 3.3 Procedural Code in Global Namespace
- **Location**: Most PHP files
- **Issue**: No proper class-based organization (some namespaces used but inconsistently)

#### 3.4 Inline JavaScript
- **Location**: Multiple PHP files
- **Issue**: JavaScript embedded in HTML/PHP strings

#### 3.5 No Frontend Build Process
- **Location**: All JS/CSS files
- **Issue**: No bundling, no minification, no transpilation

### 4. Database & State Management Issues

#### 4.1 Session Management
- **Location**: `public_html/auth/api.php:20-21`
- **Issue**: Direct `$_SESSION` access without encapsulation

#### 4.2 No Database Abstraction Layer
- **Issue**: If database is added later, no consistent interface

### 5. Performance Issues

#### 5.1 Synchronous AJAX Requests
- **Location**: Multiple JS files
- **Issue**: Operations blocked sequentially

#### 5.2 No Caching Strategy
- **Issue**: CDN files re-fetched, no browser cache headers set

#### 5.3 Inefficient DOM Manipulation
- **Location**: `upload.js:88-112`
- **Issue**: Multiple direct DOM operations instead of batching

### 6. Testing & Quality Assurance

#### 6.1 No Unit Tests
- **Issue**: Zero test coverage

#### 6.2 No Integration Tests
- **Issue**: No end-to-end testing

#### 6.3 No Code Quality Tools
- **Issue**: No PHPStan, Psalm, ESLint, or similar tools configured

### 7. Documentation Issues

#### 7.1 Minimal Documentation
- **Issue**: No API documentation, no architecture docs

#### 7.2 No Code Comments
- **Issue**: Complex functions lack explanatory comments

#### 7.3 Outdated README
- **Location**: `README.md`
- **Issue**: Very minimal, doesn't explain setup or usage

---

## Refactoring Plan

### Phase 1: Critical Security Fixes (HIGH PRIORITY)

| Task | File | Action |
|------|------|--------|
| Implement file upload validation | `mass2/save.php` | Add MIME type checking, file size limits, secure filename generation |
| Add path traversal protection | `mass2/save.php` | Use `basename()`, validate paths |
| Enable SSL verification | `multi2/header.php`, `auth/api.php` | Remove `CURLOPT_SSL_VERIFYPEER` false |
| Replace deprecated filters | `auth/api.php` | Use proper sanitization methods |
| Move URLs to configuration | All JS files | Create config.js with environment-based URLs |
| Remove global variables | `mass2/api_x.php` | Pass parameters properly |
| Add CSRF protection | All forms | Implement CSRF tokens |
| Sanitize all user input | All PHP files | Implement proper input validation |
| Add rate limiting | API endpoints | Prevent abuse |

### Phase 2: Code Deduplication & Organization

| Task | Action |
|------|--------|
| Create shared JavaScript utilities module | Consolidate duplicate functions (`count_*_plus_one`, `change_color`, `make_width_and_high`) |
| Create shared PHP utilities | Common functions in one file |
| Standardize naming conventions | Define and apply consistent naming |
| Remove `old/` directory from production | Delete or move to archive |
| Separate concerns | Split business logic from presentation |

### Phase 3: Architecture Improvements

| Task | Action |
|------|--------|
| Implement proper MVC structure | Separate controllers, models, views |
| Add dependency injection container | Use PSR-11 container |
| Create service layer | Business logic in service classes |
| Implement routing system | Centralized route handling |
| Add middleware system | Authentication, logging, error handling |
| Create configuration management | Environment-based config |

### Phase 4: Performance Optimizations

| Task | Action |
|------|--------|
| Implement async/await properly | Parallel independent operations |
| Add HTTP caching headers | Leverage browser cache |
| Implement lazy loading | Load images/components on demand |
| Bundle and minify assets | Set up build process |
| Add CDN for static assets | Offload static content |

### Phase 5: Testing & Quality Assurance

| Task | Action |
|------|--------|
| Set up PHPUnit | Write unit tests for PHP |
| Set up Jest/Puppeteer | Write JS unit and E2E tests |
| Add PHPStan/Psalm | Static analysis for PHP |
| Add ESLint/Prettier | Lint and format JS |
| Add PHP CS Fixer | Code style for PHP |
| Set up CI/CD pipeline | Automated testing on push |

### Phase 6: Documentation

| Task | Action |
|------|--------|
| Write comprehensive README | Installation, usage, contribution |
| Add API documentation | OpenAPI/Swagger spec |
| Document architecture | Architecture decision records |
| Add inline comments | Explain complex logic |
| Create developer guide | For contributors |

---

## Proposed Directory Structure

```
public_html/
├── config/
│   ├── config.php           # Main configuration
│   ├── routes.php           # Route definitions
│   └── services.php         # Service container
├── src/
│   ├── Controller/          # HTTP request handlers
│   ├── Service/             # Business logic
│   ├── Model/               # Data models
│   ├── View/                # Template rendering
│   └── Middleware/          # Auth, logging, etc.
├── public/
│   ├── assets/
│   │   ├── js/
│   │   ├── css/
│   │   └── images/
│   └── index.php            # Front controller
├── tests/
│   ├── Unit/
│   ├── Integration/
│   └── E2E/
├── vendor/                  # Composer dependencies
├── composer.json
├── phpunit.xml
└── README.md
```

---

## Implementation Priority

### Immediate (This Week)
1. Fix file upload vulnerabilities
2. Enable SSL verification
3. Add input sanitization

### Short-term (This Month)
1. Remove duplicate code
2. Implement CSRF protection
3. Add basic error logging
4. Move URLs to configuration

### Medium-term (Next 3 Months)
1. Restructure to MVC
2. Add dependency injection
3. Implement proper routing
4. Set up testing framework

### Long-term (Next 6 Months)
1. Complete test coverage
2. Full documentation
3. CI/CD pipeline
4. Performance monitoring

---

## Success Metrics

- **Security**: Zero critical vulnerabilities in security audit
- **Code Quality**: >80% test coverage, <5 warnings from static analysis
- **Performance**: <2s page load time, <500ms API response time
- **Maintainability**: Onboarding new developer takes <1 day

---

## Notes

- The codebase shows signs of organic growth without proper architecture planning
- Multiple "old" directories suggest incomplete refactoring attempts
- The project uses MediaWiki OAuth for authentication - this should be centralized
- Consider using a modern PHP framework (Symfony, Laravel) instead of custom implementation
