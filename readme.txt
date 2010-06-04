=== WordPress-to-lead for Salesforce CRM ===
Contributors: joostdevalk
Tags: crm, contact form, contactform, wordpress to lead, wordpresstolead, salesforce.com, salesforce, salesforce crm, contact form plugin, contact form builder, Wordpress CRM
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 1.0.5

WordPress-to-Lead for Salesforce CRM creates a solid integration between your WordPress install(s) and your Salesforce.com account! People can enter a contact form on your site, and the lead goes straight into Salesforce CRM: no more copy pasting lead info, no more missing leads: each and every one of them is in Salesforce.com for you to follow up.

== Description ==

WordPress-to-Lead for Salesforce CRM creates a solid integration between your WordPress install(s) and your [Salesforce CRM](http://www.salesforce.com) account! People can enter a contact form on your site, and the lead goes straight into Salesforce CRM: no more copy pasting lead info, no more missing leads: each and every one of them is in Salesforce.com for you to follow up.

You can fully configure all the different settings for the form, and then use a shortcode to insert the form into your posts or pages, or you can use the widget that comes with the plugin and insert the form into your sidebar!

Please see this [WordPress-to-Lead Demo video](http://www.youtube.com/watch?v=hnMzkxPUIyc) to get a full grasp of the power this plugin holds, and visit the [Salesforce WordPress page]( http://www.salesforce.com/form/signup/wordpress-to-lead.jsp?d=70130000000F4Mw). Check out this page to learn more about [CRM for Small Business](http://www.salesforce.com/smallbusinesscenter/).

== Screenshots ==

1. An example form generated with WordPress-to-Lead for Salesforce CRM
2. The backend administration for WordPress-to-Lead for Salesforce CRM

== Installation ==

1. Upload the `plugin` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your Salesforce.com Organisation ID on the WordPress-to-Lead plugin configuration page.

== Frequently Asked Questions ==

= Where do I find my Salesforce organisation ID? =
To find your Organisation ID, do the following:
* log in to your SalesForce.com account
* go to Setup &raquo; Company Profile &raquo; Company Information
* you'll find the Organisation ID in the lower right hand corner of your screen

= How do I change the order of input fields? =
Right now, the only way of ordering input fields is by changing the position numbers on the right hand side of the input fields table in the admin settings.

= How do I apply my own styling to the form? =
Disable the "Use Form CSS" checkbox, and copy the form css to your own css file, then start modifying it!

= Is it possible to make multiple forms with this plugin? =
Currently this plugin does not allow for that, we have it on our to do list for a future version though.

= How do I change the Lead Source that shows up in Salesforce? =
You can easily change this by going into the WordPress-to-Lead admin panel and, under Salesforce settings, changing the Lead Source.

= Can I change the submit button? =
Of course you can! Go into the WordPress-to-Lead admin panel and, under Form Settings, change the text from the default "Submit" to whatever you'd like it to be!

== Changelog ==

= 1.0.5 =
* Fix in backend security, preventing XSS hack in the backend.

= 1.0.4 =
* CSS fix for when sidebar widget and contactform are on the same page.

= 1.0.3 =
* Fix in email verification.

= 1.0.2 =
* One more escape, plus a check to see whether the email address entered is valid.

= 1.0.1 =
* Added escaping around several fields to prevent XSS vulnerabilities.

= 1.0 =
* Initial release.