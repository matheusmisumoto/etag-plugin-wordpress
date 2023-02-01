# ETag Management Plugin for WordPress

This simple plugin generates Entity Tag (eTag) headers for WordPress posts and pages and check whether or not return a new HTML document to the user.

## Getting Started

To install this plugin, you can download the .zip file and upload the plugin to your website using the Administration Panel.

## Requirements
- WordPress 6.0+
- PHP 7.4+

## How it works

The eTag is generated based on two data.

First, when the page was last modified. This can be the timestamp when the post or page was last edited, or when the latest comment was posted, whichever is most recent.

Second, the content of the page itself. A hash is created based on the full HTML output to the user. So, any modification of the content, including menus, footers and metatags will create a new eTag.

Then, ETag Managment checks if:

- The user is not logged in, to avoid caching while editing the WordPress website,
- The cached last-modified timestamp header matches with the timestamp of the page on server,
- And the cached eTag header matches with the eTag generated on the request, both weak and strong validators.

If all requirements match, ETag Management makes the WordPress website returns a HTTP 304 Not Modified response. Therefore, the browser will retrieve the cached version in users' local storage and saving network bandwidth.

---
Developed with ☕ by [Matheus Misumoto](https://matheusmisumoto.dev/) in Santos, Brazil