OnAppWHMCS
==========

Fixes for WHMCS OnApp Integration

This patch fixes the following issues in the OnAPP WHMCS integration:

* Fix for template selection not being applied
* Fix for rate limit not being applied (defaults to 0)

#### What does this patch affect?

* OnApp VPS Servers Module (20th March 2014)

#### How do I install the patch?

1. Backup your WHMCS installation
2. Drag the onappVPS.php file to your `public_html/modules/servers/onappVPS` directory
