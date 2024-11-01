=== Upload Media Exif Date ===
Contributors: Katsushi Kawamori
Donate link: https://shop.riverforest-wp.info/donate/
Tags: date, time, exif, media, media library
Requires at least: 4.7
Requires PHP: 8.0
Tested up to: 6.6
Stable tag: 1.07
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

When registering images in the media library, register with the date of EXIF information.

== Description ==

= Register image to media library  =
* Change to the date/time of EXIF information.

= Sibling plugin =
* [Exif Caption](https://wordpress.org/plugins/exif-caption/).
* [Exif Details](https://wordpress.org/plugins/exif-details/).

= Sample of how to use the filter hook =
* Sample snippet
~~~
/**  ==================================================
 * Sample snippet for Upload Media Exif Date
 *
 * The original filter hook('umed_postdate'),
 * Get the date and time from the file name when the date and time cannot be read from the EXIF.
 *
 * @param string $postdate  postdate.
 * @param string $filename  filename.
 */
function umed_postdate_from_filename( $postdate, $filename ) {

	/* Sample for 20191120_183022.jpg */
	$year = substr( $filename, 0, 4 );
	$month = substr( $filename, 4, 2 );
	$day = substr( $filename, 6, 2 );
	$hour = substr( $filename, 9, 2 );
	$minute = substr( $filename, 11, 2 );
	$second = substr( $filename, 13, 2 );

	$postdate = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second;

	return $postdate;

}
add_filter( 'umed_postdate', 'umed_postdate_from_filename', 10, 2 );
~~~

== Installation ==

1. Upload `upload-media-exif-date` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. EXIF information
2. Media Library

== Changelog ==

= [1.07] 2024/03/05 =
* Fix - Changed file operations to WP_Filesystem.

= 1.06 =
Supported WordPress 6.4.
PHP 8.0 is now required.

= 1.05 =
Fixed filter hook('umed_postdate').

= 1.04 =
Add filter hook('umed_postdate').

= 1.03 =
Supported WordPress 5.6.

= 1.02 =
Fixed problem of metadata.

= 1.01 =
Fixed a problem with moving files.

= 1.00 =
Initial release.

== Upgrade Notice ==

= 1.00 =
Initial release.
