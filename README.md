# Liked User Content
Liked User Content is a WordPress plugin that enables logged-in WordPress users to save images that have been uploaded as attachments to a “favorites” page.

![Image of LUC buttons](https://cldup.com/3-ZU8kqPmH-3000x3000.png)

### How does it work?
Liked User Content provides functions for allowing WordPress theme designers to hook “like” and “love” (optional) buttons to image attachments that have been uploaded and displayed on the site. If a user wants to save a copy of an image for later viewing he or she can click the “like” button associated with the image. The image will be copied by the plugin and attached to a special page that “collects” images the user has liked. These special pages are called Bucket pages. They are simply a custom post type in WordPress. Bucket pages can be modified through theme customization to display saved images as galleries or any other format the site developer sees fit.

After Liked User Content is installed and setup is complete, each WordPress user will have two Bucket pages. One Bucket page for images that the user has "liked" and another for images the user has "loved".

A user simply clicks the “small heart” button to like an image, or the “big heart” button to love an image.

A loved image is copied to both the user’s Like and Love Bucket pages (liked images are only copied to the Like Bucket page). This gives users the ability to keep two separate collections of saved images. The love button and its functionality is optional and can be disabled in the LUC Settings page.

LUC is ideal for gallery blogs or internal company blogs that contain design assets that employees may want to save and keep in one easy to access place.

### Installation
Upload the liked-user-content directory to your WordPress plugins directory and enable the plugin in the WordPress admin.

The first thing you probably want to do after enabling the plugin is to visit the LUC Settings page in the admin area and generate Bucket pages for all the users on the site.

### Options
The plugin can be customized in several ways. For instance the titles of bucket pages can be customized with a simple template string. The amount of “likes” permitted for users can be set and will limit the amount of image attachments copied to bucket pages for each user.

### Code
First, make the Liked User Content functions available to your theme by adding this line in your template:
```php
include_once( WP_PLUGIN_DIR . '/liked-user-content/public/functions.php' );
```

### Making an uploaded image attachment "likeable"
It is recommended you place the HTML markup for the LIKE/LOVE buttons directly below an image attachment so the user knows what buttons are associated with which image. Also, you can have several Like and Love buttons associated with multiple images on the same page. (Just make sure each set of Like and Love buttons are associated with a different image.)

```php
// ID of an image attachment.
$attachment_id = get_the_ID();	// One way to get the ID...
// The image
$src = wp_get_attachment_image_src($attachment_id, 'full');
// The markup for the Like and Love buttons.
$button_markup = luc_display_toggle_buttons($attachment_id);

```

### Getting links to the user’s Like and Love bucket pages
```php
$bucket_pages = luc_get_user_bucket_links();

// $bucket_pages will contain an array resembling this one:

[
    0 => [
        'post_title' => 'Claire\'s liked stuff',
        'url' => 'http://123.whatever.xyz/wordpress/claires-liked-stuff'
    ],
    1 => [
        'post_title' => 'Claire\'s liked stuff',
        'url' => 'http://123.whatever.xyz/wordpress/claires-loved-stuff'
    ],
]
```

### How do I display images saved to Bucket pages as a gallery?
Simply create a template file called single-bucket.php and customize it to display image attachments as a gallery. This will require some knowledge of editing themes or use of a WordPress gallery plugin. There are several tutorials on the web on how to accomplish this.

### Compatible WordPress versions
This plugin was tested on WordPress versions 4.5.2 and 4.6.

### Notes
Plugin author: Kenneth Ross (kenn.ross@gmail.com)

License: GPL v2

This plugin was developed using [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)

This plugin also uses [Google Material icons](https://design.google.com/icons/)

Changes

10/22/2016 -- Version 1.0 is released.

I hope you find it useful.