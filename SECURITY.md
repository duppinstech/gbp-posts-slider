# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.0.x   | Yes       |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

To report a vulnerability, email: **security@yoursite.com**

Include as much detail as possible:
- Plugin version
- WordPress version
- PHP version
- Description of the vulnerability
- Steps to reproduce
- Potential impact

We will acknowledge receipt within 2 business days and provide a resolution timeline. We ask that you give us a reasonable amount of time to address the issue before any public disclosure.

## Scope

The following are in scope:

- SQL injection
- Cross-site scripting (XSS)
- Cross-site request forgery (CSRF)
- Authentication or authorization bypass
- Sensitive data exposure (e.g. OAuth tokens stored insecurely)
- Remote code execution

## Security Best Practices for Users

- Always keep the plugin updated to the latest version.
- If you enter Google API credentials in the admin, ensure your WordPress admin is protected by HTTPS.
- Prefer defining credentials via wp-config.php constants over storing them in the database.
- Limit access to wp-config.php at the server level.
- Regularly rotate your Google OAuth credentials if you suspect compromise.
