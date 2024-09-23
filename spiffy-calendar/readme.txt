=== Spiffy Calendar ===
Contributors: spiffyplugins
Donate Link:  https://spiffycalendar.spiffyplugins.ca/bonus-add-ons/
Requires at least: 5.3
Tested up to: 6.6
Stable tag: 4.9.15
License: GPLv2
Tags:  calendar,event,responsive,recurring,block

Manage and display your events in a responsive calendar with multiple views, widgets and shortcodes. Color-coded categories and recurrence support.

== Description ==

Manage and display your events in a responsive calendar with multiple views, widgets and shortcodes. Color-coded categories and recurrence support. The premium [Bonus Add-Ons](https://spiffycalendar.spiffyplugins.ca/bonus-add-ons/) supplements the plugin with additional themes, customizer support, ICS export, front end submit, CSV import/export and custom fields.

>[Demo and Documentation](https://spiffycalendar.spiffyplugins.ca)
>
>[Click here](https://spiffycalendar.spiffyplugins.ca/css-snippets/) for helpful CSS snippets.
>
>[Click here](https://spiffycalendar.spiffyplugins.ca/bonus-add-ons/) to learn about Bonus Add-Ons.

== Features ==

= Post/page displays: =

* Standard monthly calendar grid that toggles to a list view
* Category filter in the monthly calendar grid
* Responsive 3-column event listing
* Mini-calendar view for compact displays
* Weekly calendar grid
* Today's events list
* Upcoming events list

= Widgets: =

* Featured event
* Today's events list
* Upcoming events list
* Mini Calendar

= Categories: =

* Color-coded categories
* Option to display category color as background or foreground on the standard calendar grid
* Displays may be filtered by category list
* Optional sort categories alphabetically 
* Live category filter on the full calendar

= Other features: =

* Mouse-over details for each event
* Normal popup or expanded display of lists
* Events can display their author (optional)
* Add custom CSS styles or just use the defaults
* Display upcoming events in your MailPoet newsletters
* Display week number in the full calendar
* Filters to allow additional formatting
* Optional responsive display for the full size calendar
* Optional drop down boxes to quickly change month and year
* Front end quick links to edit/delete events for logged in admins

= Enter and display for each event: =

* title, 
* description, 
* location,
* link to Google map,
* event category, 
* status,
* link, 
* event start/end date
* event start/end time
* event recurrence details
* event hiding details
* event image
* custom fields [(premium)](https://spiffycalendar.spiffyplugins.ca/bonus-add-ons/)
	
= Schedule a wide variety of recurring events. =

* Events can repeat on a daily (set numbers of days), weekly, monthly (by date), monthly (by day of week) or yearly basis
* Repeats can occur indefinitely or a limited number of times
* Events can span more than one day
	
= Hide all events for specific days: =

* Hide repeating event for a single day such as a holiday
* Hide full days of events that span more than one day
* Substitute new title to replace hidden events
* Select override based on category
	
= Easy to use events manager in admin dashboard =

* Comprehensive options panel for admin
* Event management list with configurable column display, sort by date or category, filter by event status, event search
* User groups other than admin can be permitted to manage events
* Authors can be restricted to editing their own events only
* Pop up JavaScript calendars help the choosing of dates
* Events can be links pointing to a location of your choice
* Events can be marked as draft or pending to remove them from displays
	
**BONUS FEATURES AVAILABLE WITH DONATION**

* Premium themes
* Live theme customizer
* ICS export
* Front End submit form with captcha
* Front End edit/delete events
* Import/Export events via CSV
* Custom fields

[Learn more about bonus add-ons](https://spiffycalendar.spiffyplugins.ca/bonus-add-ons/)
	
== Languages ==

* Dutch (Courtesy Joek Brongers)
* French (Courtesy Mathieu Gaunet, www.mathieugaunet.com, contact@mathieugaunet.com)
* German (Courtesy Ingrid Maie)
* Polish (Courtesy of Krzysztof Kacprzyk)
* Spanish (Courtesy of Andrew Kurtis, WebHostingHub)
* Swedish (Courtesy of Kenneth Andersson)
* Turkish (Courtesy Dr Abdullah Manaz, www.manaz.net)

== Installation ==

1. Install the plugin from the Wordpress repository in the usual way.

2. Activate the plugin on your WordPress plugins page

3. Configure Calendar using the following pages in the admin panel:

   Spiffy Calendar -> Events

   Spiffy Calendar -> Categories

   Spiffy Calendar -> Options

4. Edit or create a page on your blog which includes one of the shortcodes:

[spiffy-calendar] for the monthly calendar

[spiffy-minical] for the mini version of the monthly calendar

[spiffy-upcoming-list] for the upcoming events list

[spiffy-todays-list] for the list of today's events

Add one of the spiffy widgets to your theme widget areas.

All of the shortcodes and widgets accept a comma separated list of category IDs, such as *cat_list='1,4'*. The category list must be a numeric list of the category number, not the category name.

The list shortcodes and widgets also accept an optional *limit* and *style* selection (Popup or Expanded). Popup is the default, classic style.

You can use the spiffy-upcoming-list expanded style shortcode in your MailPoet newsletter, with the following format (including arguments if needed):

[custom:spiffy-upcoming-list limit=4 ...]

>If you are upgrading from Version 2 you will need to re-add your widgets after upgrading to Version 3.
>

== Frequently Asked Questions ==

= How do I change the starting day of week on my calendar? =

The WordPress sitewide setting is used. See "Settings > General > Week starts on".

= I updated/added/deleted an event and it is not reflected in my calendar. Why is this? =

If you have a caching plugin, or if your theme has built-in caching, then you need to clear your cache for the areas where the calendar is displayed.

== Screenshots ==

1. Full calendar with category color as background color, and detailed display turned off to hide images

2. Full calendar, category color used for text and detailed display turned off

3. Mini calendar

4. Add an event

5. Responsive 3-column display

6. Shortcode generator in the classic editor

7. Calendar options

8. Upcoming events list, popup view has details appear when hovering over event title

9. Upcoming events list, expanded view

== Changelog ==

= 4.9.15 (August 19, 2024) =
= 4.9.14 (August 19, 2024) =

* Security: additional calls to esc_html on output

= 4.9.13  (August 16, 2024) =

* Security: fix user deletion check

= 4.9.12 (July 8, 2024) =

* Security: fix check of category id on admin tab

= 4.9.11 (March 13, 2024) =

* Security: fix permission check for admin tabs

= 4.9.10 (March 8, 2024) =

* Security: fix potential XSS from authenticated user
* Fix: added argument check to block attribute handler

= 4.9.9 (January 25, 2024) =

* Security: add sanitation check for event author on update as well

= 4.9.8 (January 24, 2024) =

* Security: add sanitation check to event author field

= 4.9.7 (January 2, 2024) = 

* Fix: removed incorrect error message after event copy

= 4.9.6 (November 30, 2023) =

* Security: fix potential XSS from authenticated user

= 4.9.5 (November 21, 2023) =

* New: option to change "Event Title" output label
* New: option to display category name before title

= 4.9.4 (May 3, 2023) =

* Security: remove unecessary hidden input

= 4.9.3 (May 1, 2023) =

* Tested with WP 6.2
* Tweak: full size list view CSS improvements
* Tweak: fix potential WP-CLI undefined variable

= 4.9.2 (December 15, 2022) =

* Security: Sanitize orderby and order on event management page
 
= 4.9.1 (February 9, 2022) =

* Security: additional security checks and sanitation, remove front quick links. Thanks to Ex.Mi (Patchstack) for all your help.

= 4.9.0 (January 27, 2022) =

* New: category filter option on full calendar
* New: option to display category key above the calendar
* New: calendar key table has been moved inside the calendar table. This might impact your custom CSS rules.
* New: support customizer updates with block themes
* Tweak: updated deprecated jQuery click shorthand

= 4.8.5 (March 31, 2021) =

* Change: upcoming event displays showed the wrong dates if the default PHP timezone was not UTC (which is what it is supposed to be!). This change will check the 
PHP timezone setting and correct it to UTC if necessary.

= 4.8.4 (March 31, 2021) = 

* Undo 4.8.3, this was the wrong solution

= 4.8.3 (March 31, 2021) =

* Change: adjust upcoming event date display to work better on sites that change the default system timezone (even though this should not be done!)

= 4.8.2 (March 19, 2021) =

* Update: New .pot file for translators

= 4.8.1 (March 16, 2021) =

* Fix: improve efficiency in db accesses for custom fields

= 4.8.0 (February 12, 2021) =

* New: option for full size calendar toggle between grid and list views
* New: option to display mini-calendar popup left, center or right (default)
* New: shortcut links to edit/delete events on the front end
* New: support for custom fields bonus feature
* New: support Mailpoet Version 3 newsletter shortcode for upcoming events
* New: remove support for Camptix, plugin is no longer available from WordPress repository
* Fix: format of Mailpoet Version 2 upcoming events list
* Tweak: add day-with-date class to minicalendar current-day
* Tweak: warn if leaving admin area without saving changes
* Tweak: rename "event" classname to "spiffy-event-group"
* Tweak: add event id to class list on front end views

= 4.7.2 (August 7, 2020) =

* Update: replace deprecated JavaScript in preparation for WordPress 5.5

= 4.7.1 (August 7, 2020) =

* Fix: typos in table names from version 4.7.0

= 4.7.0 (August 6, 2020) =

* Fix: remove constant table names to allow multi-site access

= 4.6.0 (July 29, 2020) =

* Fix: Gutenberg blocks configuration updated to remove deprecated code in preparation for WordPress 5.5. ** YOU WILL NEED TO REASSIGN SOME OF YOUR SPIFFY CALENDAR BLOCK SETTINGS. **

= 4.5.7 (July 23, 2020) =

* New: use WordPress date translation functions for months and weekday names to work better across all languages

= 4.5.6 (April 1, 2020) =

* Fix: front-end edit disable of recurrence and image upload 
* New: add some CSS classes to allow hiding some front-end edit fields

= 4.5.5 (April 1, 2020) =

* Fix: undefined index warning for setting $spiffy_edit_errors
* Tweak: update list of bonus add-on features

= 4.5.4 (March 21, 2020) =

* Fix: event search by enter key
* Tweak: remove category key table left/right styles to fit better with most themese

= 4.5.3 (March 1, 2020) =

* Fix: missing global definition

= 4.5.2 (February 29, 2020) =

* Fix: missing global definition in time_cmp function

= 4.5.1 (February 27, 2020) =

* Fix: typo that broke the mini-calendar shortcode (since 4.5.0)

= 4.5.0 (February 27, 2020) =

* New: bulk edit of event category and published/draft/pending status
* New: better error reporting on event add/edit screen

= 4.4.2 (February 15, 2020) =

* Fix: draft/pending recurring events were being displayed incorrectly

= 4.4.1 (January 7, 2020) =

* Fix: fix storage of event image number for some DB configurations
* Fix: add rules to default styles for columns to work better on more themes

= 4.4.0 (November 11, 2019) =

* New: columns format will now allow configuration of number of columns 1-4
* New: support columns format in widgets (upcoming and today's events)
* New: option to open links to maps in new window
* New: option for link to add a single occurrence of an event to Google calendar
* New: allow configuration of the text to display for "More details"
* New: Theme updates for Twenty Twenty theme
* Fix: typo in upcoming events widget 
* Fix: missing note describing recurrence on event edit screen
* Fix: Gutenberg block options for front end submit and today's events
* Remove: CampTix references in bonus add-ons, integration is now deprecated

= 4.3.1 (March 21, 2019) =

* Fix: undefined variable error message when Category system turned off

= 4.3.0 (March 18, 2019) =

* New: add option to use category colour as the background on the basic full calendar display
* Fix: remember selected event status when an edit fails
* Tweak: display categories in alphabetic order in drop down lists when the option is enabled (previously only the category key display was alphabeticized)
* Tweak: make messages on category edit screen stand out better
* Tweak: remove category dropdown on front-end submit Gutenberg block options since it doesn't apply

= 4.2.3 (February 27, 2019) =

* Fix: coloring of event link in main calendar to use category color

= 4.2.2 (February 19, 2019) =

* Fix: moved missing closing tag to proper position

= 4.2.1 (February 14, 2019) =

* Fix: add missing closing span tag, missing since 4.1.0

= 4.2.0 (February 13, 2019) =

* New: replace datepicker script with multilingual, built-in jQuery UI datepicker

= 4.1.0 (February 5, 2019) =

* New: Support both event link and location map on same event. This changes the calendar layout, so if you have made customizations you need to check that they are still compatible.
* Tweak: default CSS improvements
* Tweak: Additional sanitation
* Tweak: re-read options when customizer active

= 4.0.1 (December 10, 2018) =

* Fix: Weekly calendar week number correction

= 4.0.0 (November 19, 2018) =

* New: Support for Gutenberg editor blocks
* New: Add title to all shortcodes to work better with Gutenberg blocks
* Tweak: Add class to main calendar time display to allow CSS targeting
* Tweak: Only load options once in constructor
* Tweak: Use full font-size on lists in shortcodes
* Tweak: Increase mini-calendar width in post/page to 320px, widget size remains at 100%

= 3.9.1 (October 29, 2018) =

* Fix: move call to enqueue styles to ensure styles are loaded in header

= 3.9.0 (October 5, 2018) =

* New: Responsive 3-column layout for upcoming list and today's event list
* Fix: weekly calendar headings should always reflect the first day displayed
* Tweak: remove excess > on admin page

= 3.8.0 (August 28, 2018) =

* New: event status (published/draft/pending)
* New: option to sort category key alphabetically
* New: option to include "today" in the upcoming event lists
* New: improvements to the admin area of event management: filter by status, customize the column display in "screen options"
* Tweak: increase some input field widths to display better on Mac Safari
* Tweak: add link to documentation on admin pages
* Tweak: changed some admin options to use checkboxes instead of select lists

= 3.7.4 (July 24, 2018) =

* Tweak: add rel=nofollow to calendar paging links to prevent excessive indexing

= 3.7.3 (June 1, 2018) =

* Tweak: use configured WordPress time format in the back end

= 3.7.2 (May 29, 2018) =

* Fix: hover/click for iOS devices

= 3.7.1 (May 25, 2018) =

* Fix: use better method to determine paging URLs to support more servers

= 3.7.0 (May 22, 2018) =

* New: event location field and option to link to Google map
* New: allow specification of titles for categories

= 3.6.2 (March 23, 2018) =

* Fix: backward compatibility for settings_update_event_edit() for front end submit

= 3.6.1 (March 19, 2018) =

* Fix: add/edit event tab for non-admins
* New: rough in bonus tabs

= 3.6.0 (March 7, 2018) =

* New: add search to the event list, moved event add/edit to a new tab
* New: use category color on minical popup event title
* Fix: minicalendar paging on blog posts/archive pages

= 3.5.11 (February 18, 2018) =

* Tweak: adjust default CSS for key table to display properly on themes with zero padding table cells

= 3.5.10 (February 9, 2018) =

* Tweak: modify category key table CSS to display categories in a single row
* Tweak: add notice on category setting page if categories are disabled
* Tweak: only enqueue frontend scripts and styles when needed

= 3.5.9 (January 17, 2018) =

* Add spiffy-week to shortcode generator

= 3.5.8 (January 17, 2018) =

* New: spiffy-week shortcode to view one week at a time
* Fix: replace default image with blank gif to avoid broken image icon when editing events with no images
* Tweak: check for wp-color-picker function before using
* Tweak: use date_i18n for calendar month output
* Tweak: refactor some code 

= 3.5.7 (December 13, 2017) =

* Fix: set text fields in DB to utf8_general_ci to properly support Hebrew

= 3.5.5 (December 7, 2017) =

* Fix: some translation files became corrupted, restored to the values from version 3.4.2

= 3.5.4 (November 28, 2017) =

* Improvement: retain scroll position when paging through the calendar

= 3.5.3 (November 26, 2017) =

* Fix: unmatched HTML tag in expanded list view 

= 3.5.2 (November 21, 2017) =

* Fix: hover/click behaviour on iphone, broken in 3.5.0

= 3.5.1 (November 17, 2017) =

* Fix: table creation on new install, broken in 3.5.0
* Fix: admin message highlighting

= 3.5.0 (November 14, 2017) =

* New: option to make the full size calendar responsive
* New: HTML/CSS edits for better validation and responsiveness! If you added custom CSS please check that you are still getting the expected result.
* New: allow titles up to 60 chars
* New: mini-calendar will now support links from the event title in the popup
* New: add new bonus front end submit shortcode to the generator
* New: add class to weekday headings row
* New: updated default CSS to allow popups to remain open on hover
* New: allow infinite recurrence of custom days recurring events 
* Fix: problem when updating the database format and there are no existing events
* Improvement: add script to force correct entries for custom days recurrence
* Improvement: add some sanity checks to option input
* Improvement: remove touch device onclick code, default behaviour now works better
* Improvement: display the WP db error message if event edit fails to aid in diagnosing the problem
* Update plugin domain

= Version 3.4.2 (October 21, 2017) =

* Fix: edit of event with blank time

= Version 3.4.1 (October 21, 2017) =

* Fix: titles for upcoming and today's widgets, broken in Version 3.4.0
* Minor: allow blank authors for frontend submit (upcoming bonus feature)
* Minor: edits to remove PHP warnings
* Minor: update some error message text

= Version 3.4.0 (October 12, 2107) =

* New: option to display today's date with the Today's Events list
* New: option to display a message if no events are found in the Today's Events list and the Upcoming Events list
* New: option to display week number on full size calendar
* New: allow specification of midnight event start time
* Fix: display of custom days recurring events in dashboard
* Improvement: Limit default file upload screen to images only
* Improvement: merged the bonus addons shortcode generator code here
* Improvement: remove donation message for those who have already donated
* Improvement: clean up uninstall code that referenced deprecated options
* Improvement: convert calendar heading from table row to a caption to avoid colspan
* Improvement: added filters (spiffy_upcoming_day_classes, spiffy_upcoming_day_date) to allow modification of upcoming event list format
* Improvement: use SVG icon in admin area instead of image

= Version 3.3.0 (June 2, 2017) =

* Fix: remove Reflected Cross Site Scripting vulnerability. Many thanks to Dimitrios Tsagkarakis for responsible disclosure.

= Version 3.2.0 (March 2, 2017) = 

* New: add option to allow description and image display in the mini-calendar popup window

= Version 3.1.5 (January 13, 2017) =

* New: add filter hook to description output
* Fix: reference to undefined variable when adding new event
* Additional output sanitizing

= Version 3.1.4 (December 1, 2016) =

* Update default styles to account for new Twenty Seventeen theme 
* Add version number to default.css to force reload
* Fix widget default titles when using the WP customizer

= Version 3.1.3 (September 20, 2016) =

* Improvement: default the event author on new events to the logged in user instead of first in list

= Version 3.1.2 (August 26, 2016) =

*  Allow admin to set author when creating a new event

= Version 3.1.1 (August 25, 2016) =

* Fix: author display name fix for WP versions lower than 4.5
* Clean up a couple of minor PHP notices
* Update .pot file

= Version 3.1.0 (August 23, 2016) =

* New: option to limit non-admin event managers to handling their own events only
* New: event managers with full admin privileges can modify the author of an event
* Improvement: Honour the user post reassignment selection (if available) on spiffy events when a user is deleted

= Version 3.0.8 (August 4, 2016) =

* Fix potential conflict of shortcode buttons with other plugins

= Version 3.0.7 (July 25, 2016) =

* Modify MailPoet support to insert inline styles
* Force expanded style in MailPoet newsletter

= Version 3.0.6 (June 29, 2016) =

* New: Support spiffy-upcoming-list in Mail Poet newsletters
* Fix: Avoid error message when DB table is empty
* Move plugin documentation to new domain

= Version 3.0.5 (January 27, 2016) =

* Improvement: Allow new lines in event description

= Version 3.0.4 (January 3, 2016) = 

* Fix: Default styles for detailed event display

= Version 3.0.3 (December 30, 2015) =

* Improvement: Replace local time function with WordPress standard function current_time
* Improvement: Simplify the year switcher build code
* Improvement: Add CSS rule to the defaults to help avoid conflicts with other plugins' CSS
* Improvement: Don't add admin bar shortcuts if the user has no permission to access the area

= Version 3.0.2 (November 20, 2015) =

* New: Apply category colour to expanded list event titles
* New: Test and tweak styles for WordPress 4.4 and Twenty Sixteen theme
* Improvement: add "weekend" class to calendar grid boxes
* Improvement: remove form padding in default CSS
* Improvement: remove 200px limitation of mini-calendar widget
* Improvement: use even faster query to check DB table 
* Fix: Fix problem with slashes being added to custom CSS quotes
* Fix: Add event after errors noted is now fixed

= Version 3.0.1 (November 13, 2015) =

* New: Screen option to set number of events displayed in the event manager list

= Version 3.0.0 (November 12, 2015) =

NOTE: **You will need to re-add your widgets after upgrading!**

NOTE: *It is recommended that you reset your calendar styles to the default.* However, if you performed customization on your styles and don't wish to lose your customization, you should check that the calendar is still displaying as expected. Default styles are now always loaded; custom CSS will be appended to the default styles. This change will allow for proper future style updates. [Click here](https://spiffycalendar.spiffyplugins.ca/css-snippets/) for helpful CSS snippets.

* New: Event admin list updated to WP format -- Now supports bulk event deletion, copying events, sorting the event list by category
* New: Widgets updated to use Widget API - you can now add multiple widgets
* New: "Limit" argument added to upcoming list and today's events lists (both shortcode and widgets)
* New: "Style" argument added to upcoming list and today's events lists (both shortcode and widgets)
* New: Featured event widget
* New: Updated styles and display classes (backwards compatible as much as possible)
* New: Category class added to main calendar display to allow CSS to target by category
* Fix: Provide a way to unlink an image from an event
* CHANGE: Default styles are now always loaded. Custom CSS will be added after the default styles, allowing for proper future style updates.
* Improvement: Removed options to enable/disable upcoming lists and today's events lists since they didn't really do anything
* Improvement: Rename "sept" link to "sep" for consistency
* Improvement: Use "prepare" instead of "esc_sql"
* Improvement: Fix image thumbnail display to use thumbnail size of image
* Improvement: When filtering by category, the category key will now only display the filtered categories

= Version 2.1.3 (October 10, 2015) =

* Better solution over version 2.1.2, uses table query to check DB structure and fixes automatically without using information schema query

= Version 2.1.2 (October 9, 2015) =

* Move database format check back to the activate function since DB information schema query causes some hosts to hang (in particular HostGator). 

= Version 2.1.1 (October 8, 2015) =

* Fix yearly recurring event display in upcoming lists, one more time. Thanks to tony_phillips for his help finding and diagnosing this issue.

= Version 2.1.0 =
* Fix: Fix monthly and yearly recurring events' display in upcoming lists
* New FEATURE: shortcode generator button in edit windows
* New FEATURE: update admin pages to match current WP dashboard style with tabs, add shortcuts to admin toolbar
* LANGUAGE SUPPORT: Updated .POT file
* LANGUAGE SUPPORT: German, courtesy Ingrid Maie

= Version 2.0.1 =

* Fix: Fix weekly recurring events with a specified number of repeats to display properly in their last month

= Version 2.0.0 =

* New FEATURE: Support custom days recurrence
* SECURITY: Run all input/output through WordPress sanitation functions
* SECURITY: Ensure category list is specified as numeric ids
* Improvement: Use WordPress Media uploader for event image specification
* Improvement: Deleting an event will no longer delete the associated image, it will remain in the Media Library
* Improvement: Use WordPress color picker for category color configuration
* Improvement: Add colgroup to display category key in smaller size in html5
* Improvement: Update default CSS to better fit long titles
* Improvement: Rewrite event query functions to reduce repetitive queries
* Fix: Fix problem when an event edit was rejected, the subsequent edit would create a new event

= Version 1.3.1 Stable =

* Replace occurrences of mysql_real_escape_string (deprecated on some servers) with esc_sql
* Remove the test for 30 characters when saving an event in the DB
* Polish translation courtesy of Krzysztof Kacprzyk 
* Swedish translation courtesy of Kenneth Andersson

= Version 1.3.0 =

* Rename "popup" div to "spiffy-popup" to avoid conflicts with other themes/plugins

**Warning: Version 1.3.0 renames the hover popup DIV used to show the event details. Please check your hovering after upgrading. If it is misbehaving and you have no CSS customizations, tick the box to restore the default CSS. If you have customized CSS, please add the following:**

	.calnk a:hover div.spiffy-popup { position:absolute; }
	
* Turkish translation (Courtesy Dr Abdullah Manaz, www.manaz.net)
* Dutch translation (Courtesy Joek Brongers)

= Version 1.2.1 =

* Encode more visible strings for translation
* French translation (Courtesy Mathieu Gaunet, www.mathieugaunet.com, contact@mathieugaunet.com)

= Version 1.2.0 =

* Override selected parts of recurring event without changing original event definition, courtesy of Douglas Forester. MAKE SURE YOU DEACTIVATE AND REACTIVATE THE PLUGIN. This will happen automatically if you use the WP updater, but if you just copy the files via FTP you must do this manually to ensure the database is updated.

= Version 1.1.8 =

* CSS improvements. The default CSS has been updated to work better with most themes.
* Additional language strings

= Version 1.1.7 =

* Add language support
* Spanish translation (Courtesy of Andrew Kurtis, WebHostingHub)

= Version 1.1.6 July 18, 2013 =

* Fix typo in event end time test

= Version 1.1.5 March 21, 2013 =

* Fix title and category selection on mini calendar widget

= Version 1.1.4 March 20, 2013 = 

* Fix change made in version 1.1.3 

= Version 1.1.3 March 18, 2013 =

* Add popup window closure for better functionality on touch devices

= Version 1.1.2 February 17, 2013 =

* Allow 3 digits for upcoming days configuration
* Fix minicalendar widget (has been missing since day 1)

= Version 1.1.1 February 7, 2013 =

* Fix default CSS to confine table styles to Spiffy Calendar tables

= Version 1.1.0 January 22, 2013 =

* New FEATURE: Provide option to open event links in new window
* Fix typo in minical html

= Version 1.0.3 January 15, 2013 =

* Fix end time on mini-calendar hover

= Version 1.0.2a December 17, 2012 =

* Make sure CSS file is recreated after plugin upgrade

= Version 1.0.1 November 2012 =

* Corrected missed removal of some options when plugin is deleted, and renamed to avoid conflicts

= Version 1.0.0 November 19, 2012 =

* Initial version

== Upgrade Notice ==

= 1.0.0 =

* Initial release