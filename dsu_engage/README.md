CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Functionality
* Troubleshooting
* Maintainers

INTRODUCTION
------------

The ENGAGE project is a key solution within Nestlé’s Personalized Consumer
Experiences (PCE) strategy. The Engage Contact Us integration in LightNest
enables contact form submissions to be sent to the Consumer Services operations
directly for further processing in a streamlined and efficient manner.


REQUIREMENTS
------------

This module requires the following modules:

* Lightnest components: Core

INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

1. Go to `/admin/config/lightnest/dsu-engage`

2. Select _Connection Settings_ and fill in the credentials

3. Select _Form Settings_ and configure the following fields:
  - **Enable engage form Page**: Enable or disable the contact us page.
  - **Contact Us page URL**: The URL of your choice to display the form
  - **Request Type**: Select the type of requests that should be available in
    the form (Question/Suggestion, Complaint, Praise).
  - For each of the enabled _Request Types_, you need to select which fields
    will be displayed, and which fields are required.

4. Select _Additional settings_ and configure the following fields:
  - **Privacy policy text**: Text of the privacy policy that must be accepted.
  - **Privacy policy link text**: Link text.
  - **Privacy policy url**: URL to which the previous link will direct.
  - **Enable Engage Additional Notes**: Whether or not to display the footer
    with additional details.
  - **Additional Footer Notes**: The text to display in the footer.
  - **Additional Contact No**: The phone number to display in the footer.

5. Select _Data Settings_ and configure the following fields:
  - **Country List**: Countries that will be available in the form selector.
  - **State list by country**: States that will be available in the form selector.
  - **Phone list**: Phone prefix that will be available in the form selector.

6. Select _Tooltip settings _ and configure the following fields:
  - **Batch Code tooltip**: Tooltip Text for Batch Code.
  - **Bar code tooltip**: Tooltip Text for Bar Code.


FUNCTIONALITY
-------------

* The module provides a page to display the contact us form in the URL
  defined in the settings form.

* By default the module provides a block form to display the contact us
  form in a block which can send submissions to _Engage_ instead of storing
  them locally.

**IMPORTANT!** If you were using the Webform module in the previous version,
please replace it with the new block available in the main module
(Webform will no longer support connection with Engage).
You can attach the new block in the administration (`/admin/structure/block`)
or you can use any of the following contrib Drupal modules:
  - Block Field (https://www.drupal.org/project/block_field)
  - Paragraph Blocks (https://www.drupal.org/project/paragraph_blocks)

Alternatively, you can follow your custom implementation to integrate it.

TROUBLESHOOTING
---------------

* If the content does not display correctly, make sure all settings are correct.
  Make sure your content is up to date with the latest version of the module.

MAINTAINERS
-----------

* Nestle Webcms team.
