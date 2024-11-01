=== Plugin Name ===
Contributors: simunix
Tags: age verify, age verification, verification, age
Requires at least: 5.2
Tested up to: 6.5.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Age verification for WooCommerce installations using the Age Verify UK method

== Description ==

UK customer Age Verification from AVUK is much more than just a pop box that anyone can tick to say that they are 18 years or over.
The fundamental difference with this plugin is that it will take the name and address details your customer has used to register an account with on your website, and match against a database of UK people that contains date of births. 
Any website using Wordpress and WooCommerce that sells age restricted products is required to prove they have only sold such products to customers over the age of 18. Verifying UK customers against actual data is the reliable and easy way to do this, with no inconvenience to your customers of manual age checks in the purchase process, thus reducing cart abandonment.
Age restricted items include tobacco products and e-cigarettes, alcohol, 18+ films and computer games, bladed items, acids and other corrosive substances, offensive weapons, betting. This service uses a our API which is a 3rd party resource and requires an active credit balance.

Instant Age Verification 
========================

During the checkout process, your customer will input their name and address detail in registering for an account, or be logged in to an existing account containing these details.
The plugin will instantly compare their name and address to a UK database and verify if they are age 18 or over. This age verification will be seamless in the background and your website’s process of checking out and paying for basket items will continue as normal.
Any UK customer buying age restricted items that is not verified as 18+ will receive a message and be unable to proceed to purchase. You can then employ your own age checks on the customer and be able to flag them in the admin section as ‘manually verified’. They will then be allowed to purchase their age restricted items through your usual checkout process.

This service uses a our API which is a 3rd party resource and requires an active credit balance.

Demo our Age Verification data service here: [https://ageverifyuk.com/#demo)

Everything you need to know
===========================
*    Works using tried and tested data and methods from ageverifyuk.com which is an api developed by Simunix Ltd, established in 1998.
*    Matches name and address details for a UK customer against AVUK data sources and verifies if they are aged 18 or over.
*    All your customers can be viewed in the admin section where they will have a status of age verified or unverified. Here you have the ability to manually verify a customer, for use when they could not be automatically verified but have since passed your own proof of age checks.
*    You will be able to view a list of all your products in the admin section and tick any that are age restricted and require customer age verification. If all products on your website are age restricted (e.g. e-cigarette website) you can simply turn on age verification for the whole website.
*    As the plugin uses data from the AVUK.io website, you will need to set up a AVUK account.
*    Within your AVUK account you can update your login / password details and buy credits. 10 credits are deducted from your credit balance for every age verification check undertaken. The cost of each verification check will be between £0.10 and £0.29 depending on the size of credit pack you buy.
*    You will be able to view your credit balance and top up your credits at any time. You will receive notifications when your balance is running low.

== Installation == 
*    From the Wordpress menu go to Plugins > Add New
*    Search for AVUK Age Verification
*    Find the AVUK Age Verification for WooCommerce plugin and click on ‘Install Now’
*    Follow the instructions on the setup page. You will be asked to setup an account on AVUK.io and input your unique API key.


== Frequently Asked Questions ==

= Can I view a list of customers that have been age verified? =

Yes. In the admin section you can see all your customers with a status of ‘verified’ or ‘not verified’ against each one.

= Can I choose which products I want customers to be age verified to purchase? =

Yes. Any product can be individually ticked as ‘age restricted’ and then an age check will be performed before a customer is allowed to buy it. If all your products are age restricted you can simply tick one box which turns age verification on for the whole website.

= Why do I need a AVUK account? =

The plugin works on the api methods used in AVUK.io. In order to access this it is necessary to have a AVUK account that produces a unique API key. Without this API key the age verification service cannot be used.

= How do I pay for Credits? =

The plugin is run from AVUK.io which operates on a prepayment basis (similar to a Pay As You Go mobile phone).

= How long do my credits last? =

12 months

= If a customer is not age verified do I still get charged? =

If we have found a match of the customer’s details and it is found they are under 18, you will be charged as the system has performed a check and returned an answer of ‘Found – not verified’. Selling an age restricted item to an under age person will have been avoided.
In the rare event we cannot find a match of the customer’s details in our UK data you will not be charged for that check.

= What if I run out of credits? =

We send automated notifications and email warnings when your credit balance is running low. If you do not buy more credits and run out altogether, the age verification service will cease to operate. In this situation customers will still be able to purchase their items but *will not be age verified* – we strongly advise against running out of credits as you will no longer be compliant with verifying the age of a person that is buying an age restricted item. The service will resume automatically when credits are purchased.

= Why do only UK customers get age verified? =

AVUK only has a licence to check against UK data sources and so cannot perform the same match and age verification against customers that are resident in other countries.


== Changelog ==

= 1.4 =
* Removed OCR
* Tested on 6.5.2

= 1.3.3 =
* Fixed problem with credits not accurately reporting

= 1.3.2 =
* Tested on 6.1.1

= 1.3.1 =
* Fixed missing javascript file

= 1.3.0 =
OCR functionality added

= 1.2.0 =
* Hard launch of Age Verify. Users will now manage their account and credits at https://ageverifyuk.com

= 1.1.5 =
* Fixed bugs with manual verification
* Softlaunch of Age Verify branding

= 1.1.4 =
* Fixed a bug preventing customisable products from running age verification

= 1.1.3 =
* Included functionality to modify dialog which appears when customer does not validate

= 1.1.2 =
* Fix to previous upgrade script causing a warning in some instances
* Inclusion of new t2a age verification table to database which allows guest customers to be manually verified and prevent repeated guest checkouts from being verified multiple times
* Inclusion of new t2a age verification table which captures all failed attempts, to allow for manual verification
* Support address line 2 

= 1.1.1 =
* Changed the way individual products that are to be validated are stored in the database to reflect the optimisations made to customers

= 1.1.0 =
* Changed the way validated customers are stored in the database to improve scalability and reduce overheads
* Introduced plugin version tracking in the database so legacy implementations can be upgraded without loss
* Optimised displaying and processing of users in the admin page
* Fixed a bug where running verification in admin pages would not remove verification from a user who did not pass if they previously passed or were set to valid via manual verification
* Fixed syntax errors causing PHP notices

= 1.0.8 = 
* Improved error handling

= 1.0.7 =
* Tested on version 5.8 of wordpress

= 1.0.6 =
* Support for variable products has been added

= 1.0.5 =
* Replaced insecure HTTP calls with HTTPS calls

= 1.0.4 =
* Fixed bug in product pagination which prevented more than a single page of products from being added to the age restriction list

= 1.0.3 =
* Fixed bug relating to validation of API key when the balance on the account is 0
* Fixed settings link in Installed Plugins menu
* Pointed Get an API Key link in Installed Plugins to registration page of AVUK instead of homepage
* Verification disabled in customers page if user credit balance is not sufficient to perform a single validation
* Amended copy on demo page

= 1.0.2 =
* Added additional parameter to client_info to let us know that users are using our service through wordpress. This allows us to provide a better experience to Wordpress users.

= 1.0.1 =

* Removed instances of CURL and replaced with HTTPS API
* Added local sanitisation and validation to API calls

= 1.0 =
* First official release of this brand new plugin following weeks of internal and external testing.

