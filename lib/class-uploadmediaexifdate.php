<?php
/**
 * Upload Media Exif Date
 *
 * @package    Upload Media Exif Date
 * @subpackage UploadMediaExifDate Main function
/*  Copyright (c) 2020- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$uploadmediaexifdate = new UploadMediaExifDate();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class UploadMediaExifDate {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_filter( 'wp_generate_attachment_metadata', array( $this, 'attachment_datetime_handler' ), 10, 2 );
	}

	/** ==================================================
	 * Filter of Attachment datetime
	 *
	 * @param array $metadata  metadata.
	 * @param int   $id  id.
	 */
	public function attachment_datetime_handler( $metadata, $id ) {

		$mimetype = get_post_mime_type( $id );
		if ( 'image/jpeg' === $mimetype || 'image/tiff' === $mimetype ) {

			$postdate = null;

			$exif_ux_time = null;
			if ( ! empty( $metadata ) && array_key_exists( 'image_meta', $metadata ) ) {
				$exif_ux_time = $metadata['image_meta']['created_timestamp'];
			}
			if ( ! empty( $exif_ux_time ) ) {
				if ( function_exists( 'wp_date' ) ) {
					$postdate = wp_date( 'Y-m-d H:i:s', $exif_ux_time, new DateTimeZone( 'UTC' ) );
				} else {
					$postdate = date_i18n( 'Y-m-d H:i:s', $exif_ux_time, false );
				}
			} else {
				if ( function_exists( 'wp_get_original_image_path' ) ) {
					$file_path = wp_get_original_image_path( $id );
				} else {
					$file_path = get_attached_file( $id );
				}
				$shooting_date_time = null;
				$exif = @exif_read_data( $file_path, 'FILE', true );
				if ( isset( $exif['EXIF']['DateTimeOriginal'] ) && ! empty( $exif['EXIF']['DateTimeOriginal'] ) ) {
					$shooting_date_time = $exif['EXIF']['DateTimeOriginal'];
				} else if ( isset( $exif['IFD0']['DateTime'] ) && ! empty( $exif['IFD0']['DateTime'] ) ) {
					$shooting_date_time = $exif['IFD0']['DateTime'];
				}
				if ( ! empty( $shooting_date_time ) ) {
					$shooting_date = str_replace( ':', '-', substr( $shooting_date_time, 0, 10 ) );
					$shooting_time = substr( $shooting_date_time, 10 );
					$postdate = $shooting_date . $shooting_time;
				} else {
					$filetype = wp_check_filetype( $file_path );
					$filename = wp_basename( $file_path, '.' . $filetype['ext'] );
				}
			}

			if ( is_null( $postdate ) ) {
				/* Original hook */
				$postdate = apply_filters( 'umed_postdate', $postdate, $filename );
			}

			if ( $postdate ) {
				$postdategmt = get_gmt_from_date( $postdate );
				$up_post = array(
					'ID' => $id,
					'post_date' => $postdate,
					'post_date_gmt' => $postdategmt,
					'post_modified' => $postdate,
					'post_modified_gmt' => $postdategmt,
				);
				wp_update_post( $up_post );

				/* Move YearMonth Folders */
				if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
					$current_dir = wp_upload_dir();
					$current_subdir = $current_dir['subdir'];
					$y = substr( $postdategmt, 0, 4 );
					$m = substr( $postdategmt, 5, 2 );
					if ( "/$y/$m" <> $current_subdir ) {
						$move_path = str_replace( wp_normalize_path( $current_subdir ), wp_normalize_path( "/$y/$m" ), $current_dir['path'] );
						if ( ! is_dir( $move_path ) ) {
							wp_mkdir_p( $move_path );
						}
						$current_path = wp_normalize_path( trailingslashit( $current_dir['path'] ) );
						$move_path = wp_normalize_path( trailingslashit( $move_path ) );
						/* Thumbnails */
						$thumbnails = $metadata['sizes'];
						foreach ( $thumbnails as $key => $key2 ) {
							$this->copy_file( $current_path . $key2['file'], $move_path . $key2['file'] );
						}
						/* Image */
						$attache_file = get_post_meta( $id, '_wp_attached_file', true );
						$basename = wp_basename( $attache_file );
						$this->copy_file( $current_path . $basename, $move_path . $basename );
						/* Original Image */
						if ( ! empty( $metadata ) && ! empty( $metadata['original_image'] ) && array_key_exists( 'original_image', $metadata ) ) {
							$this->copy_file( $current_path . $metadata['original_image'], $move_path . $metadata['original_image'] );
						}
						update_post_meta( $id, '_wp_attached_file', str_replace( trim( $current_subdir, '/' ), "$y/$m", $attache_file ) );
						$metadata['file'] = str_replace( trim( $current_subdir, '/' ), "$y/$m", $metadata['file'] );
					}
				}
			}
		}

		return $metadata;
	}

	/** ==================================================
	 * Copy file for Move YearMonth Folders
	 *
	 * @param string $copy_file_org  copy_file_new.
	 * @param string $copy_file_new  copy_file_new.
	 */
	private function copy_file( $copy_file_org, $copy_file_new ) {

		if ( file_exists( $copy_file_org ) ) {
			$err_copy = @copy( $copy_file_org, $copy_file_new );
			if ( ! $err_copy ) {
				if ( file_exists( $copy_file_new ) ) {
					wp_delete_file( $copy_file_new );
				}
			} else {
				wp_delete_file( $copy_file_org );
			}
		}
	}
}
