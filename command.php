<?php

/**
 * Command to crack user passwords.
 */
Class Wapuu_The_Ripper_Command {

	/**
	 * Wapuu the Ripper - a tool to crack user passwords.
	 * Based on https://www.drupal.org/project/drop_the_ripper
	 *
	 * Users can be filtered via arguments supported by:
	 * [WP_User_Query()](https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/).
	 *
	 * Uses a default wordlist from http://www.openwall.com/wordlists
	 *
	 * ## OPTIONS
	 *
	 * [--role=<role>]
	 * : Only display users with a certain role.
	 *
	 * [--<field>=<value>]
	 * : Filter users by one or more arguments of WP_User_Query().
	 *
	 * [--top=<top>]
	 * : Use the top x passwords from the wordlist.
	 *
	 * [--all]
	 * : Use all of the passwords from the wordlist.
	 *
	 * [--hide]
	 * : Do not show plaintext passwords in output.
	 *
	 * [--no-guessing]
	 * : Disables built-in password guessing (e.g. username as password).
	 *
	 * [--wordlist=<wordlist>]
	 * : Path to a custom wordlist (default is openwall's password list).
	 *
	 * ## EXAMPLES
	 *
	 * wp wtr
	 *
	 * wp wtr --top=100 --roles=administrator
	 *
	 * wp wtr --all --role__not_in=subscriber
	 *
	 * wp wtr --exclude=1 --hide
	 *
	 * @alias wapuu-the-ripper
	 */
	public function __invoke($args, $assoc_args) {
		$start = microtime(TRUE);
		$users = get_users($assoc_args);

		$wordlist = __DIR__ . '/wordlist.txt';
		if (isset($assoc_args['wordlist'])) {
			if (file_exists($assoc_args['wordlist'])) {
				$wordlist = $assoc_args['wordlist'];
			}
			else {
				WP_CLI::warning('Custom wordlist not found; using default.');
			}
		}
		$passwords = $this->wtr_load_wordlist($wordlist, $assoc_args);
		$matches = [];
		$user_checks = 0;
		$pw_checks = 0;

		foreach ($users as $user) {
			$user_checks++;
			if (!isset($assoc_array['no-guessing'])) {
				$guesses = $this->wtr_user_guesses($user);
				foreach ($guesses as $guess) {
					$pw_checks++;
					if (wp_check_password($guess, $user->data->user_pass, $user->ID)) {
						$matches[] = $user;
						if (isset($assoc_args['hide'])) {
							$guess = '*****';
						}
						WP_CLI::log(sprintf('Match: ID=%s login=%s password=%s',  $user->ID ,$user->data->user_login, $guess));
						continue 2; // No need to try passwords for this user.
					}
				}
			}
			foreach ($passwords as $password) {
				$pw_checks++;
				if (wp_check_password($password, $user->data->user_pass, $user->ID)) {
					$matches[] = $user;
					if (isset($assoc_args['hide'])) {
						$password = '*****';
					}
					WP_CLI::log(sprintf('Match: ID=%s login=%s password=%s',  $user->ID ,$user->data->user_login, $password));
					break;
				}
			}
		}

		$finish = microtime(TRUE);
		$time_taken = sprintf('%.2f', $finish - $start);
		$output = sprintf('Ran %s checks for %s users in %s seconds.', $pw_checks, $user_checks, $time_taken);
		if (empty($matches)) {
			WP_CLI::success($output . ' No matches.');
		}
		else {
			WP_CLI::success($output . ' ' . count($matches) . ' match(es).');
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

	/**
	 * Make a few guesses about a user's password.
	 *
	 * @param object $user
	 *   A  user object.
	 *
	 * @return array
	 *   Guesses at the user's password.
	 */
	private function wtr_user_guesses($user) {
		$guesses = [];
		$guesses[] = $user->user_login;
		$guesses[] = $user->user_login . date('Y');
		$guesses[] = $user->user_nicename;
		$guesses[] = $user->display_name;
		$guesses[] = $user->user_email;
		if (preg_match('/(.*)@(.*)\..*/', $user->user_email, $matches)) {
			$guesses[] = $matches[1]; // Username portion of mail.
			$guesses[] = $matches[2]; // First part of domain.
		}
		return array_unique(array_filter($guesses));
	}

}

WP_CLI::add_command('wtr', 'Wapuu_The_Ripper_Command');
