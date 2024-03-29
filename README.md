mcdruid/wapuu-the-ripper
========================

<img src='wapuu_the_ripper.png' width='169' align="right" />

A password cracker for WordPress (WP-CLI)

Quick links: [Using](#using) | [Installing](#installing) | [Credits](#credits)

## Using

NAME

  wp wtr

DESCRIPTION

  Wapuu the Ripper - a tool to crack user passwords.

SYNOPSIS

  `wp wtr [--role=<role>] [--<field>=<value>] [--top=<top>] [--all] [--hide] [--no-guessing]`

  Based on [Drop the Ripper](https://www.drupal.org/project/drop_the_ripper) for Drupal.

  Users can be filtered via arguments supported by
  [WP_User_Query()](https://developer.wordpress.org/reference/classes/wp_user_query/prepare_query/).

OPTIONS

  [`--role=<role>`]
    Only check users with a certain role.

  [`--<field>=<value>`]
    Filter users by one or more arguments of WP_User_Query().

  [`--top=<top>`]
    Use the top x passwords from the wordlist (default is 25).

  [`--all`]
    Use all of the passwords from the wordlist.

  [`--hide`]
    Do not show plaintext passwords in output.

  [`--no-guessing`]
    Disables built-in password guessing (e.g. username as password).

  [`--wordlist=<wordlist>`]
    Path to a custom wordlist (default is openwall's password list).

EXAMPLES

  wp wtr

  wp wtr --top=100 --role=administrator

  wp wtr --all --role__not_in=subscriber

  wp wtr --exclude=1 --hide

  wp wrt --wordlist=/tmp/rockyou.txt --include=2,3,4

## Installing

You can install this package with:

    wp package install git@github.com:mcdruid/wapuu-the-ripper.git

## Credits

* WtR uses a default wordlist from http://www.openwall.com/wordlists
* https://ayesh.me/ for suggesting the name!
