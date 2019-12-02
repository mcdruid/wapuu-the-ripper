<?php

/**
 * Command to crack user passwords.
 * Based on https://www.drupal.org/project/drop_the_ripper
 */
Class Wapuu_The_Ripper_Command {

	/**
	 * Try to crack user passwords
	 *
	 * Users are filtered / selcted via arguments supported by
	 * [WP_User_Query()](https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/).
	 *
	 * ## OPTIONS
	 *
	 * [--role=<role>]
	 * : Only display users with a certain role.
	 *
	 * [--top=<top>]
	 * : Use the top x passwords from the wordlist.
	 *
	 * [--all]
	 * : Use all of the passwords from the wordlist.
	 */
	public function __invoke($args, $assoc_args) {

		$users = get_users($assoc_args);

		$wordlist = __DIR__ . '/wordlist.txt';
		$passwords = $this->wtr_load_wordlist($wordlist, $assoc_args);
		$matches = [];
		$checked = 0;

		foreach ($users as $user) {
			$checked++;
			foreach ($passwords as $password) {
				if (wp_check_password($password, $user->data->user_pass, $user->ID)) {
					$matches[] = $user;
					WP_CLI::warning('Match! ' . $user->ID . ': ' . $user->data->user_login . ' password: ' . $password);
					continue;
				}
			}
		}

		if (empty($matches)) {
			WP_CLI::success('Checked ' . $checked . ' users. No matches.');
		}
		else {
			WP_CLI::success('Checked ' . $checked . ' users. ' . count($matches) . ' match(es).');
		}
	}

	/**
	 * Parse a wordlist file into an array.
	 *
	 * @param string $wordlist
	 *   Path to the wordlist file.
	 *
	 * @return array
	 *   Candidate passwords.
	 */
	private function wtr_load_wordlist($wordlist, $assoc_args) {
		$passwords = file($wordlist);
		$passwords = array_filter($passwords, [
			$this,
			'wtr_wordlist_filter_callback',
		]);
		$passwords = array_map([$this, 'wtr_trim_newline'], $passwords);
		$passwords = array_unique($passwords);

		if (!isset($assoc_args['all'])) {
			$top = isset($assoc_args['top']) ? (int) $assoc_args['top'] : 25;
			if ($top > 0) {
				$passwords = array_slice($passwords, 0, $top);
			}
		}

		return $passwords;
	}

	/**
	 * Callback for wordlist array filtering; removes comments.
	 *
	 * @param string $line
	 *   An item from a wordlist.
	 *
	 * @return bool
	 *   FALSE if the line is a comment.
	 */
	private function wtr_wordlist_filter_callback($line) {
		return (strpos($line, '#!comment:') !== 0);
	}

	/**
	 * Callback for wordlist array trimming; remove only trailing newlines.
	 *
	 * @param string $line
	 *   An item from a wordlist.
	 *
	 * @return string
	 *   Candidate password with trailing newline removed.
	 */
	private function wtr_trim_newline($line) {
		// Note that double quotes are necessary for the whitespace characters.
		return rtrim($line, "\r\n");
	}

}

WP_CLI::add_command('wtr', 'Wapuu_The_Ripper_Command'); // Struggling with @alias
WP_CLI::add_command('wapuu_the_ripper', 'Wapuu_The_Ripper_Command');
