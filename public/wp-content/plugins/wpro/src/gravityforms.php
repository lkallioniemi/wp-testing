<?php

class WPRO_Gravityforms {

	function __construct() {
		$log = wpro()->debug->logblock('WPRO_Gravityforms::__construct()');
		add_action('gform_after_submission', array($this, 'gravityforms_after_submission'), 10, 2);
		return $log->logreturn(true);
	}

	function gravityforms_after_submission($entry, $form) {
		$log = wpro()->debug->logblock('WPRO_Gravityforms::gravityforms_after_submission()');

		$upload_dir = wp_upload_dir();
		foreach($form['fields'] as $field) {
			if ($field['type'] == 'fileupload') {
				$id = (int) $field['id'];
				if ($entry[$id]) {
					$file_to_upload = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $entry[$id]);
					$mime = wp_check_filetype($file_to_upload);
					$data = apply_filters('wpro_backend_store_file', array(
						'url' => $entry[$id],
						'file' => $file_to_upload,
						'type' => $mime['type'],
					));
					if (is_array($data)) {
						$entry[$id] = wpro()->url->attachmentUrl($entry[$id]);
						GFAPI::update_entry($entry);
					} else {
						$log->log('Some error somewhere: $data after wpro_backend_store_file filter is not an array.');
					}
				}
			} else if ($field['type'] == 'post_image') {
				$id = (int) $field['id'];
				if ($entry[$id]) {
					$entry[$id] = wpro()->url->attachmentUrl($entry[$id]);
					GFAPI::update_entry($entry);
				}
			}
		}
		return $log->logreturn(true);
	}
}
