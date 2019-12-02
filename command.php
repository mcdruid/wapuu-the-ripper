<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Try to crack user passwords
 *
 * @when after_wp_load
 */
$wapuu_the_ripper_command = function() {
  $users = get_users();

  $wordlist = __DIR__ . '/wordlist.txt';
  $passwords = _dtr_load_wordlist($wordlist);
  $matches = [];
  $checked = 0;

  foreach($users as $user) {
    $checked ++;
    foreach($passwords as $password) {
      if (wp_check_password($password, $user->user_pass, $user->ID)) {
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
};
WP_CLI::add_command( 'wtr', $wapuu_the_ripper_command );

function drush_get_option($name, $default) {
  return $default;
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
function _dtr_load_wordlist($wordlist) {
  $passwords = file($wordlist);
  $passwords = array_filter($passwords, '_dtr_wordlist_filter_callback');
  $passwords = array_map('_dtr_trim_newline', $passwords);
  $passwords = array_unique($passwords);

  if (!drush_get_option('all', FALSE)) {
    $top = (int) drush_get_option('top', 25);
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
function _dtr_wordlist_filter_callback($line) {
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
function _dtr_trim_newline($line) {
  // Note that double quotes are necessary for the whitespace characters.
  return rtrim($line, "\r\n");
}
