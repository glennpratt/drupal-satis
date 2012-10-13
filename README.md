drupal-satis
====================

Extends Satis to crawl drupal.org for composer projects.


`bin/drupal-satis`

First, populate `satis.json` with minimum composer details, name, description.

Crawl sites to add potential project to satis.json

`bin/drupal-satis crawl`

Gather package info and build Satis HTML in package directory.

`bin/drupal-satis build satis.json packages`

If you get errors, remove the repository in question from `satis.json`.  If it's invalid JSON, you might open an issue with the project in question.

Checkout the HTML.

`open packages/index.html`
